<?php

class Monitoring extends CI_Controller {
	function __construct(){
		parent::__construct();
		$this->load->model('monmodel');
	}

/*
		if(!$this->session->userdata('user_id')){

			redirect('login/index/auth');
		}else{
*/


	public function situation(){
		$act = array(
			'map_center' => $this->config->item('map_center'),
			'keywords'   => $this->config->item('maps_keywords'),
			'map_type'   => 1
		);
		$this->load->view('monitoring/queue',$act);
	}

	public function object($id){
		$data = $this->monmodel->object_get($id);
		$act = array(
			'map_center' => $this->config->item('map_center'),
			'keywords'   => $this->config->item('maps_keywords'),
			'map_type'   => 1,
			'content'    => $this->load->view('frontend/std_view', $data)
		);
		$this->load->view('frontend/frontend_nomap2', $act);
	}


	## AJAX-section

	public function current(){
		$avar_group = 8;
		return $this->monmodel->current_get($avar_group);
	}

}

/* End of file welcome.php */
/* Location: ./system/application/controllers/welcome.php */