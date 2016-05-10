<?php
class Map extends CI_Controller {
	function __construct(){
		parent::__construct();
		$this->load->model('mapmodel');
		$this->load->model('usefulmodel');
		if(!$this->session->userdata('lang')){
			$this->session->set_userdata('lang', 'en');
		}
	}

	private function select_by_type($type) {
		if(!file_exists(getcwd()."/application/views/cache/mapsets/type".$type."_".$this->session->userdata('lang').".src")){
			$this->load->model("mapsetmodel");
			$this->mapsetmodel->cache_type($type);
		}
		$this->load->helper("file");
		return read_file("application/views/cache/mapsets/type".$type."_".$this->session->userdata('lang').".src");
	}

	private function select_by_group($group) {
		if(!file_exists(getcwd()."/application/views/cache/mapsets/group".$group."_".$this->session->userdata('lang').".src")){
			$this->load->model("mapsetmodel");
			$this->mapsetmodel->cache_group($group);
		}
		$this->load->helper("file");
		return read_file("application/views/cache/mapsets/group".$group."_".$this->session->userdata('lang').".src");
	}

	public function index(){
		$this->simple(1);
	}

	public function simple($mapset = 1){
		$act = $this->mapmodel->map_data_get($mapset);
		$this->load->view('shared/map', $act);
	}

	public function set_language(){
		//$this->output->enable_profiler(TRUE);
		$this->session->set_userdata('lang', $this->input->post('lang'));
		$this->load->helper("url");
		redirect($this->input->post('redirect'));
	}

	private function getMapName ($type) {
		$groups		= $this->config->item('groups');
		$categories = $this->config->item('categories');
		$lang		= $this->session->userdata('lang');
		$result = $this->db->query("SELECT
			`objects_groups`.id,
			`locations_types`.id as type
			FROM
			`locations_types`
			INNER JOIN `objects_groups` ON (`locations_types`.object_group = `objects_groups`.id)
			WHERE `locations_types`.`id` = ?
			LIMIT 1", array($type));
		if($result->num_rows()) {
			$row = $result->row(0);
			$map_header = $groups[$row->id][$lang]." - ".$categories[$row->type][$lang];
			return array( 'map_header' => $map_header, 'group' => $row->id );
		}
		return array( 'map_header' => '', 'group' => 1 );
	}

	public function type($type){
		$this->load->config('translations_g');
		$this->load->config('translations_c');
		$lang		= $this->session->userdata('lang');
		$headers	= $this->config->item("balloon_headers");
		$mapdata	= $this->getMapName($type);
		$mapconfig  = array(
			'map_center'	=> $this->config->item('map_center'),
			'switches'		=> 'switches = {}',
			'group'			=> $mapdata['group'],
			'otype'			=> $type,
			'mapset'		=> 0,
			'headers'		=> $headers[$lang],
			'map_header'	=> $mapdata['map_header']
		);
		$act = $this->returnActArray($mapconfig);
		$this->load->view('shared/map', $act);
	}

	private function returnActArray($mapconfig) {
		$brands		= $this->config->item("brand");
		return $act = array(
			'footer'		=> $this->load->view('shared/page_footer', array(), true),
			'brand'			=> $brands[$this->session->userdata("lang")],
			'menu'			=> $this->load->view('cache/menus/menu_'.$this->session->userdata('lang'), array(), true).$this->usefulmodel->admin_menu(),
			'keywords'		=> $this->config->item('map_keywords'),
			'map_header'	=> $mapconfig['map_header'],
			'selector'		=> '<div class="altSelector">'.$mapconfig['map_header'].'</div>',
			'mapconfig'		=> $this->load->view("shared/mapconfig", $mapconfig, true),
			'title'			=> $this->config->item('site_title_start')." Интерактивная карта"
		);
	}

	public function get_map_content() {
		$mapset = $this->input->post('mapset');
		if(!file_exists(getcwd()."/application/views/cache/mapsets/mapset".$mapset."_".$this->session->userdata('lang').".src")){
			$this->load->model("mapsetmodel");
			$this->mapsetmodel->cache_mapset($mapset);
		}
		$this->load->helper("file");
		print read_file("application/views/cache/mapsets/mapset".$mapset."_".$this->session->userdata('lang').".src");
	}

	public function group($group){
		$map_header = "";
		$this->load->config('translations_g');
		$groups     = $this->config->item('groups');
		$lang       = $this->session->userdata('lang');
		$headers	= $this->config->item("balloon_headers");
		$map_header = $groups[$group][$lang];
		$mapconfig  = array(
			'map_center'	=> $this->config->item('map_center'),
			'switches'		=> 'switches = {}',
			'group'			=> $group,
			'otype'			=> 0,
			'mapset'		=> 0,
			'headers'		=> $headers[$lang],
			'map_header'	=> $groups[$group][$lang]
		);
		$act = $this->returnActArray($mapconfig);
		$this->load->view($this->session->userdata('lang').'/frontend/frontend_map2', $act);
	}

	public function msearch(){
		print $this->select_by_type($this->input->post('type', true));
	}

	public function gsearch(){
		print $this->select_by_group($this->input->post('group', true));
	}

	public function cachemapset(){
		$this->load->model('mapsetmodel');
		$this->mapsetmodel->cache_mapset(1);
	}

}

/* End of file map.php */
/* Location: ./system/application/controllers/map.php */