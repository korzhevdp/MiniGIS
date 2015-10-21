<?php
class Admin extends CI_Controller{
	function __construct(){
		parent::__construct();
		//$this->output->enable_profiler(TRUE);
		$this->load->helper('url');
		//$this->db->query("SET lc_time_names = 'ru_RU'");
		if(!$this->session->userdata('user_id')){
			redirect('login/index/auth');
		}else{
			$this->load->model('cachemodel');
			$this->load->model('usefulmodel');
			$this->load->model('adminmodel');
			$this->load->library('upload');
		}
	}

	function index(){
		$output['menu']      = $this->load->view('admin/menu', '', true);
		if($this->session->userdata('user_class') == md5("secret_userclass1")){
			$supermenu       = $this->usefulmodel->semantics_supermenu();
			$output['menu'] .= $this->load->view('admin/supermenu', $supermenu, true);
		}
		$output['content']   = $this->load->view('admin/startpage', array(), true);
		$this->load->view('admin/view', $output);
	}

	function library($obj_group=1, $loc_type=0){
		//$this->output->enable_profiler(TRUE);
		$output['menu']=$this->load->view('admin/menu','',true);
		if($this->session->userdata('user_class') == md5("secret_userclass1")){
			$supermenu=$this->usefulmodel->semantics_supermenu();
			$output['menu'].=$this->load->view('admin/supermenu', $supermenu, true);
		}
		$output['content'] = $this->adminmodel->get_full_index($obj_group, $loc_type);
		$this->load->view('admin/view', $output);
	}

	function sheets($mode, $sheet_id="0"){
		$output['menu']=$this->load->view('admin/menu','',true);
		if($this->session->userdata('user_class') == md5("secret_userclass1")){
			$supermenu=$this->usefulmodel->semantics_supermenu();
			$output['menu'].=$this->load->view('admin/supermenu', $supermenu, true);
			switch ($mode) {
				case "edit":
					$output['content']=$this->adminmodel->sheet_edit($sheet_id);
				break;
				case "save":
					$this->adminmodel->sheet_save($sheet_id);
					$this->cachemodel->menu_build(1, $this->config->item('mod_housing_root'), $this->config->item('mod_gis'));
					redirect('admin/sheets/edit/'.$sheet_id);
				break;
			}
		}else{
			$this->session->sess_destroy();
			redirect('admin');
		}

		$this->load->view('admin/view',$output);
	}

	function maps(){
		/* modes: show, save, new */
		$mapset = ($this->input->post('map_view')) ? $this->input->post('map_view') : 1;
		$output = array(
			'content'	=> $this->load->view('admin/map_content', $this->adminmodel->mc_show($mapset), true),
			'menu'		=> $this->load->view('admin/menu', array(), true)
		);
		if($this->session->userdata('user_class') == md5("secret_userclass1")){
			$output['menu'] .= $this->load->view('admin/supermenu', $this->usefulmodel->semantics_supermenu(), true);
		}else{
			$this->session->sess_destroy();
			redirect('admin');
		}
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
		$this->load->view('admin/view', $output);
	}

	function gis($obj=0){
		if($this->session->userdata('user_class') == md5("secret_userclass1")){
			$output = array(
				'menu'		=> $this->load->view('admin/menu', '', true).$this->load->view('admin/supermenu', $this->usefulmodel->semantics_supermenu(), true),
				'content'	=> $this->adminmodel->gis_objects_show($obj)
			);
			$this->load->view('admin/view', $output);
		}else{
			$this->session->sess_destroy();
			redirect('admin');
		}
	}

	function gis_save(){
		//$this->output->enable_profiler(TRUE);
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

	function semantics($obj_group=1, $obj=0){
		if($this->session->userdata('user_class') == md5("secret_userclass1")){
			$supermenu = $this->usefulmodel->semantics_supermenu();
			$values         = $this->adminmodel->show_semantics_values($obj_group, $obj);
			$values['list'] = $this->adminmodel->show_semantics($obj_group);
			$output = array(
				'menu'    => $this->load->view('admin/menu', array(), true).$this->load->view('admin/supermenu', $supermenu, true),
				'content' => $this->load->view('admin/prop_control_table', $values, true)
			);
			$this->load->view('admin/view', $output);
		}else{
			$this->session->sess_destroy();
			redirect('admin');
		}
	}

	function save_semantics(){
		$this->adminmodel->save_semantics();
	}

	function sync($location_id=0){ //mod_housing
		($location_id == 'all') ? $this->adminmodel->_params_sync_all() : $this->adminmodel->_params_sync_traversal($location_id);
	}

	function usermanager($id=0,$mode='show'){
		if($this->session->userdata('user_class') == md5("secret_userclass1")){
			if($mode == 'show'){
				$supermenu=$this->usefulmodel->semantics_supermenu();
				$output['menu']     = $this->load->view('admin/menu','',true);
				$output['menu']    .= $this->load->view('admin/supermenu',$supermenu,true);
				$output['content']  = '<h1>Управление пользователями. <small>Рейтинг</small></h1>';
				$output['content'] .= $this->adminmodel->users_show();
				$this->load->view('admin/view', $output);
			}
			if($mode == 'save'){
				if($id){
					$this->adminmodel->users_save($id);
				}
				redirect("admin/usermanager");
			}
		}else{
			$this->session->sess_destroy();
			redirect('admin');
		}
	}
	
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
}
/* End of file admin.php */
/* Location: ./system/application/controllers/admin.php */