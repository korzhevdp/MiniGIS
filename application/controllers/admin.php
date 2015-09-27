<?php
class Admin extends CI_Controller{
	function __construct(){
		parent::__construct();
		$this->load->helper('url');
		if(!$this->session->userdata('user_id')){
			redirect('login/index/auth');
		}else{
			$this->load->model('usefulmodel');
			$this->load->model('adminmodel');
			$this->session->set_userdata("c_l", 0);
		}
		if(!$this->session->userdata('lang')){
			$this->session->set_userdata('lang', 'en');
		}
	}

	public function index() {
		$this->usefulmodel->check_admin_status();
		$output = array(
			'menu'    => $this->load->view('admin/menu', array(), true)
						.$this->load->view('admin/supermenu', $this->usefulmodel->semantics_supermenu(), true),
			'content' => $this->load->view('admin/startpage', array(), true),
		);
		$this->load->view('admin/view', $output);
	}

	public function library($obj_group = 0, $loc_type = 0, $param = 1, $page = 1) {
		$this->usefulmodel->check_admin_status();
		$output = array(
			'menu'     => $this->load->view('admin/menu', array(), true)
						 .$this->load->view('admin/supermenu', $this->usefulmodel->semantics_supermenu(), true),
			'content'  => $this->adminmodel->get_composite_indexes($obj_group, $loc_type, $param, $page)
		);
		$this->load->view('admin/view', $output);
	}

	public function sheets($mode, $sheet_id="0") {
		$this->usefulmodel->check_admin_status();
		$this->load->model('cachemodel');
		if ($mode === 'save') {
			$this->adminmodel->sheet_save($sheet_id);
			$this->cachemodel->menu_build(1, 0, 'file');
		}
		$output = array(
			'menu'    => $this->load->view('admin/menu', array(), true)
						.$this->load->view('admin/supermenu', $this->usefulmodel->semantics_supermenu(), true),
			'content' => $this->adminmodel->sheet_edit($sheet_id)
		);
		$this->load->view('admin/view', $output);
	}

	public function maps(){
		$this->usefulmodel->check_admin_status();
		$this->load->model('cachemodel');
		$mapset = ($this->input->post('map_view')) ? $this->input->post('map_view') : 0;
		if($this->input->post('save')){
			$this->adminmodel->mc_save();
			$this->cachemodel->menu_build(1, 0, 'file');
			$this->cachemodel->cache_selector_content('file');
		}
		if($this->input->post('new')){
			$mapsetid = $this->adminmodel->mc_new();
			$this->cachemodel->menu_build(1, 0, 'file');
			$this->cachemodel->cache_selector_content('file');
		}
		$output = array(
			'menu'    => $this->load->view('admin/menu', array(), true)
						.$this->load->view('admin/supermenu', $this->usefulmodel->semantics_supermenu(), true),
			'content'	=> $this->load->view('admin/map_content', $this->adminmodel->mc_show($mapset), true),
		);
		$this->load->view('admin/view', $output);
	}

	public function maps_save(){
		/**/
	}

	public function gis($obj = 0){
		$this->usefulmodel->check_admin_status();
		$output = array(
			'menu'    => $this->load->view('admin/menu', array(), true)
						.$this->load->view('admin/supermenu', $this->usefulmodel->semantics_supermenu(), true),
			'content' => $this->adminmodel->gis_objects_show($obj)
		);
		$this->load->view('admin/view', $output);
	}

	public function gis_save(){
		//$this->output->enable_profiler(TRUE);
		$this->usefulmodel->check_admin_status();
		$this->load->model('cachemodel');
		$this->adminmodel->gis_save();
		$this->cachemodel->menu_build(1, 0, 'file');
		$this->cachemodel->cache_selector_content('file');
		$this->cachemodel->build_object_lists();
		redirect("admin/gis");
	}

	public function semantics($obj_group = 0, $obj = 0){
		$this->usefulmodel->check_admin_status();
		$values         = $this->adminmodel->show_semantics_values($obj_group, $obj);
		$values['list'] = $this->adminmodel->show_semantics($obj_group, $obj);
		$output = array(
			'menu'    => $this->load->view('admin/menu', array(), true)
						.$this->load->view('admin/supermenu', $this->usefulmodel->semantics_supermenu(), true),
			'content' => $this->load->view('admin/prop_control_table', $values, true)
		);
		$this->load->view('admin/view', $output);
	}

	public function save_semantics(){
		$this->usefulmodel->check_admin_status();
		$this->adminmodel->save_semantics();
	}

	public function usermanager($id=0){
		$this->usefulmodel->check_admin_status();
		$output = array(
			'menu'    => $this->load->view('admin/menu', array(), true)
						.$this->load->view('admin/supermenu', $this->usefulmodel->semantics_supermenu(), true),
			'content' => $this->adminmodel->users_show($id)
		);
		$this->load->view('admin/view', $output);
	}

