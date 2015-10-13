<?php
class Editor extends CI_Controller{
	function __construct(){
		parent::__construct();
		$this->load->helper('url');
		if(!$this->session->userdata('user_id')){
			redirect('login/index/auth');
		}else{
			$this->load->model('usefulmodel');
			$this->load->model('editormodel');
		}
	}

	public function edit($location = 0) {
		$output = $this->editormodel->starteditor("edit", $location);
		$this->load->view('editor/altview', $output);
	}

	public function add($type = 0){
		if ($this->session->userdata("c_l") !== 0) {
			redirect("editor/edit/".$this->session->userdata("c_l"));
		}
		$output = $this->editormodel->starteditor("add", $type);
		$this->load->view('editor/altview', $output);
	}

	private function checkdatafullness(){
		if (
			$this->input->post('ttl')          !== FALSE ||
			$this->input->post('attr')         !== FALSE ||
			$this->input->post('name')         !== FALSE ||
			$this->input->post('address')      !== FALSE ||
			$this->input->post('active')       !== FALSE ||
			$this->input->post('contact')      !== FALSE ||
			$this->input->post('type')         !== FALSE ||
			$this->input->post('pr')           !== FALSE ||
			$this->input->post('coords')       !== FALSE //||
			//$this->input->post('coords_array') !== FALSE ||
			//$this->input->post('coords_aux')   !== FALSE
		) {
			return true;
		}
		$this->usefulmodel->insert_audit("Неполный набор данных при сохранении объекта: #".$this->input->post('ttl'));
		return false;
	}

