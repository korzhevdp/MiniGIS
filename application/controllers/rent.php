<?php
class Rent extends CI_Controller {
	function __construct(){
		parent::__construct();
		//$this->output->enable_profiler(TRUE);
		$this->load->model('rentmodel');
		$this->load->model('usefulmodel');
		if(!$this->session->userdata('common_user')){
			$this->session->set_userdata('common_user', md5(rand(0,9999).'zy'.$this->input->ip_address()));
		}
		if (strlen($this->session->userdata('user_id')) !== 40){
			$this->load->helper('url');
			redirect("login");
		}
	}

	function index(){
		$act['yandex_key']=$this->config->item('yandex_key');
		$act['maps_center']=$this->config->item('maps_center');
		$act['menu']=$this->load->view('cache/menus/menu',array(),TRUE).$this->usefulmodel->_rent_menu();
		//$act['content'] = $this->rentmodel->_list_build();
		$act['title']=$this->config->item('site_title_start');
		$act['typeslist']=$this->load->view('cache/menus/typeslist_6',array(),TRUE);
		$this->load->view('rent/view',$act);
	}
}

/* End of file rent.php */
/* Location: ./system/application/controllers/rent.php */