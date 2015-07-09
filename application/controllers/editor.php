<?php
class Editor extends CI_Controller{
	function __construct(){
		parent::__construct();
		//$this->output->enable_profiler(TRUE);
		$this->db->query("SET lc_time_names = 'ru_RU'");
		if(!$this->session->userdata('user_id')){
			$this->load->helper('url');
			redirect('login/index/auth');
		}else{
			$this->load->model('usefulmodel');
			$this->load->model('editormodel');
		}
	}

	public function forms($location = 0, $type = 0){
		$output = array();
		//$location = (!$location || !$this->usefulmodel->check_owner($location)) ? $this->session->userdata('init_loc') : $location;
		$output = $this->editormodel->starteditor($location, $type);
		$output['menu']=$this->load->view('admin/menu', '', true);
		if($this->session->userdata('user_class') == md5("secret_userclass1")){
			$supermenu=$this->usefulmodel->semantics_supermenu();
			$output['menu'].=$this->load->view('admin/supermenu', $supermenu, true);
		}
		$this->load->view('editor/view',$output);
	}

	public function saveobject(){
		//$this->output->enable_profiler(TRUE);
		//return false;
		if($this->input->post('id')){
			if (!$this->usefulmodel->check_owner($this->input->post('id'))){
				print "владелец не совпадает";
				redirect('admin/library');
			}
			/*
			if($this->input->post('id') !== $this->session->userdata("c_l")){
				print "подмена целевого объекта";
				redirect('admin/library');
			}
			*/
			$result = $this->db->query("UPDATE
			`locations`
			SET
			`locations`.`type` = ?,
			`locations`.`coord_y` = ?,
			`locations`.`active` = ?
			WHERE
			`locations`.`id` = ?", array(
				$this->input->post("type"),
				$this->input->post("coord_y"),
				$this->input->post("active"),
				$this->input->post('id')
			));
			$result = $this->db->query("DELETE
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
				$this->input->post('id'),
				$this->input->post("type")
			));
			$result = $this->db->query("INSERT INTO `properties_assigned` (
				`properties_assigned`.`location_id`,
				`properties_assigned`.`property_id`,
				`properties_assigned`.`value`
			) VALUES (?, (SELECT locations_types.pl_num FROM locations_types where locations_types.id = ?), 1)", array(
				$this->input->post('id'),
				$this->input->post("type")
			));
		}

		$this->load->model('cachemodel');
		$this->cachemodel->cache_location($this->input->post('id'));
		$this->load->helper('url');
		redirect("editor/forms/".$this->input->post('id'));
	}

	public function saveprops(){
		//$this->output->enable_profiler(TRUE);
		$output = array();
		$ids    = array();
		$checks = $this->input->post("check");
		$text   = $this->input->post("te");
		$tarea  = $this->input->post("ta");
		/*
		addr	Адрес объекта
		lid	578
		name	Новый объект
		style	twirl#metroMoscowIcon
		*/
		$result = $this->db->query("UPDATE
		`locations`
		SET
		`locations`.location_name = ?,
		`locations`.address = ?,
		`locations`.style_override = ?,
		`locations`.contact_info = ?
		WHERE
		`locations`.`id` = ?", array(
			$this->input->post("name"),
			$this->input->post("addr"),
			$this->input->post("style"),
			$this->input->post("cont"),
			$this->input->post("lid")
		));


		if($checks && sizeof($checks)){
			foreach($checks as $val){
				array_push($output, '('.$this->input->post("lid").', '.$val.', 1)');
				array_push($ids, $val);
			}
		}
		if($text && sizeof($text)){
			foreach($text as $key=>$val){
				array_push($output, '('.$this->input->post("lid").', '.$key.', \''.$val.'\')');
				array_push($ids, $key);
			}
		}
		if($tarea && sizeof($tarea)){
			foreach($tarea as $key=>$val){
				array_push($output, '('.$this->input->post("lid").', '.$key.', \''.$val.'\')');
				array_push($ids, $key);
			}
		}
		if(sizeof($ids)){
			$result = $this->db->query("SELECT DISTINCT `properties_list`.page FROM `properties_list` WHERE `properties_list`.`id` IN(".implode($ids, ",").")");
			if($result->num_rows() == 1){
				$row = $result->row(0);
				//print "OK! page: ".$row->page;
				$page = $row->page;
				$this->db->query("DELETE FROM
				properties_assigned
				WHERE properties_assigned.property_id IN (
					SELECT properties_list.id FROM properties_list WHERE properties_list.page = ?
				) AND properties_assigned.location_id = ?", array($page, $this->input->post("lid")));
				$this->db->query("INSERT INTO properties_assigned (
					properties_assigned.location_id,
					properties_assigned.property_id,
					properties_assigned.value
				) VALUES ".implode($output, ",\n"));
			}else{
				print "NO! Flooding page: ".$row->page;
				return false;
			}
		}
//		print implode($output);
	}

	public function geosemantics($mode=1){
		//$this->output->enable_profiler(TRUE);
		if($this->session->userdata('user_class') !== md5("secret_userclass1")){
			print "not allowed!";
		}else{
			$output = $this->editormodel->geoeditor($this->config->item("geo_module_group"), $mode);
			$output['menu'] = $this->load->view('admin/menu','',true);
			$supermenu = $this->usefulmodel->semantics_supermenu();
			$this->load->view('editor/geoview',$output);
		}
	}

	####################################################
	#AJAX-SECTION

	public function get_property_page($obj_group, $location, $page){
		if(!$this->session->userdata('user_id')){
			print "Время работы в текущей сессии истекло.<br>Завершите работу и введите имя пользователя и пароль заново";
			exit;
		}
		//$this->load->model('usefulmodel');
		//$this->load->model('editormodel');
		if($location && !$this->usefulmodel->check_owner($location)){
			print "У вас нет прав просматривать наборы свойств по этому объекту";
			return false;
		}
		print $this->editormodel->show_form_content($obj_group, $location, $page);
	}

	public function get_context(){
		$this->editormodel->get_context();
	}
}
/* End of file editor.php */
/* Location: ./system/application/controllers/editor.php */