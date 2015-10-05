<?php
class Page extends CI_Controller {
	function __construct(){
		parent::__construct();
		$this->load->model('frontendmodel');
		$this->load->model('usefulmodel');
		$this->load->model('cachemodel');
		if(!$this->session->userdata('common_user')){
			$this->session->set_userdata('common_user', md5(rand(0, 9999).'zy'.$this->input->ip_address()));
		}
		if(!$this->session->userdata('lang')){
			$this->session->set_userdata('lang', 'en');
		}
	}

	function index(){
		$act = array(
			'userid'     => $this->session->userdata('common_user'),
			'keywords'   => $this->config->item('maps_keywords'),
			'title'      => $this->config->item('site_title_start'),
			'menu'       => $this->load->view('cache/menus/menu_'.$this->session->userdata('lang'), array(), TRUE).$this->usefulmodel->rent_menu().$this->usefulmodel->admin_menu(),
			'header'     => '', //$this->load->view($this->session->userdata('lang').'/frontend/page_header',	array(), TRUE),
			'footer'     => $this->load->view($this->session->userdata('lang').'/frontend/page_footer',	array(), TRUE),
			'links_heap' => $this->load->view('cache/links/links_heap',	array(), TRUE),
			'content'    => $this->load->view($this->session->userdata('lang')."/frontend/main_page_content", array(), true)
		);
		$this->load->view($this->session->userdata('lang').'/frontend/frontend_nomap2', $act);
	}

	function map($mapset = 1){
		$result = $this->db->query("SELECT 
		`map_content`.name
		FROM
		`map_content`
		where
		`map_content`.id = ?", array($mapset));
		if($result->num_rows()){
			$row = $result->row();
		}
		$act = array(
			'map_header' => $row->name,
			'content'    => "",
			'mapset'     => $mapset,
			'map_center' => $this->config->item('map_center'),
			'keywords'   => $this->config->item('maps_keywords'),
			'title'      => $this->config->item('site_title_start')." Интерактивная карта",
			'menu'       => $this->load->view('cache/menus/menu_'.$this->session->userdata('lang'), array(), TRUE).$this->usefulmodel->rent_menu().$this->usefulmodel->admin_menu(),
			'selector'   => $this->load->view('cache/menus/selector_'.$mapset."_".$this->session->userdata('lang'),	array(), TRUE),
			'footer'     => $this->load->view($this->session->userdata('lang').'/frontend/page_footer',			array(), TRUE),
			'links_heap' => $this->load->view('cache/links/links_heap',			array(), TRUE)
		);
		$this->load->view($this->session->userdata('lang').'/frontend/frontend_map2', $act);
	}

	function gis($location_id = 0){
		//$this->output->enable_profiler(TRUE);
		$props = $this->frontendmodel->get_properties($location_id);
		$act = array(
			'comment'  => ($props['comments']) ? $this->frontendmodel->comments_show($location_id) : "",
			'title'    => $this->config->item('site_title_start')." ГИС",
			'keywords' => $this->config->item('maps_keywords').','.$props['name'],
			'content'  => $this->frontendmodel->get_cached_content($location_id),
			'header'   => '',//$this->load->view($this->session->userdata('lang').'/frontend/page_header',	array(), TRUE),
			'menu'     => $this->load->view('cache/menus/menu_'.$this->session->userdata('lang'), array(), TRUE).$this->usefulmodel->rent_menu().$this->usefulmodel->admin_menu(),
			'footer'   => $this->load->view($this->session->userdata('lang').'/frontend/page_footer',	array(), TRUE)
		);
		$this->load->view($this->session->userdata('lang').'/frontend/frontend_nomap2', $act);
	}

	public function addcomment(){
		$this->load->model('docmodel');
		$this->docmodel->addcomment();
	}

	function docs($docid = 1){
		$act = array(
			'footer'		=> "",
			'mapset'		=> 0,
			'menu'			=> $this->load->view('cache/menus/menu_'.$this->session->userdata('lang'), array(), true),
			'keywords'		=> $this->config->item('map_keywords'),
			'map_header'	=> "Объекты по типам",
			'map_center'	=> $this->config->item('map_center'),
			'title'			=> $this->config->item('site_title_start')." Интерактивная карта",
			'content'		=> $this->frontendmodel->show_doc($docid)
		);
		$this->load->view($this->session->userdata('lang').'/frontend/frontend_nomap2', $act);
	}
}

/* End of file page.php */
/* Location: ./system/application/controllers/page.php */