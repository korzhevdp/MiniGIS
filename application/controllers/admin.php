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
		$this->library();
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

		$this->load->model('docmodel');
		if ($mode === 'save') {
			$this->docmodel->sheet_save($sheet_id);
		}
		$output = array(
			'menu'    => $this->load->view('admin/menu', array(), true)
						.$this->load->view('admin/supermenu', $this->usefulmodel->semantics_supermenu(), true),
			'content' => $this->docmodel->sheet_edit($sheet_id)
		);
		$this->load->view('admin/view', $output);
	}

	public function maps(){
		$this->usefulmodel->check_admin_status();
		$this->load->model('cachemodel');
		$this->load->model('mcmodel');
		$mapset = ($this->input->post('map_view')) ? $this->input->post('map_view') : 0;
		if($this->input->post('save')){
			$this->mcmodel->mc_save();
			$this->cachemodel->menu_build(1, 0, 'file');
			$this->cachemodel->cache_selector_content('file');
		}
		if($this->input->post('new')){
			$mapsetid = $this->mcmodel->mc_new();
			$this->cachemodel->menu_build(1, 0, 'file');
			$this->cachemodel->cache_selector_content('file');
		}
		$output = array(
			'menu'    => $this->load->view('admin/menu', array(), true)
						.$this->load->view('admin/supermenu', $this->usefulmodel->semantics_supermenu(), true),
			'content'	=> $this->load->view('admin/map_content', $this->mcmodel->mc_show($mapset), true),
		);
		$this->load->view('admin/view', $output);
	}

	public function gis($obj = 0){
		$this->load->model('gismodel');
		$this->usefulmodel->check_admin_status();
		$output = array(
			'menu'    => $this->load->view('admin/menu', array(), true)
						.$this->load->view('admin/supermenu', $this->usefulmodel->semantics_supermenu(), true),
			'content' => $this->gismodel->gis_objects_show($obj)
		);
		$this->load->view('admin/view', $output);
	}

	public function gis_save(){
		//$this->output->enable_profiler(TRUE);
		$this->usefulmodel->check_admin_status();
		$this->load->model('gismodel');
		$this->load->model('cachemodel');
		$this->gismodel->gis_save();
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
		$this->load->model('gismodel');
		$output = array(
			'menu'         => $this->load->view('admin/menu', array(), true)
						     .$this->load->view('admin/supermenu', $this->usefulmodel->semantics_supermenu(), true),
			'content'      => $this->gismodel->groups_show($id)
		);
		$this->load->view('admin/view', $output);
	}

	public function group_save(){
		$this->usefulmodel->check_admin_status();
		$this->load->model('gismodel');
		$id = $this->gismodel->group_save();
		redirect('admin/groupmanager/'.$id);
	}
	####################################################
	public function swpropsearch($group = 1, $type = 0, $prop = 0, $page){
		$result = $this->db->query("
		UPDATE
		`properties_bindings`
		SET
		`properties_bindings`.searchable = IF(`properties_bindings`.searchable = 1, 0, 1)
		WHERE 
		`properties_bindings`.property_id = ?
		AND `properties_bindings`.groups  = ?", array($prop, $group));
		redirect('admin/library/'.$group."/".$type."/".$prop."/".$page);
	}

	public function swpropactive($group = 1, $type = 0, $prop = 0, $page){
		$result = $this->db->query("UPDATE properties_list 
		SET properties_list.active = IF(properties_list.active = 1, 0, 1) 
		WHERE 
		properties_list.id = ?", array($prop));
		redirect('admin/library/'.$group."/".$type."/".$prop."/".$page);
	}

	public function translations($mode = "groups"){
		$this->usefulmodel->check_admin_status();
		$this->config->load('translations_g', FALSE);
		$this->config->load('translations_c', FALSE);
		$this->config->load('translations_p', FALSE);
		$this->config->load('translations_l', FALSE);
		$this->config->load('translations_m', FALSE);
		$this->config->load('translations_a', FALSE);
		$this->load->model('transmodel');
		$output = array(
			'menu'     => $this->load->view('admin/menu', array(), true)
						 .$this->load->view('admin/supermenu', $this->usefulmodel->semantics_supermenu(), true),
			'content'  => $this->transmodel->translations($mode)
		);
		$this->load->view('admin/view', $output);
	}

	public function trans_save(){
		$this->load->model('transmodel');
		$this->transmodel->trans_save();
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