<?php
class Page extends CI_Controller {
	function __construct(){
		parent::__construct();
		if(!$this->session->userdata('common_user')){
			$this->session->set_userdata('common_user', md5(rand(0, 9999).'zy'.$this->input->ip_address()));
		}
		if(!$this->session->userdata('lang')){
			$this->session->set_userdata('lang', 'ru');
		}
		$this->load->model('usefulmodel');
		///$this->output->enable_profiler(TRUE);
	}

	function index(){
		$brands = $this->config->item("brand");
		$title  = $this->config->item('site_title');
		$act = array(
			'userid'     => $this->session->userdata('common_user'),
			'brand'      => $brands[$this->session->userdata("lang")],
			'comment'    => '',
			'keywords'   => $this->config->item('maps_keywords'),
			'title'      => $title[$this->session->userdata("lang")],
			'menu'       => $this->load->view('cache/menus/menu_'.$this->session->userdata('lang'), array(), true).$this->usefulmodel->admin_menu(),
			'header'     => '', //$this->load->view($this->session->userdata('lang').'/frontend/page_header', array(), true),
			'footer'     => $this->load->view('shared/page_footer', array(), true),
			'links_heap' => '', //$this->load->view('cache/links/links_heap', array(), true),
			'content'    => $this->load->view($this->session->userdata('lang')."/frontend/main_page_content", array(), true)
		);
		$this->load->view('shared/nomap', $act);
	}
	/*
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
		$brands = $this->config->item("brand");
		$act = array(
			'map_header' => $row->name,
			'brand'      => $brands[$this->session->userdata("lang")],
			'content'    => "",
			'mapset'     => $mapset,
			'map_center' => $this->config->item('map_center'),
			'keywords'   => $this->config->item('maps_keywords'),
			'title'      => $this->config->item('site_title_start')." Интерактивная карта",
			'menu'       => $this->load->view('cache/menus/menu_'.$this->session->userdata('lang'), array(), true).$this->usefulmodel->admin_menu(),
			'selector'   => $this->load->view('cache/menus/selector_'.$mapset."_".$this->session->userdata('lang'),	array(), true),
			'footer'     => $this->load->view('shared/page_footer', array(), true),
			'links_heap' => $this->load->view('cache/links/links_heap',			array(), true)
		);
		$this->load->view('shared/map', $act);
	}
	*/

	function gis($location_id = 0){
		//$this->output->enable_profiler(TRUE);
		$this->load->model('frontendmodel');
		$props = $this->frontendmodel->get_properties($location_id);
		$brands = $this->config->item("brand");
		$act = array(
			'comment'  => ($props['comments']) ? $this->frontendmodel->comments_show($location_id) : "",
			'title'    => $this->config->item('site_title_start')." ГИС",
			'brand'    => $brands[$this->session->userdata("lang")],
			'keywords' => $this->config->item('maps_keywords').','.$props['name'],
			'content'  => $this->frontendmodel->get_cached_content($location_id),
			'header'   => '',//$this->load->view($this->session->userdata('lang').'/frontend/page_header',	array(), TRUE),
			'menu'     => $this->load->view('cache/menus/menu_'.$this->session->userdata('lang'), array(), TRUE).$this->usefulmodel->admin_menu(),
			'footer'   => $this->load->view('shared/page_footer', array(), true),
		);
		$this->load->view('shared/nomap', $act);
	}

	public function addcomment(){
		$this->load->model('docmodel');
		$this->docmodel->addcomment();
	}

	public function testcaptcha(){
		if ( (string) $this->session->userdata("cpt") === (string) md5(strtolower($this->input->post("captcha")))) {
			print "OK";
		} else {
			print "Fail";
		}
	}

	function docs($docid = 1){
		$this->load->model('frontendmodel');
		$brands = $this->config->item("brand");
		$act = array(
			'footer'		=> $this->load->view('shared/page_footer', array(), true),
			'mapset'		=> 0,
			'brand'			=> $brands[$this->session->userdata("lang")],
			'menu'			=> $this->load->view('cache/menus/menu_'.$this->session->userdata('lang'), array(), true),
			'keywords'		=> $this->config->item('map_keywords'),
			'map_header'	=> "Объекты по типам",
			'map_center'	=> $this->config->item('map_center'),
			'title'			=> $this->config->item('site_title_start')." Интерактивная карта",
			'content'		=> $this->frontendmodel->show_doc($docid)
		);
		$this->load->view('shared/nomap', $act);
	}

	public function comment_control(){
		$result = $this->db->query("SELECT
		`locations`.owner
		FROM
		`locations`
		INNER JOIN `comments` ON (`locations`.id = `comments`.location_id)
		WHERE `comments`.`hash` = ?
		LIMIT 1", array($this->input->post('hash')));
		
		if ($result->num_rows()) {
			$row = $result->row(0);
		}
		if(
			   ((string) $row->owner === (string) $this->session->userdata("user_id")) 
			|| ($this->session->userdata('user_id') && $this->config->item('admin_can_edit_user_locations'))
		) {
			$result = $this->db->query("UPDATE
			`comments`
			SET
			`status` = IF(comments.status = 'N', 'A', 'N')
			Where
			comments.hash = ?", array($this->input->post('hash')));
			if($this->db->affected_rows()){
				$result = $this->db->query("SELECT 
				`comments`.`status`
				FROM
				`comments`
				WHERE `comments`.`hash` = ?", array($this->input->post('hash')));
				if ($result->num_rows()) {
					$row = $result->row(0);
					print $row->status;
				}
			}
			return true;
		}
		print "alert('An owner has been forged!')";
		return false;
	}

	public function comment_delete(){
		$result = $this->db->query("SELECT
		`locations`.owner
		FROM
		`locations`
		INNER JOIN `comments` ON (`locations`.id = `comments`.location_id)
		WHERE `comments`.`hash` = ?
		LIMIT 1", array($this->input->post('hash')));
		
		if ($result->num_rows()) {
			$row = $result->row(0);
		}
		if(
			   ((string) $row->owner === (string) $this->session->userdata("user_id")) 
			|| ($this->session->userdata('user_id') && $this->config->item('admin_can_edit_user_locations'))
		) {
			$result = $this->db->query("UPDATE
			`comments`
			SET
			`status` = 'D'
			WHERE
			comments.hash = ?", array($this->input->post('hash')));
			if($this->db->affected_rows()){
				print "D";
			}
			return true;
		}
		print "alert('An owner has been forged!')";
		return false;
	}

}

/* End of file page.php */
/* Location: ./system/application/controllers/page.php */