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
		return array(
			'otype'      => 0,
			'map_center' => $this->config->item('map_center'),
			'keywords'   => $this->config->item('maps_keywords'),
			'map_header' => $row->name,
			'content'    => "",
			'footer'     => "",
			'mapset'     => $mapset,
			'menu'       => $this->load->view('cache/menus/menu', array(), true).$this->usefulmodel->admin_menu(),
			'selector'   => $this->load->view('cache/menus/selector_'.$mapset, array(), true),
			'title'      => $this->config->item('site_title_start')." Интерактивная карта"
		);
	}
}

/* End of file mapmodel.php */
/* Location: ./system/application/controllers/mapmodel.php */