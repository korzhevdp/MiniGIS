<?php
class Adminmodel extends CI_Model{
	function __construct() {
		parent::__construct();
	}

	private function get_type_name($loc_type) {
		$output = "Тип не определён";
		$result = $this->db->query("SELECT
		`locations_types`.name AS `type_name`
		FROM
		`locations_types`,
		`objects_groups`
		WHERE `locations_types`.`id` = ?", array($loc_type));
		if($result->num_rows()){
			$row    = $result->row();
			$output = $row->type_name;
		}
		return $output;
	}

	private function get_group_name($obj_group) {
		$output = "Нет названия группы";
		$result = $this->db->query("SELECT 
		`objects_groups`.name
		FROM
		`objects_groups`
		WHERE
		`objects_groups`.id = ?", array($obj_group));
		if($result->num_rows()){
			$row    = $result->row();
			$output = $row->name;
		}
		return $output;
	}

	private function get_library_group_list($obj_group, $controller) {
		$output = array();
		$result = $this->db->query('SELECT 
		`objects_groups`.name,
		`objects_groups`.id
		FROM
		`objects_groups`
		WHERE `objects_groups`.`active`
		AND `objects_groups`.`id` IN ('.$this->session->userdata('access').')
		ORDER BY `objects_groups`.name ASC');
		if($result->num_rows()){
			foreach ($result->result_array() as $row){
				$row['title']     = "";
				$row['obj_group'] = $obj_group;
				$row['img']       = '<img src="'.$this->config->item("api").'/images/folder.png" alt="">';
				$row['link']      = '/'.$controller.'/library/'.$row['id'];
				array_push($output, $this->load->view("admin/libraryitem", $row, true));
			}
		}
		return $output;
	}

	private function get_library_type_list($obj_group, $controller) {
		$output = array();
		$result = $this->db->query("SELECT 
		locations_types.id,
		locations_types.name AS `title`,
		IF(LENGTH(locations_types.name) > 49, CONCAT(LEFT(locations_types.name, 46) ,'...'), locations_types.name) AS name
		FROM
		locations_types
		WHERE
		`locations_types`.`pl_num` 
		AND `locations_types`.`object_group` = ?
		ORDER BY title", array($obj_group));
		if($result->num_rows()){
			foreach ($result->result_array() as $row){
				$row['obj_group'] = $obj_group;
				$row['img']  = '<img src="'.$this->config->item("api").'/images/folder.png" alt="">';
				$row['link'] = '/'.$controller.'/library/'.$obj_group.'/'.$row['id'];
				array_push($output, $this->load->view("admin/libraryitem", $row, true));
			}
		}
		return $output;
	}

	private function get_library_locations_list_by_type($loc_type) {
		$output = array();
		$view_user_locations = "AND `locations`.owner = ?";
		if($this->config->item('admin_can_edit_user_locations') === true){
			if($this->session->userdata('admin')){
				$view_user_locations = "";
			}
		}
		$result = $this->db->query("SELECT 
		IF(LENGTH(`locations`.location_name) > 49, CONCAT(LEFT(`locations`.location_name, 46), '...'),`locations`.location_name) AS name,
		`locations`.location_name AS title,
		`locations`.id
		FROM
		`locations`
		WHERE
		`locations`.`type`    = ?"
		.$view_user_locations."
		ORDER BY title", array(
			$loc_type,
			$this->session->userdata("user_id"))
		);
		if($result->num_rows()){
			foreach ($result->result_array() as $row){
				$row['img']  = '<img src="'.$this->config->item("api").'/images/location_pin.png" alt="">';
				$row['link'] = '/editor/edit/'.$row['id'];
				array_push($output, $this->load->view("admin/libraryitem", $row, true));
			}
		}
		$row = array(
			'img'   => '<img src="'.$this->config->item("api").'/images/location_pin.png" alt="">',
			'name'  => 'Добавить объект',
			'link'  => '/editor/add/'.$loc_type,
			'title' => "Добавить новый объект этого класса"
		);
		array_push($output, $this->load->view("admin/libraryitem", $row, true));
		return $output;
	}

	public function get_composite_indexes($obj_group, $loc_type, $param = 1, $page = 1) {
		$values         = $this->semanticsmodel->show_semantics_values($obj_group, $loc_type, $param);
		$values['list'] = $this->semanticsmodel->show_semantics($obj_group, $loc_type);
		$output = array(
			'content'  => $this->adminmodel->get_full_index($obj_group, $loc_type),
			'content2' => $this->load->view('admin/prop_control_table', $values, true),
			'page'     => $page
		);
		return $this->load->view("admin/library2", $output, true);
	}
	
	public function get_full_index($obj_group = 0, $loc_type = 0, $page = 1) {
		$controller = ($this->session->userdata('admin')) ? "admin" : "user";
		$output = array();
		$out    = array(
			'loc_type'	 => $loc_type,
			'obj_group'	 => $obj_group,
			'controller' => $controller
		);

		$out['type_name'] = $this->get_type_name($loc_type);
		$out['name']      = $this->get_group_name($obj_group);
		
		if(!$obj_group) {
			$output = $this->get_library_group_list($obj_group, $controller);
		} else {
			if(!$loc_type){
				$output = $this->get_library_type_list($obj_group, $controller);
			} else {
				$output = $this->get_library_locations_list_by_type($loc_type);
			}
		}
		$out['library'] = implode($output, "\n");
		return $this->load->view("admin/library", $out, true);
	}

	function users_show($id = 0) {
		$access = "";
		$users  = array();
		$output = array( 'admin'  => '', 'valid'  => '', 'active' => '', 'rating' => '', 'name'   => '', 'id'     => $id );
		$result = $this->db->query("SELECT
		`users_admins`.id,
		`users_admins`.class_id,
		`users_admins`.nick,
		DATE_FORMAT(`users_admins`.registration_date, '%d.%m.%Y') AS registration_date,
		CONCAT_WS(' ',`users_admins`.name_f,`users_admins`.name_i,`users_admins`.name_o) AS `fio`,
		SUBSTRING(`users_admins`.`info`, 1, 400) as info,
		`users_admins`.active,
		`users_admins`.rating,
		`users_admins`.valid,
		`users_admins`.access
		FROM
		`users_admins`
		ORDER BY `users_admins`.`class_id` ASC, fio ASC");
		if($result->num_rows()){
			foreach($result->result() as $row){
				$fio = (strlen($row->fio)) ? $row->fio : '<em class="muted">ФИО не указано</em>';
				if ($row->id == $id){
					$access = $row->access;
					$output['admin']  = (($row->class_id === "1") ? ' checked="checked"' : '');
					$output['valid']  = (($row->valid) ? ' checked="checked"' : '');
					$output['active'] = (($row->active) ? ' checked="checked"' : '');
					$output['rating'] = $row->rating;
					$output['name']   = $row->nick."&nbsp;&nbsp;&nbsp;&nbsp;<small>".$row->fio.",&nbsp;".$row->info."</small>";
					$output['id']     = $row->id;
				}
				$string = '<tr><td>'.$row->nick.'</td><td><small>'.$fio.'<br>'.$row->info.'</small></td><td>'.$row->rating.'</td><td>'.(($row->class_id === "1") ? 'Да' : 'Нет').'</td><td>'.(($row->active)   ? 'Да' : 'Нет').'</td><td>'.(($row->valid)    ? 'Да' : 'Нет').'</td><td><a href="/admin/usermanager/'.$row->id.'" class="btn btn-primary btn-mini">Редактировать</span></td></tr>';
				array_push($users, $string);
			}
		}
		$output['layers'] = $this->get_access_layers();
		$output['table']  = implode($users,  "\n");
		return $this->load->view("admin/usermanager", $output, true);
	}

	private function get_access_layers($access) {
		$layers = array();
		$result = $this->db->query('SELECT 
		`objects_groups`.name,
		`objects_groups`.id,'.
		(strlen($access) 
			? 'IF(`objects_groups`.`id` IN('.$access.'), 1, 0) AS granted'
			: '0 AS granted'
		).
		' FROM
		`objects_groups`
		WHERE `objects_groups`.`active`');
		if($result->num_rows()){
			foreach($result->result() as $row){
				$checked = ($row->granted == "1") ? ' checked="checked"' : "";
				$string = '<li><label class="checkbox"><input type="checkbox" name="groups[]" value="'.$row->id.'"'.$checked.'>'.$row->name.'</label></li>';
				array_push($layers, $string);
			}
		}
		return implode($layers, "\n");
	}

	function users_save($id) {
		//$this->output->enable_profiler(TRUE);
		//return false;
		$admin = ($this->input->post('admin') == "1") ? 1 : 2;
		$result = $this->db->query("UPDATE users_admins 
		SET
		users_admins.active   = ?,
		users_admins.valid    = ?,
		users_admins.rating   = ?,
		users_admins.access   = ?,
		users_admins.class_id = ?
		WHERE
		users_admins.id = ?", array(
			$this->input->post('active', true),
			$this->input->post('valid' , true),
			$this->input->post('rating', true),
			implode($this->input->post('groups', true), ", "),
			$admin,
			$this->input->post('id')
		));
		$text = "Администратором ".$this->session->userdata("user_name")." изменены характеристики пользователя #".$this->input->post('id').": active: ".$this->input->post('active', true).", valid: ".$this->input->post('valid' , true).", rating: ".$this->input->post('rating' , true).", class: ".$admin.", access: ".implode($this->input->post('groups', true), ", ");
		$this->usefulmodel->insert_audit($text);
	}
}
/* End of file adminmodel.php */
/* Location: ./application/models/adminmodel.php */