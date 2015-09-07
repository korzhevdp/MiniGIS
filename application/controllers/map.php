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
		$map_header = "";
		$result = $this->db->query("SELECT 
			CONCAT_WS( ' - ', `objects_groups`.name, `locations_types`.name ) AS name
			FROM
			`locations_types`
			INNER JOIN `objects_groups` ON (`locations_types`.object_group = `objects_groups`.id)
			WHERE `locations_types`.`id` = ? 
			LIMIT 1", array($type));
		if($result->num_rows()){
			$row = $result->row(0);
			$map_header = $row->name;
		}
		$act = array(
			'footer'		=> $this->load->view('frontend/page_footer', array(), true),
			'otype'			=> $type,
			'mapset'		=> 0,
			'menu'			=> $this->load->view('cache/menus/menu', array(), true),
			'keywords'		=> $this->config->item('map_keywords'),
			'map_header'	=> $map_header,
			'switches'		=> 'switches = {}',
			'selector'		=> '<div class="altSelector">'.$map_header.'</div>',
			'map_center'	=> $this->config->item('map_center'),
			'title'			=> $this->config->item('site_title_start')." Интерактивная карта"
		);
		$this->load->view('frontend/frontend_map2', $act);
	}
}

/* End of file map.php */
/* Location: ./system/application/controllers/map.php */