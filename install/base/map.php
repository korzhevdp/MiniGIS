<?php
class Map extends CI_Controller {
	function __construct(){
		parent::__construct();
		$this->load->model('mapmodel');
		$this->load->model('usefulmodel');
		//$this->output->enable_profiler(TRUE);
	}

	public function index(){
		$this->simple(1);
	}

	public function mapdata($mapset = 1){
		return $this->mapmodel->map_data_get($mapset);
	}
	
	public function simple($mapset = 1){
		$act = $this->mapdata($mapset);
		$this->load->view('frontend/frontend_map2', $act);
	}

	public function type($type){
		$act = array(
			'footer'		=> "",
			'otype'			=> $type,
			'mapset'		=> 0,
			'menu'			=> $this->load->view('cache/menus/menu', array(), true),
			'keywords'		=> $this->config->item('map_keywords'),
			'map_header'	=> "Объекты по типам",
			'map_center'	=> $this->config->item('map_center'),
			'title'			=> $this->config->item('site_title_start')." Интерактивная карта"
		);
		$this->load->view('frontend/frontend_typemap', $act);
	}

	public function own($mapset = 1){
		$act = $this->mapdata($mapset);
		$this->load->view('frontend/frontend_ownmap', $act);
	}
}

/* End of file map.php */
/* Location: ./system/application/controllers/map.php */