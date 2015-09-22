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

	public function library($obj_group = 0, $loc_type = 0, $page = 1) {
		$this->usefulmodel->check_admin_status();
		$output = array(
			'menu'     => $this->load->view('admin/menu', array(), true)
						 .$this->load->view('admin/supermenu', $this->usefulmodel->semantics_supermenu(), true),
			'content'  => $this->adminmodel->get_composite_indexes($obj_group, $loc_type, $page)
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
			$this->cachemodel->cache_selector_content('file');
			$this->cachemodel->menu_build(1, 0, 'file');
		}
		if($this->input->post('new')){
			$mapsetid = $this->adminmodel->mc_new();
			$this->cachemodel->cache_selector_content('file');
			$this->cachemodel->menu_build(1, 0, 'file');
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

	public function semantics($obj_group = 1, $obj = 0){
		$this->usefulmodel->check_admin_status();
		$values         = $this->adminmodel->show_semantics_values($obj_group, $obj);
		$values['list'] = $this->adminmodel->show_semantics($obj_group);
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
	public function swpropsearch($group = 1, $prop = 0){
		$result = $this->db->query("UPDATE properties_list 
		SET properties_list.searchable = IF(properties_list.searchable = 1, 0, 1) 
		WHERE 
		properties_list.id = ?", array($prop));
		redirect('admin/semantics/'.$group);
	}

	public function swpropactive($group = 1, $prop = 0){
		$result = $this->db->query("UPDATE properties_list 
		SET properties_list.active = IF(properties_list.active = 1, 0, 1) 
		WHERE 
		properties_list.id = ?", array($prop));
		redirect('admin/semantics/'.$group);
	}

	public function cachemap($mode = "file"){
		$this->load->model('cachemodel');
		$this->cachemodel->cache_selector_content($mode);
	}

	public function cachemenu(){
		$this->load->model('cachemodel');
		$this->cachemodel->cache_docs(1, 'file');
	}


	public function translations($mode="groups"){
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