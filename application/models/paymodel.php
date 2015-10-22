<?php
class Paymodel extends CI_Model {
	function __construct() {
		parent::__construct();
		//$this->output->enable_profiler(TRUE);
	}
	
	private function get_types() {
		$output = array();
		$result = $this->db->query("SELECT 
		`locations_types`.name,
		`locations_types`.id
		FROM
		`locations_types`");
		if($result->num_rows()){
			foreach($result->result() as $row){
				$selected = ($row->id === $this->input->post('byType')) ? 'selected="selected"' : '';
				$string   = '<option value="'.$row->id.'"'.$selected.'>'.$row->name.'</option>';
				array_push($output, $string);
			}
		}
		return implode($output, "\n");
	}

	private function make_pay_location_string($row, $paiddata) {
		$paid          = (isset($paiddata['status']))  ? $paiddata['status']  : "" ;
		$active        = ($row->active)                ? ''                   : 'error muted';
		$comments      = ($row->comments)              ? ' checked="checked"' : "";
		$end           = (isset($paiddata['end']))     ? $paiddata['end']     : "Оплат нет";
		$location_name = (strlen($row->location_name)) ? $row->location_name  : "Нет названия";
		$datefield     = ($this->session->userdata('admin'))
			? '<input type="text" class="datepicker" placeholder="Оплат нет" id="d'.$row->id.'" value="'.$end.'">' 
			: $end;

		$string = '<tr class="'.$paid.$active.'"><td><a href="/editor/edit/'.$row->id.'">'.$location_name.'</a></td><td>'.$row->typename.'</td><td>'.$row->address.'</td><td>'.$row->nick.'</td><td>'.$row->contact_info.'</td><td><input type="checkbox" id="c'.$row->id.'"'.$comments.'></td><td>'.$datefield.'</td><td><button type="button" class="savePaidStatus" ref="'.$row->id.'">Сохранить</button></td></tr>';
		return $string;
	}

	/*
		(SELECT payments.paid from `payments` WHERE `payments`.`end` > NOW() AND `payments`.`location_id` = `locations`.`id` LIMIT 1) as paid,
		(SELECT DATEDIFF(`payments`.`end`, now()) from `payments` WHERE `payments`.`location_id` = `locations`.`id` LIMIT 1) as paid2,
		
		а ещё как вариант попробуй через IN выбрать оплаченность и разницу дней до окончания срока.
		хрен там, оптимальнее всего и без мозголомства выбирать вообще всё. Не так то уж часто и будут. На крайняк выделим отдельный поток обработки. Модуль инородный.
	*/

	private function get_paid_data(){
		$output = array();
		$result = $this->db->query("SELECT
		`payments`.`location_id`,
		DATE_FORMAT(`payments`.`end`, '%d.%m.%Y') as `end`,
		IF(DATEDIFF(`payments`.`end`, NOW()) < 10, 'warning', 'success') as status
		FROM
		`payments`
		WHERE
		`payments`.`end` > NOW()");
		if($result->num_rows()){
			foreach($result->result_array() as $row){
				$output[$row['location_id']] = $row;
			}
		}
		return $output;
	}

	private function fill_search_arrays() {
		$output = array(
			'where' => array(),
			'data'  => array()
		);
		if ($this->input->post("byType")) {
			array_push($output['where'], "`locations`.`type` = ?");
			array_push($output['data'],  $this->input->post("byType"));
		}
		if ( ! $this->session->userdata('admin') && !($this->config->item('admin_can_edit_user_locations'))) {
			array_push($output['where'], "`locations`.`owner` = ?");
			array_push($output['data'],  $this->session->userdata('user_id'));
		}
		if ($this->input->post("comments")) {
			array_push($output['where'], "`locations`.`comments`");
		}
		return $output;
	}

	public function get_locations_pay_summary() {
		$output   = array();
		$data     = $this->fill_search_arrays();
		//print_r($data);
		$paiddata = $this->get_paid_data();
		$result   = $this->db->query("SELECT
		`locations`.`location_name`,
		`locations`.`contact_info`,
		`locations`.`address`,
		`locations`.`active`,
		`locations`.`id`,
		`locations`.`comments`,
		`locations_types`.`name` as typename,
		`users_admins`.nick
		FROM
		`locations`
		LEFT OUTER JOIN `locations_types` ON (`locations`.`type` = `locations_types`.`id`)
		LEFT OUTER JOIN `users_admins` ON (locations.owner = `users_admins`.uid)
		".((sizeof($data['where'])) ? "WHERE\n".implode($data['where'], "\nAND ") : "")."
		ORDER BY typename, `locations`.`location_name`", $data['data']);
		if ($result->num_rows()) {
			foreach($result->result() as $row) {
				$paydata = (isset($paiddata[$row->id])) ? $paiddata[$row->id] : array();
				if ($this->input->post("paid")) {
					if (isset($paiddata[$row->id])) {
						array_push($output, $this->make_pay_location_string($row, $paydata));
					}
				} else {
					array_push($output, $this->make_pay_location_string($row, $paydata));
				}
			}
		}
		$paydata = array(
			'table'   => implode($output, "\n"),
			'types'   => $this->get_types(),
			'paidchecked' => ($this->input->post("paid"))     ? ' checked="checked"' : "",
			'commchecked' => ($this->input->post("comments")) ? ' checked="checked"' : ""
		);
		return $this->load->view('admin/payments', $paydata, true);
	}

	private function check_paid_period() {
		$result = $this->db->query("SELECT
		*
		FROM
		`payments`
		WHERE
		`payments`.`location_id` = ?
		AND NOW() BETWEEN `payments`.`start` AND `payments`.`end`" , array($this->input->post('location')));
		if ($result->num_rows()) {
			return true;
		} else {
			return false;
		}
	}

	private function elongate_period() {
		$this->db->query("UPDATE
		`payments`
		SET
		`payments`.`end` = ?,
		`payments`.admin = ?,
		`payments`.`paid` = IF(NOW() > ?, 0, 1)
		WHERE location_id = ?", array(
			implode(array_reverse(explode(".", $this->input->post('paidtill'))), "-"),
			$this->session->userdata('name'),
			implode(array_reverse(explode(".", $this->input->post('paidtill'))), "-"),
			$this->input->post('location')
		));
	}

	private function set_comments_status() {
		$this->db->query("UPDATE
		`locations`
		SET
		`locations`.`comments` = ?
		WHERE `locations`.`id` = ?", array(
			($this->input->post('comments')) ? 1 : 0,
			$this->input->post('location')
		));
	}

	//UPDATE `payments` SET `payments`.paid = if(now() BETWEEN `payments`.`start` AND `payments`.`end`, 1, 0)

	private function set_new_period() {
		$this->db->query("INSERT INTO
		`payments`(
			`payments`.location_id,
			`payments`.start,
			`payments`.`end`,
			`payments`.admin,
			`payments`.paid
		)
		VALUES( ?, NOW(), ?, ?, IF(NOW() > ?, 0, 1) )", array(
			$this->input->post('location'),
			implode(array_reverse(explode(".", $this->input->post('paidtill'))), "-"),
			$this->session->userdata('name'),
			implode(array_reverse(explode(".", $this->input->post('paidtill'))), "-"),
		));
	}

	public function set_payment() {
		if ( !$this->input->post('location') || !$this->input->post('paidtill') ) {
			print "Not enough data";
			return false;
		}
		//администратор может управлять пользовательскими комментариями?
		if ($this->usefulmodel->check_owner($this->input->post('location'))) {
			$this->set_comments_status();
		}
		if($this->session->userdata('admin')){
			if ( $this->check_paid_period() ) {
				$this->elongate_period();
			} else {
				$this->set_new_period();
			}
		}
	}
}
/* End of file paymodel.php */
/* Location: ./application/models/paymodel.php */