<?php
class Admin extends CI_Controller{
	function __construct(){
		parent::__construct();
		$this->load->helper('url');
		if(!$this->session->userdata('user_id')){
			redirect('login/index/auth');
		}else{
			$this->load->model('cachemodel');
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

	public function library($obj_group=0, $loc_type=0) {
		$this->usefulmodel->check_admin_status();
		$output = array(
			'menu'    => $this->load->view('admin/menu', array(), true)
						.$this->load->view('admin/supermenu', $this->usefulmodel->semantics_supermenu(), true),
			'content' => $this->adminmodel->get_full_index($obj_group, $loc_type)
		);
		$this->load->view('admin/view', $output);
	}

	public function sheets($mode, $sheet_id="0") {
		$this->usefulmodel->check_admin_status();
		if ($mode === 'save') {
			$this->adminmodel->sheet_save($sheet_id);
			$this->cachemodel->menu_build(1, $this->config->item('mod_housing_root'), $this->config->item('mod_gis'));
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
		$mapset = ($this->input->post('map_view')) ? $this->input->post('map_view') : 0;
		if($this->input->post('save')){
			$this->adminmodel->mc_save();
			$this->cachemodel->cache_selector_content($this->input->post('mapset'));
			$this->cachemodel->menu_build(1, $this->config->item('mod_housing_root'), $this->config->item('mod_gis'));
		}
		if($this->input->post('new')){
			$mapsetid = $this->adminmodel->mc_new();
			$this->cachemodel->cache_selector_content($mapsetid);
			$this->cachemodel->menu_build(1, $this->config->item('mod_housing_root'), $this->config->item('mod_gis'));
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
		$this->usefulmodel->check_admin_status();
		$this->adminmodel->gis_save();
		$result = $this->db->query("SELECT 
		map_content.id
		FROM
		map_content
		WHERE
		(map_content.a_layers = ?)", array(
			$this->input->post('obj_group', true)
		));
		if($result->num_rows){
			foreach($result->result() as $row){
				$this->cachemodel->cache_selector_content($row->id);
			}
		}
		$this->cachemodel->menu_build(1, $this->config->item('mod_housing_root'), $this->config->item('mod_gis'));
		$this->cachemodel->_build_object_lists();
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
			'menu'    => $this->load->view('admin/menu', array(), true)
						.$this->load->view('admin/supermenu', $this->usefulmodel->semantics_supermenu(), true),
			'content' => $this->adminmodel->groups_show($id)
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

	public function cachemap($mapset = 1, $mode = "file"){
		$this->cachemodel->cache_selector_content($mapset, $mode);
	}
}
/* End of file admin.php */
/* Location: ./system/application/controllers/admin.php */