	private function create_location() {
		$this->db->query("INSERT INTO
		`locations`(
			`locations`.location_name,
			`locations`.owner,
			`locations`.`type`,
			`locations`.style_override,
			`locations`.contact_info,
			`locations`.address,
			`locations`.coord_y,
			`locations`.coord_array,
			`locations`.coord_obj,
			`locations`.`date`,
			`locations`.parent,
			`locations`.active,
			`locations`.comments
		) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ? )", array(
			$this->input->post('name'),
			$this->session->userdata("user_id"),
			$this->input->post('type'),
			$this->input->post('attr'),
			$this->input->post('contact'),
			$this->input->post('address'),
			$this->input->post('coords'),
			$this->input->post('coords_array'),
			$this->input->post('coords_aux'),
			0,
			$this->input->post('active'),
			$this->input->post('comments')
		));
		return $this->db->insert_id();
	}

	private function update_location() {
		if (!$this->usefulmodel->check_owner($location_id)) {
			$this->usefulmodel->insert_audit("При сохранении свойств объекта: #".$location_id." - владелец не совпадает");
			redirect('user/library');
		}
		$this->db->query("UPDATE
		`locations` 
		SET
		`locations`.location_name  = ?,
		`locations`.`type`         = ?,
		`locations`.style_override = ?,
		`locations`.contact_info   = ?,
		`locations`.address        = ?,
		`locations`.coord_y        = ?,
		`locations`.coord_array    = ?,
		`locations`.coord_obj      = ?,
		`locations`.parent         = ?,
		`locations`.active         = ?,
		`locations`.comments       = ?
		WHERE
		`locations`.id = ?", array(
			$this->input->post('name'),
			$this->input->post('type'),
			$this->input->post('attr'),
			$this->input->post('contact'),
			$this->input->post('address'),
			$this->input->post('coords'),
			$this->input->post('coords_array'),
			$this->input->post('coords_aux'),
			0,
			$this->input->post('active'),
			$this->input->post('comments'),
			$this->input->post('ttl')
		));
	}

	private function insert_main_property($location_id) {
		$this->db->query("INSERT INTO 
		`properties_assigned` (
			`properties_assigned`.`location_id`,
			`properties_assigned`.`property_id`,
			`properties_assigned`.`value`
		) VALUES (?, (
			SELECT 
			locations_types.pl_num
			FROM
			locations_types
			WHERE 
			locations_types.id = ?
		), 1)", array(
			$location_id,
			$this->input->post("type")
		));
		$this->session->set_userdata("c_l", $location_id);
	}

	private function update_main_property() {
		$this->db->query("DELETE
		FROM `properties_assigned`
		WHERE
		`properties_assigned`.`location_id` = ? AND
		`properties_assigned`.`property_id` IN (
			SELECT locations_types.pl_num
			FROM locations_types
			WHERE
			(locations_types.object_group = (
				SELECT `locations_types`.`object_group`
				FROM `locations_types`
				WHERE `locations_types`.id = ?)
			) AND `locations_types`.`pl_num`
		)", array(
			$this->input->post('ttl'),
			$this->input->post("type")
		));

		$result = $this->db->query("INSERT INTO `properties_assigned` (
			`properties_assigned`.`location_id`,
			`properties_assigned`.`property_id`,
			`properties_assigned`.`value`
		) VALUES (?, (SELECT locations_types.pl_num FROM locations_types where locations_types.id = ?), 1)", array(
			$this->input->post('ttl'),
			$this->input->post("type")
		));
	}

	public function saveobject() {
		$location_id = $this->input->post('ttl');
		if (!$this->checkdatafullness()){
			//print 'console.log("Data is inconsistent. Operation was aborted.")';
			return false;
		}
		if ($location_id == 0 && $location_id !== FALSE) {
			$location_id = $this->create_location();
			$this->insert_main_property($location_id);
		} else {
			if (!$this->usefulmodel->check_owner($location_id)) {
				$this->usefulmodel->insert_audit("При сохранении объекта: #".$location_id." - владелец не совпадает");
				redirect('user/library');
			}
			if ($location_id != $this->session->userdata("c_l")) {
				$this->usefulmodel->insert_audit("При сохранении объекта: #".$location_id." - подмена целевого объекта (".$this->session->userdata("c_l").")");
			}
			$this->update_location();
			$this->update_main_property();
		}

		$this->load->model('cachemodel');
		$this->cachemodel->cache_location($location_id);
		$this->usefulmodel->insert_audit("Объект: #".$location_id." - успешно сохранён и кэширован");
		print "data = { ttl : ".$location_id." }";
	}

	public function saveprops() {
		if (!$this->checkdatafullness()){
			return false;
		}
		$location_id = $this->input->post('ttl');
		if ($location_id) {
			$this->update_location();
		} else {
			$location_id = $this->createobject();
		}
		$output = array();
		$ids    = array();
		$checks = $this->input->post("check");
		$text   = $this->input->post("te");
		$tarea  = $this->input->post("ta");
		$select = $this->input->post("se");

		if ($checks  && sizeof($checks)) {
			foreach($checks as $val){
				array_push($output, '('.$location_id.', '.$val.', 1)');
				array_push($ids, $val);
			}
		}
		if ($text   && sizeof($text)) {
			foreach($text as $key => $val){
				array_push($output, '('.$location_id.', '.$key.', \''.$val.'\')');
				array_push($ids, $key);
			}
		}
		if ($tarea  && sizeof($tarea)) {
			foreach($tarea as $key => $val){
				array_push($output, '('.$location_id.', '.$key.', \''.$val.'\')');
				array_push($ids, $key);
			}
		}
		if ($select && sizeof($select)) {
			foreach($select as $key => $val){
				array_push($output, '('.$location_id.', '.$key.', \''.$val.'\')');
				array_push($ids, $key);
			}
		}
		$this->insert_properties($output, $ids);
		$this->load->model('cachemodel');
		$this->cachemodel->cache_location($location_id);
		$this->usefulmodel->insert_audit("Объект: #".$location_id." - успешно сохранён и кэширован");
		print "data = { ttl : ".$location_id." }";
	}

	private function insert_properties($output, $ids) {
		if (sizeof($ids)) {
			// page number of the property
			$result = $this->db->query("SELECT DISTINCT `properties_list`.page FROM `properties_list` WHERE `properties_list`.`id` IN(".implode($ids, ",").")");
			if($result->num_rows() === 1) {
				$row = $result->row(0);
				$this->db->query("DELETE FROM
				properties_assigned
				WHERE
				properties_assigned.property_id IN (
					SELECT properties_list.id FROM properties_list WHERE properties_list.page = ?
				)
				AND properties_assigned.location_id = ?", array($row->page, $location_id));
				$this->db->query("INSERT INTO properties_assigned (
					properties_assigned.location_id,
					properties_assigned.property_id,
					properties_assigned.value
				) VALUES ".implode($output, ",\n"));
				//print 'console.log("This save was successful")';
			} else {
				print 'console.log("Page flood detected. Operation was aborted. Use a proper tool to access storage")';
				return false;
			}
		}
	}

	public function geosemantics($mode=1) {
		$this->usefulmodel->check_admin_status();
		$output = $this->editormodel->geoeditor($this->config->item("geo_module_group"), $mode);
		$this->load->view('editor/geoview', $output);
	}

	####################################################
	#AJAX-SECTION

	public function get_property_page(){
		if(!$this->session->userdata('user_id')){
			print "Время работы в текущей сессии истекло.<br>Завершите работу и введите имя пользователя и пароль заново";
			//exit;
		}
		if($this->input->post("loc") && !$this->usefulmodel->check_owner($this->input->post("loc"))){
			print "У вас нет прав просматривать наборы свойств по этому объекту";
			return false;
		}
		//$this->output->enable_profiler(TRUE);
		print $this->editormodel->show_form_content($this->input->post("group"), $this->input->post("loc"), $this->input->post("page"));
	}

	public function get_shedule(){
		$this->editormodel->get_schedule($this->input->post("location"));
	}

	public function get_context(){
		$this->editormodel->get_context();
	}

	public function save_shedule(){
		//$this->output->enable_profiler(TRUE);
		$this->editormodel->save_shedule();
	}
}
/* End of file editor.php */
/* Location: ./system/application/controllers/editor.php */