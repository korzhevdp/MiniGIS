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

	public function index(){
		$this->simple(1);
	}

	public function simple($mapset = 1){
		$act = $this->mapmodel->map_data_get($mapset);
		$this->load->view($this->session->userdata('lang').'/frontend/frontend_map2', $act);
	}

	public function set_language(){
		//$this->output->enable_profiler(TRUE);
		$this->session->set_userdata('lang', $this->input->post('lang'));
		$this->load->helper("url");
		redirect($this->input->post('redirect'));
	}

	public function type($type){
		$this->load->config('translations_g');
		$this->load->config('translations_c');
		$map_header = "";
		$groups		= $this->config->item('groups');
		$categories = $this->config->item('categories');
		$lang		= $this->session->userdata('lang');
		$brands		= $this->config->item("brand");
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
		}
		$mapconfig = array(
			'map_center'	=> $this->config->item('map_center'),
			'switches'		=> 'switches = {}',
			'group'			=> $row->id,
			'otype'			=> $type,
			'mapset'		=> 0
		);
		$act = array(
			'footer'		=> $this->load->view('shared/page_footer', array(), true),
			'brand'			=> $brands[$this->session->userdata("lang")],
			'menu'			=> $this->load->view('cache/menus/menu_'.$this->session->userdata('lang'), array(), true).$this->usefulmodel->admin_menu(),
			'keywords'		=> $this->config->item('map_keywords'),
			'map_header'	=> $map_header,
			'selector'		=> '<div class="altSelector">'.$map_header.'</div>',
			'mapconfig'		=> $this->load->view("shared/mapconfig", $mapconfig, true),
			'title'			=> $this->config->item('site_title_start')." Интерактивная карта"
		);
		$this->load->view($this->session->userdata('lang').'/frontend/frontend_map2', $act);
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
		$map_header = $groups[$group][$lang];
		$brands     = $this->config->item("brand");
		$mapconfig  = array(
			'map_center'	=> $this->config->item('map_center'),
			'switches'		=> 'switches = {}',
			'group'			=> $group,
			'otype'			=> 0,
			'mapset'		=> 0
		);
		$act = array(
			'footer'		=> $this->load->view('shared/page_footer', array(), true),
			'brand'			=> $brands[$this->session->userdata("lang")],
			'menu'			=> $this->load->view('cache/menus/menu_'.$this->session->userdata('lang'), array(), true).$this->usefulmodel->admin_menu(),
			'keywords'		=> $this->config->item('map_keywords'),
			'map_header'	=> $map_header,
			'selector'		=> '<div class="altSelector">'.$map_header.'</div>',
			'mapconfig'		=> $this->load->view("shared/mapconfig", $mapconfig, true),
			'title'			=> $this->config->item('site_title_start')." Интерактивная карта"
		);
		$this->load->view($this->session->userdata('lang').'/frontend/frontend_map2', $act);
	}

	private function select_by_type($type){
		if(!file_exists(getcwd()."/application/views/cache/mapsets/type".$type."_".$this->session->userdata('lang').".src")){
			$this->load->model("mapsetmodel");
			$this->mapsetmodel->cache_type($type);
		}
		$this->load->helper("file");
		return read_file("application/views/cache/mapsets/type".$type."_".$this->session->userdata('lang').".src");
	}

	private function select_by_group($group){
		if(!file_exists(getcwd()."/application/views/cache/mapsets/group".$group."_".$this->session->userdata('lang').".src")){
			$this->load->model("mapsetmodel");
			$this->mapsetmodel->cache_group($group);
		}
		$this->load->helper("file");
		return read_file("application/views/cache/mapsets/group".$group."_".$this->session->userdata('lang').".src");
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