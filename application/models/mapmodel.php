<?php
class Mapmodel extends CI_Model{
	function __construct(){
		parent::__construct();
	}
	
	public function map_data_get($mapset){
		$result = $this->db->query("SELECT
		`map_content`.name
		FROM
		`map_content`
		where
		`map_content`.id = ?", array($mapset));
		if($result->num_rows()){
			$row = $result->row();
		}
		$this->load->config('translations_m');
		$maps   = $this->config->item('maps');
		$brands = $this->config->item("brand");
		$lang   = $this->session->userdata('lang');
		$mapconfig  = array(
			'map_center'	=> $this->config->item('map_center'),
			'switches'		=> $this->load->view('cache/selectors/selector_'.$mapset."_switches_".$lang, array(), true),
			'group'			=> 0,
			'otype'			=> 0,
			'mapset'		=> $mapset
		);
		return array(
			'brand'      => $brands[$lang],
			'keywords'   => $this->config->item('maps_keywords'),
			'map_header' => (strlen($maps[$mapset][$lang])) ? $maps[$mapset][$lang] : $row->name,
			'content'    => "",
			'footer'     => $this->load->view('shared/page_footer', array(), true),
			'menu'       => $this->load->view('cache/menus/menu_'.$lang, array(), true).$this->usefulmodel->admin_menu(),
			'selector'   => $this->load->view('cache/selectors/selector_'.$mapset."_".$lang, array(), true),
			'mapconfig'	=> $this->load->view("shared/mapconfig", $mapconfig, true),
			'title'      => $this->config->item('site_title_start')." Интерактивная карта"
		);
	}
}

/* End of file mapmodel.php */
/* Location: ./system/application/controllers/mapmodel.php */