<?php
class Usefulmodel extends CI_Model{
	function __construct(){
		parent::__construct();
	}

	public function check_owner($location_id){
		//на будущее: создание хэша// UPDATE `users_admins` SET `users_admins`.`uid` = sha1(concat(sha1('uid'),`users_admins`.`id`))
		// or GOLDEN hash :)
		if($this->config->item('admin_can_edit_user_locations') === true){
			if($this->session->userdata('admin')){
				return TRUE;
			}
		}
		/*
		if($this->session->userdata('user_id') == $this->config->item('golden_hash')){
			return TRUE;
		}
		*/
		$result=$this->db->query("SELECT
		`locations`.`owner`
		FROM
		`locations`
		WHERE
		locations.id = ? 
		AND locations.owner = ?", array( $location_id, $this->session->userdata('user_id') ));
		if($result->num_rows()){
			return TRUE;
		}else{
			return FALSE;
		}
	}

	public function check_admin_status(){
		if(!$this->session->userdata('admin')){
			$this->session->sess_destroy();
			redirect('admin');
		}
	}

	public function show_admin_menu(){
		$output = $this->load->view('admin/menu', array(), true);
		if($this->session->userdata('admin')){
			$output .= $this->load->view('admin/supermenu', $this->usefulmodel->semantics_supermenu(), true);
		}
		return $output;
	}

	public function rent_menu(){
		//return $this->admin_menu();
		/*
		if($this->session->userdata('user_class') == md5('secret_userclass3')){
			return $this->load->view("userclass3", array('user' => $this->session->userdata('user_name')), true);
		}else{
			return "";
		}
		*/
		return "";
	}

	public function admin_menu(){
		/*
		if($this->session->userdata('user_class') == md5('secret_userclass2')){
			return $this->load->view("menu/userclass2", array('user' => $this->session->userdata('user_name')), true);
		}
		if($this->session->userdata('user_class') == md5('secret_userclass1')){
			//print 1;
			return $this->load->view("menu/userclass1", array('user' => $this->session->userdata('user_name')), true);
		}
		*/
		$menu = $this->load->view("menu/userclass0", array(), true);
		if ($this->session->userdata('user_name') !== false) {
			$menu = $this->load->view("menu/userclass1", array('user' => $this->session->userdata('user_name')), true);
		}
		return $menu;
	}

	public function _captcha_make(){
		$imgname="captcha/src.gif";
		$im = @ImageCreateFromGIF($imgname);
		//$im = @ImageCreate (100, 50) or die ("Cannot Initialize new GD image stream");
		$filename="captcha/cp_".date("dmyHIS").rand(0,99).rand(0,99).".gif";
		$background_color = ImageColorAllocate($im, 255, 255, 255);
		$text_color = ImageColorAllocate($im, 0,0,0);
		$string="";
		$symbols=Array("A","B","C","D","E","F","G","H","J","K","L","M","N","P","Q","R","S","T","U","V","W","X","Y","Z","2","3","4","5","6","7","8","9");
		for($i=0;$i<5;$i++){
			$string.=$symbols[rand(0,(sizeof($symbols)-1))];
		}
		ImageTTFText ($im, 24, 8, 5, 50, $text_color, "captcha/20527.ttf",$string);
		$this->session->set_userdata('cpt', md5(strtolower($string)));
		ImageGIF ($im, $filename);
		return $filename;
		//return "zz";
	}

	public function semantics_supermenu(){
		$result=$this->db->query("SELECT
		`objects_groups`.id,
		`objects_groups`.name
		FROM
		`objects_groups`
		WHERE `objects_groups`.active = 1
		ORDER BY `objects_groups`.name");
		$ogps = Array();
		$gis_library = Array();
		if($result->num_rows()){
			foreach($result->result() as $row){
				$c1 = ($_SERVER["REQUEST_URI"] == '/admin/semantics/'.$row->id) ? 'class="active"': "";
				$c2 = ($_SERVER["REQUEST_URI"] == '/admin/library/'.$row->id) ? 'class="active"': "";
				array_push($ogps,'<li '.$c1.'><a href="/admin/semantics/'.$row->id.'"><i class="icon-folder-open"></i>&nbsp;'.$row->name.'</a></li>');
				array_push($gis_library,'<li '.$c2.'><a href="/admin/library/'.$row->id.'" title="Каталог объектов: '.$row->name.'"><i class="icon-folder-open"></i>&nbsp;'.$row->name.'</a></li>');
			}
		}
		$out = array('semantics' => implode($ogps, "\n"), 'gis_library' => implode($gis_library, "\n"));
		return $out;
	}

	public function insert_audit($text){
		$result = $this->db->query("INSERT INTO 
		`audit`(
			`audit`.`user`,
			`audit`.`text`,
			`audit`.`object`
		) VALUES( ?, ?, ? )", array(
			$this->session->userdata('user_id'), 
			$text, 
			($this->session->userdata("c_l")) ? $this->session->userdata("c_l") : 0
		));
	}
}
#
/* End of file usefulmodel.php */
/* Location: ./system/application/models/usefulmodel.php */