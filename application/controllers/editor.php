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

	private function check_aux_points(){
		$output    = array();
		if(strlen($this->input->post('coords_aux'))) {
			$data      = array();
			$auxpoints = explode(",", $this->input->post('coords_aux'));
			$contour   = explode(";", $this->input->post('coords_array'));
			$result    = $this->db->query("SELECT 
			`locations`.id,
			`locations`.coord_y
			FROM
			`locations`
			WHERE `locations`.`id` IN (".$this->input->post('coords_aux').")");
			if($result->num_rows()){
				foreach($result->result() as $row){
					$data[$row->id] = $row->coord_y;
				}
			}
			foreach ($auxpoints as $val){
				if(in_array($data[$val], $contour)) {
					$output[$val] = $val;
				}
			}
		}
		return implode($output, ",");
	}

	private function create_location() {
		$address   = str_replace("'", "&quot;", $this->input->post('address'));
		$name      = str_replace("'", "&quot;", $this->input->post('name'));
		$contact   = str_replace("'", "&quot;", $this->input->post('contact'));
		$attr      = str_replace("'", "&quot;", $this->input->post('attr'));
		$type      = preg_replace("[^0-9]", "", $this->input->post('type'));
		$type      = (strlen($type)) ? $type : 1;
		//$auxpoints = $this->check_aux_points();
		$auxpoints = $this->input->post("coords_aux");
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
			$name,
			$this->session->userdata("user_id"),
			$type,
			$attr,
			$contact,
			$address,
			$this->input->post('coords'),
			$this->input->post('coords_array'),
			$auxpoints,
			0,
			$this->input->post('active'),
			$this->input->post('comments')
		));
		$ttl = $this->db->insert_id();
		$this->insert_composites($this->input->post('ttl'), $auxpoints);
		$this->recache_datasets();
		return $ttl;
	}

	private function update_location() {
		if (!$this->usefulmodel->check_owner($this->input->post('ttl'))) {
			$this->usefulmodel->insert_audit("При сохранении свойств объекта: #".$this->input->post('ttl')." - владелец не совпадает");
			redirect('user/library');
		}
		if ($this->input->post('ttl') != $this->session->userdata("c_l")) {
			$this->usefulmodel->insert_audit("При сохранении объекта: #".$this->input->post('ttl')." - подмена целевого объекта (".$this->session->userdata("c_l").")");
			redirect('user/library');
		}
		$address = str_replace("'", "&quot;", $this->input->post('address'));
		$name    = str_replace("'", "&quot;", $this->input->post('name'));
		$contact = str_replace("'", "&quot;", $this->input->post('contact'));
		$attr    = str_replace("'", "&quot;", $this->input->post('attr'));
		$type    = preg_replace("[^0-9]", "", $this->input->post('type'));
		$type    = (strlen($type)) ? $type : 1;
		//$auxpoints = $this->check_aux_points();
		$auxpoints = $this->input->post("coords_aux");
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
			$name,
			$type,
			$attr,
			$contact,
			$address,
			$this->input->post('coords'),
			$this->input->post('coords_array'),
			$auxpoints,
			0,
			$this->input->post('active'),
			$this->input->post('comments'),
			$this->input->post('ttl')
		));
		$this->insert_composites($this->input->post('ttl'), $auxpoints);
	}

	private function insert_composites($source, $data) {
		$obj_nodes = explode(",", $data);
		$composites = array();
		foreach($obj_nodes as $obj) {
			if (strlen($obj)) {
				array_push($composites, "(".$source.", ".$obj.")");
			}
		}
		if (sizeof($composites)) {
			$this->db->query("DELETE FROM composites WHERE `composites`.parent = ?", array($source));
			$this->db->query("INSERT INTO `composites`( 
				`composites`.parent,
				`composites`.location
			) VALUES ".implode($composites, ",\n"));
		}
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
		`properties_assigned`.`location_id` = ?
		AND `properties_assigned`.`property_id` IN (
			SELECT locations_types.pl_num
			FROM locations_types
			WHERE
			(locations_types.object_group = (
				SELECT `locations_types`.`object_group`
				FROM `locations_types`
				WHERE `locations_types`.id = ?)
			)
			AND `locations_types`.`pl_num`
		)", array(
			$this->input->post('ttl'),
			$this->input->post("type")
		));

		$result = $this->db->query("INSERT INTO `properties_assigned` (
			`properties_assigned`.`location_id`,
			`properties_assigned`.`property_id`,
			`properties_assigned`.`value`
		) VALUES (?, (
			SELECT 
			locations_types.pl_num
			FROM
			locations_types
			WHERE locations_types.id = ?
		), 1)", array(
			$this->input->post('ttl'),
			$this->input->post("type")
		));
	}

	public function saveobject() {
		$location_id = $this->input->post('ttl');
		if (!$this->checkdatafullness()){
			return false;
		}
		if ($location_id) {
			$this->update_location();
			$this->update_main_property();
		} else {
			$location_id = $this->create_location();
			$this->insert_main_property($location_id);
		}
		$this->load->model('cachecatalogmodel');
		$this->cachecatalogmodel->cache_location($location_id);
		$this->load->model('mapsetmodel');
		$this->mapsetmodel->recache_datasets($location_id);
		$this->usefulmodel->insert_audit("Объект: #".$location_id." - успешно сохранён и кэширован");
		print "data = { ttl : ".$location_id." }";
	}

	public function saveprops() {
		$location_id = $this->input->post('ttl');
		if (!$this->checkdatafullness()){
			return false;
		}
		if ($location_id) {
			$this->update_location();
			$this->update_main_property();
		} else {
			$location_id = $this->create_location();
			$this->insert_main_property($location_id);
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
		$this->insert_properties($output, $ids, $location_id);
		$this->load->model('cachecatalogmodel');
		$this->cachecatalogmodel->cache_location($location_id);
		$this->load->model('mapsetmodel');
		$this->mapsetmodel->recache_datasets($location_id);
		$this->usefulmodel->insert_audit("Объект: #".$location_id." - успешно сохранён и кэширован");
		//print $this->input->post('coords_aux');
		print "data = { ttl : ".$location_id." }";
	}

	private function insert_properties($output, $ids, $location_id) {
		if (sizeof($ids)) {
			$result = $this->db->query("SELECT DISTINCT
			`properties_list`.page
			FROM 
			`properties_list`
			WHERE
			`properties_list`.`id` IN(".implode($ids, ", ").")");
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
	/*
	public function geosemantics($mode=1) {
		$this->usefulmodel->check_admin_status();
		$output = $this->editormodel->geoeditor($this->config->item("geo_module_group"), $mode);
		$this->load->view('editor/geoview', $output);
	}
	*/

	####################################################
	#AJAX-SECTION

	public function get_property_page() {
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

	public function save_image_order() {
		if(!is_array($this->input->post("order"))){
			return false;
		}
		$limit = 0;
		$input = array();
		$ids   = array();
		if($this->config->item("image_limit")) {
			$limit  = $this->config->item("image_limit");
			$result = $this->db->query("SELECT 
			`payments`.id
			FROM
			`payments`
			WHERE `payments`.`location_id` = ?
			AND `payments`.`paid`", $this->session->userdata("c_l"));
			if($result->num_rows()){
				$limit = $this->config->item("image_paid_limit");
			}
		}
		foreach($this->input->post("order") as $key=>$val) {
			if (sizeof($input) < $limit) {
				$string = "WHEN ".(integer) $val." THEN ".sizeof($input) * 10;
				array_push($input, $string);
				array_push($ids, (integer) $val);
			}
		}
		$this->db->query("UPDATE images
		SET `images`.`order` = (
			CASE `images`.`id`
				".implode($input, "\n")."
			END)
		WHERE images.id IN(".implode($ids, ", ").")
		AND images.location_id = ?", array($this->session->userdata("c_l")));
	}

	public function delete_image() {
		$lid   = $this->input->post("lid", true);
		$image = $this->input->post("image", true);
		if ($image === FALSE || $lid === FALSE) {
			return false;
		}
		if( !$this->usefulmodel->check_owner($lid)
			|| $lid != $this->session->userdata("c_l")
		) {
			return false;
		}
		$result = $this->db->query("SELECT 
		`images`.filename
		FROM
		`images`
		WHERE `images`.`hash` = ?", array($image));
		if($result->num_rows()){
			$row = $result->row(0);
			unlink("./uploads/".$row->filename);
			unlink("./uploads/small/".$lid."/".$row->filename);
			unlink("./uploads/mid/".$lid."/".$row->filename);
			unlink("./uploads/full/".$lid."/".$row->filename);
			$this->db->query("DELETE
			FROM
			`images`
			WHERE `images`.`hash` = ?", array($image));
		}
	}
}
/* End of file editor.php */
/* Location: ./system/application/controllers/editor.php */