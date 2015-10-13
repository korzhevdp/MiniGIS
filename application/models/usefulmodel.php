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

		$result = $this->db->query("SELECT
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
		return "";
	}

	public function admin_menu(){
		$menu = $this->load->view("menu/userclass0", array(), true);
		if ($this->session->userdata('user_name') !== false) {
			$menu = $this->load->view("menu/userclass1", array('user' => $this->session->userdata('user_name')), true);
		}
		return $menu;
	}

	public function captcha_make(){
		$imgname          = "captcha/src.gif";
		$im               = ImageCreateFromGIF($imgname);
		//$im = @ImageCreate (100, 50) or die ("Cannot Initialize new GD image stream");
		$filename         ="captcha/src/capt.gif";
		$background_color = ImageColorAllocate($im, 255, 255, 255);
		$text_color       = ImageColorAllocate($im, 0,0,0);
		$string           = "";
		$symbols          = array("A","B","C","D","E","F","G","H","J","K","L","M","N","P","Q","R","S","T","U","V","W","X","Y","Z","2","3","4","5","6","7","8","9");
		for( $i = 0; $i < 5; $i++ ){
			$string      .= $symbols[rand(0, (sizeof($symbols)-1))];
		}
		ImageTTFText($im, 24, 8, 5, 50, $text_color, "captcha/20527.ttf", $string);
		$this->session->set_userdata('cpt', md5(strtolower($string)));
		ImageGIF($im, $filename);
		return $filename;
		//return "zz";
		/*
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
		*/
	}

	public function semantics_supermenu(){
		$result=$this->db->query("SELECT
		`objects_groups`.id,
		`objects_groups`.name
		FROM
		`objects_groups`
		WHERE `objects_groups`.active
		ORDER BY `objects_groups`.name");
		$object_groups = array();
		$gis_library   = array();
		if($result->num_rows()){
			foreach($result->result() as $row){
				$class1 = ($this->uri->uri_string() == '/admin/semantics/'.$row->id) ? 'class="active"': "";
				$class2 = ($this->uri->uri_string() == '/admin/library/'.$row->id)   ? 'class="active"': "";
				array_push($object_groups, '<li '.$class1.'><a href="/admin/semantics/'.$row->id.'"><i class="icon-folder-open"></i>&nbsp;'.$row->name.'</a></li>');
				array_push($gis_library,'<li '.$class2.'><a href="/admin/library/'.$row->id.'" title="Каталог объектов: '.$row->name.'"><i class="icon-folder-open"></i>&nbsp;'.$row->name.'</a></li>');
			}
		}
		$out = array('semantics' => implode($object_groups, "\n"), 'gis_library' => implode($gis_library, "\n"));
		return $out;
	}

	public function insert_audit($text){
		$result = $this->db->query("INSERT INTO 
		`audit`(
			`audit`.`user`,
			`audit`.`text`,
			`audit`.`object`
		) VALUES( ?, ?, ? )", array(
			$this->session->userdata('user_name'), 
			$text, 
			($this->session->userdata("c_l")) ? $this->session->userdata("c_l") : 0
		));
	}
}
#
/* End of file usefulmodel.php */
/* Location: ./system/application/models/usefulmodel.php */