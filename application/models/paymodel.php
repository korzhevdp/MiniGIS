<?php
class Paymodel extends CI_Model{
	function __construct(){
		parent::__construct();
		//$this->output->enable_profiler(TRUE);
	}
	
	private function get_types(){
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

	public function get_locations_pay_summary() {
		$output = array();
		$where  = array();
		$data   = array();
		if($this->input->post("byType")){
			array_push($where, "AND `locations`.`type` = ?");
			array_push($data, $this->input->post("byType"));
		}
		if( ! $this->session->userdata('admin') && !($this->config->item('admin_can_edit_user_locations'))) {
			array_push($where, "AND `locations`.`owner` = ?");
			array_push($data,  $this->session->userdata('user_id'));
		}
		if($this->input->post("paid")){
			array_push($where, "AND `payments`.`paid`");
		}
		$result = $this->db->query("SELECT 
		`locations`.`location_name`,
		`payments`.`paid`,
		`locations`.`contact_info`,
		`locations`.`address`,
		`locations`.`active`,
		`locations`.`id`,
		`locations_types`.`name` as typename,
		IF(ISNULL(`payments`.`end`), '', DATE_FORMAT(`payments`.`end`, '%d.%m.%Y')) AS end,
		`users_admins`.nick
		FROM
		`locations`
		LEFT OUTER JOIN `payments` ON (`locations`.`id` = `payments`.`location_id`)
		LEFT OUTER JOIN `locations_types` ON (`locations`.`type` = `locations_types`.`id`)
		LEFT OUTER JOIN `users_admins` ON (locations.owner = `users_admins`.uid)
		WHERE
		(`payments`.`paid` = 1 OR ISNULL(`payments`.`paid`))
		".implode($where, "\n")."
		order by typename, `locations`.`location_name`", $data);
		if($result->num_rows()){
			foreach($result->result() as $row){
				$paid   = ($row->paid) ? 'success' : '';
				$active = ($row->active) ? '' : 'error muted';
				$location_name = (strlen($row->location_name)) ? $row->location_name : "Нет названия";
				$string = '<tr class="'.$paid.$active.'"><td><a href="/editor/edit/'.$row->id.'">'.$location_name.'</a></td><td>'.$row->typename.'</td><td>'.$row->address.'</td><td>'.$row->nick.'</td><td>'.$row->contact_info.'</td><td><input type="text" class="datepicker" placeholder="Оплат нет" id="d'.$row->id.'" value="'.$row->end.'"></td><td><button type="button" class="savePaidStatus" ref="'.$row->id.'">Сохранить</button></td></tr>';
				array_push($output, $string);
			}
		}
		$paydata = array(
			'table'   => implode($output, "\n"),
			'types'   => $this->get_types(),
			'checked' => ($this->input->post("paid")) ? ' checked="checked"' : ""
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
		if ( $this->check_paid_period() ) {
			$this->elongate_period();
		} else {
			$this->set_new_period();
		}
	}
}
/* End of file paymodel.php */
/* Location: ./application/models/paymodel.php */