	public function user_save(){
		$this->usefulmodel->check_admin_status();
		$this->adminmodel->users_save($this->session->userdata("user_id"));
		redirect("/admin/usermanager/".$this->input->post('id'));
	}
	####################################################
	public function groupmanager($id=0){
		$this->usefulmodel->check_admin_status();
		$output = array(
			'menu'         => $this->load->view('admin/menu', array(), true)
						     .$this->load->view('admin/supermenu', $this->usefulmodel->semantics_supermenu(), true),
			'content'      => $this->adminmodel->groups_show($id)
		);
		$this->load->view('admin/view', $output);
	}

	public function group_save(){
		$this->usefulmodel->check_admin_status();
		$id = $this->adminmodel->group_save();
		redirect('admin/groupmanager/'.$id);
	}
	####################################################
	public function swpropsearch($group = 1, $type = 0, $prop = 0, $page){
		$result = $this->db->query("UPDATE properties_list 
		SET properties_list.searchable = IF(properties_list.searchable = 1, 0, 1) 
		WHERE 
		properties_list.id = ?", array($prop));
		redirect('admin/library/'.$group."/".$type."/".$prop."/".$page);
	}

	public function swpropactive($group = 1, $type = 0, $prop = 0, $page){
		$result = $this->db->query("UPDATE properties_list 
		SET properties_list.active = IF(properties_list.active = 1, 0, 1) 
		WHERE 
		properties_list.id = ?", array($prop));
		redirect('admin/library/'.$group."/".$type."/".$prop."/".$page);
	}
	// user-calls for caching model
	public function cachemap($mode = "browser"){
		$this->load->model('cachemodel');
		$this->cachemodel->cache_selector_content($mode);
	}

	public function cachemenu(){
		$this->load->model('cachemodel');
		$this->cachemodel->cache_docs(1, 'file');
	}

	public function cacheloc($loc_id){
		$this->load->model('cachemodel');
		$this->cachemodel->cache_location($loc_id, 0, 'browser');
	}
	// Reconciler
	/*
	public function reconcile() {
		$result = $this->db->query("DELETE
		FROM `properties_assigned`
		WHERE 
		`properties_assigned`.`property_id` IN (
			SELECT
			`locations_types`.pl_num
			FROM
			`locations_types`
		)");
		$run1 = $this->db->affected_rows();
		$result = $this->db->query("INSERT INTO `properties_assigned` (
		`properties_assigned`.`property_id`,
		`properties_assigned`.`location_id`
		)
		SELECT
		`locations_types`.pl_num,
		`locations`.id
		FROM
		`locations`
		INNER JOIN `locations_types` ON (`locations`.`type` = `locations_types`.id)");
		$run2 = $this->db->affected_rows();
		print "Reconcillation DONE<br>Deleted: ".$run1.",<br>Inserted: ".$run2;
	}
	*/
	public function restore_property_bindings() {
		$result = $this->db->query("SELECT
		`properties_list`.id,
		`properties_list`.selfname,
		`properties_list`.object_group
		FROM
		`properties_list`
		order by `properties_list`.id");
		$input = array();
		if($result->num_rows()){
			foreach($result->result() as $row){
				if(!isset($input[$row->selfname])) { 
					$input[$row->selfname] = array('lowest_id' => $row->id);
				}
				array_push($input[$row->selfname], $row->object_group);
			}
		}
		
		$output = array();
		foreach($input as $key => $data) {
			$id = $data['lowest_id'];
			unset($data['lowest_id']);
			foreach($data as $val) {
				array_push($output, "(".$id.", ".$val.", 1)");
			}
			
		}
		//print_r($output);
		$result = $this->db->query("INSERT INTO
		`properties_bindings`(
		property_id,
		groups,
		searchable)
		VALUES ".implode($output, ", "));
	}

	public function translations($mode = "groups"){
		$this->config->load('translations_g', FALSE);
		$this->config->load('translations_c', FALSE);
		$this->config->load('translations_p', FALSE);
		$this->config->load('translations_l', FALSE);
		$this->config->load('translations_m', FALSE);
		$this->config->load('translations_a', FALSE);
		$this->usefulmodel->check_admin_status();
		$output = array(
			'menu'         => $this->load->view('admin/menu', array(), true)
							 .$this->load->view('admin/supermenu', $this->usefulmodel->semantics_supermenu(), true),
			'content'      => $this->adminmodel->translations($mode)
		);
		$this->load->view('admin/view', $output);
	}

	public function trans_save(){

		$this->adminmodel->trans_save();
		$this->load->model('cachemodel');
		$this->config->load('translations_g');
		$this->config->load('translations_c');
		$this->config->load('translations_p');
		$this->config->load('translations_l');
		$this->config->load('translations_m');
		$this->config->load('translations_a');
		$this->cachemodel->menu_build(1, 0, 'file');
		$this->cachemodel->cache_selector_content('file');
		redirect("/admin/translations/".$this->input->post('type'));
	}
}
/* End of file admin.php */
/* Location: ./system/application/controllers/admin.php */