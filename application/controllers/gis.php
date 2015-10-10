<?php
class Gis extends CI_Controller {
	function __construct(){
		parent::__construct();
		$this->load->model('frontendmodel');
		$this->load->model('usefulmodel');
		$this->load->helper('file');
		if(!$this->session->userdata('common_user')){
			$this->session->set_userdata('common_user', md5(rand(0,9999).'zy'.$this->input->ip_address()));
		}
	}

	function index($location_id = 0){
		$this->item($location_id);
	}

	function item($location_id = 0){
		$content = read_file("application/views/cache/locations/location_".$location_id.".src");
		$props = $this->frontendmodel->get_properties($location_id);
		$act = array(
			'comment'  => $this->frontendmodel->comments_show($location_id),
			'title'    => $this->config->item('site_title_start')." ГИС",
			'keywords' => $this->config->item('maps_keywords').','.$props['name'],
			'content'  => ($content) ? $content : "Объект не кэширован",
			'header'   => $this->load->view('frontend/page_header',	array(), TRUE),
			'menu'     => $this->load->view('cache/menus/menu',		array(), TRUE).$this->usefulmodel->_rent_menu().$this->usefulmodel->_admin_menu(),
			'footer'     => $this->load->view('shared/page_footer', array(), true),
		);
		$this->load->view('frontend/frontend_nomap2', $act);
	}

	public function cache_all(){
		$this->load->model('cachemodel');
		$links = array();
		$result = $this->db->query("SELECT 
		locations.id,
		locations.location_name
		FROM
		locations");
		if($result->num_rows()){
			foreach($result->result() as $row){
				$this->cachemodel->cache_location($row->id);
				array_push($links, '<a href="http://maps.korzhevdp.com/page/gis/'.$row->id.'">'.$row->location_name.'</a>');
			}
			print "Sucessfully re-cached ".$result->num_rows()." objects!";
		}
		$this->load->helper("file");
		write_file('../base/extralinks.html', '<!doctype html><html lang="en"><head><meta charset="UTF-8"></head><body>'.implode($links,"<br>\n")).'</body></html>';
	}
}

/* End of file gis.php */
/* Location: ./system/application/controllers/gis.php */