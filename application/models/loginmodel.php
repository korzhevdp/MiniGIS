<?php
class Loginmodel extends CI_Model{
	function __construct(){
		parent::__construct();
		$this->load->helper('url');
		//$this->output->enable_profiler(TRUE);
	}

	public function	index($mode	= 'auth'){
		$act = array(
			'captcha'	=> $this->usefulmodel->captcha_make(),
			'page'		=> 1,
			'errorlist'	=> "",
			'reg'		=> ($mode === 'auth') ?	0 :	1,
			'menu'		=> $this->load->view('cache/menus/menu_'.$this->session->userdata('lang'), array(),	true),
		);
		$this->load->view('login/login_view2', $act);
	}

	private	function set_session($row){
		$session = array(
			'user_id'		=>	$row->uid,
			'user_name'		=> $this->input->post('name', true),
			'io'			=> $row->io,
			'admin'			=> ($row->class_id === "1") ? 1 : 0,
			'init_loc'		=> (strlen($row->init_loc) ? $row->init_loc :	0),
			'lang'			=> $row->lang,
			'access'		=> (($row->access) ? $row->access :	"1"),
			'map_center'	=> (strlen($row->map_center) > 3 ? $row->map_center	: $this->config->item('map_center')),
			'map_zoom'		=> (strlen($row->map_zoom)	   ? $row->map_zoom	  :	$this->config->item('map_zoom')),
			'map_type'		=> (strlen($row->map_type)	   ? $row->map_type	  :	$this->config->item('map_type'))
		);
		$this->session->set_userdata($session);
		redirect('user');
	}

	private	function check_user_state($row){
		$errors	= array();
		if (!$row->valid) {//была ли проведена валидация
			array_push($errors,	'Пользователь с	указанными именем и	паролем	ещё	не был проверен. Чтобы начать работу, проверьте	свой ящик электронной почты	и перейдите	по присланной ссылке для завершения	проверки.');
		}
		if (!$row->active) {//а	может быть пользователя	мы отключили?
			array_push($errors,	"Пользователь с	указанными именем и	паролем	неактивен. Напишите	письмо в администрацию сайта по	адресу:	<u><a href=\"mailto:korzhevdp@gmail.com\">korzhevdp@gmail.com</a></u> если Вы желаете активировать его.");
		}
		return $errors;
	}

	private	function return_to_login_page($errors, $reg, $page)	{
		$act = array(
			'captcha'	=> $this->usefulmodel->captcha_make(),
			'reg'		=> $reg,
			'page'		=> $page,
			'menu'		=> $this->load->view('cache/menus/menu_'.$this->session->userdata('lang'), array(),	true),
			'errorlist'	=> implode($errors,	"</li>\n<li>")
		);
		$this->load->view('login/login_view2', $act);
	}

	function test_user(){
		$errors	= array();
		$result	= $this->db->query("SELECT 
		users_admins.passw,
		users_admins.uid,
		CONCAT_WS('	', users_admins.name_i,	users_admins.name_o) AS	io,
		users_admins.class_id,
		users_admins.valid,
		users_admins.active,
		IF(LENGTH(users_admins.lang), users_admins.lang, 'en') AS lang,
		users_admins.access,
		users_admins.map_center,
		users_admins.map_zoom,
		users_admins.map_type,
		MIN(locations.id) AS init_loc
		FROM locations 
		RIGHT OUTER	JOIN users_admins ON (locations.owner =	users_admins.uid) 
		WHERE (users_admins.nick = ?) 
		LIMIT 1", array($this->input->post('name', true)));
		if($result->num_rows())	{
			$row = $result->row();
			if(md5(md5('secret').$this->input->post('pass')) ==	$row->passw) { // если пароль верен
				$errors	= $this->check_user_state($row);
				if (!sizeof($errors)) {
					$this->set_session($row);
				}
			} else {
				array_push($errors,	'Пользователь с	указанными именем и	паролем	не найден. Проверьте правильность ввода	имени пользователя и пароля. Обратите внимание,	что	прописные и	строчные буквы различаются');
			}
		} else {
			array_push($errors,'Пользователь с указанными именем и паролем не найден. Проверьте	правильность ввода имени пользователя и	пароля.	Обратите внимание, что прописные и строчные	буквы различаются');
		}
		$this->return_to_login_page($errors, 0,	1);
	}
	/*
	function captcha_make(){
		$imgname		  =	"captcha/src.gif";
		$im				  =	@ImageCreateFromGIF($imgname);
		//$im =	@ImageCreate (100, 50) or die ("Cannot Initialize new GD image stream");
		$filename		  ="captcha/src/capt.gif";
		$background_color =	ImageColorAllocate($im,	255, 255, 255);
		$text_color		  =	ImageColorAllocate($im,	0,0,0);
		$string			  =	"";
		$symbols		  =	array("A","B","C","D","E","F","G","H","J","K","L","M","N","P","Q","R","S","T","U","V","W","X","Y","Z","2","3","4","5","6","7","8","9");
		for( $i	= 0; $i	< 5; $i++ ){
			$string		 .=	$symbols[rand(0, (sizeof($symbols)-1))];
		}
		ImageTTFText($im, 24, 8, 5,	50,	$text_color, "captcha/20527.ttf", $string);
		$this->session->set_userdata('cpt',	md5(strtolower($string)));
		ImageGIF($im, $filename);
		return $filename;
		//return "zz";
	}
	*/

	private	function check_unique($errors){
		$query	 = $this->db->query("SELECT	users_admins.id	FROM users_admins WHERE	LOWER(users_admins.nick) = ?", array($this->input->post('name',true)));
		if ($query->num_rows())	{
			array_push($errors,	"Пользователь с	таким именем уже существует. Выберите другое имя");
		}
		$query = $this->db->query("SELECT users_admins.id FROM users_admins	WHERE LOWER(users_admins.email)	= ?", array($this->input->post('email',	true)));
		if ($query->num_rows())	{
			array_push($errors,	"На	этот адрес электронной почты уже регистрировалось другое имя пользователя. В целях обеспечения безопасности	данных выберите	другой почтовый	адрес");
		}
		return $errors;
	}
	private	function user_and_password_check($errors){
		if (strlen($this->input->post('name', true)) < 6) {
			array_push($errors,	"Для обеспечения безопасности данных имя пользователя должно быть длиной не	менее 6	символов");
		}
		if ($this->input->post('pass', true) !== $this->input->post('pass2', true))	{
			array_push($errors,	"Пароли	не совпадают");
		} else {
			if (strlen($this->input->post('pass')) < 6)	{
				array_push($errors,	"Пароль	должен быть	длиной не менее	6 символов");
			}
		}
		return $errors;
	}


	public function	new_user_data_test(){
		$errors	 = array();
		$errors	= $this->check_unique($errors);
		$errors	= $this->password_check();
		if (!preg_match("/([a-z\-_\.0-9])@([a-z\-_\.0-9]+)\.(.+)/",	$this->input->post('email',	true)))	{
			array_push($errors,	"Адрес электронной почты не	похож на настоящий");
		}
		if (md5(strtolower($this->input->post('cpt', true))) !== $this->session->userdata('cpt')) {
			array_push($errors,	"Код с картинки	введён неправильно");
		}
		if (sizeof($errors)) {
			$this->return_to_login_page($errors, 1,	2);
		}
		return true;
	}

	function user_add($valcode){
		$rights_level =	2;
		$active	= 0;
		$result	= $this->db->query("SELECT 
		COUNT(*) as	users
		FROM
		`users_admins`");
		if($result->num_rows()){
			$row = $result->row();
			$rights_level =	($row->users ==	0) ? 1:	2;
		}
		if(	$this->config->item('users_active_at_once')	) {
			$active	= 1;
		}
		$query = $this->db->query("INSERT INTO 
		`users_admins`
		(`users_admins`.class_id,
		`users_admins`.nick,
		`users_admins`.passw,
		`users_admins`.registration_date,
		`users_admins`.uid,
		`users_admins`.active,
		`users_admins`.valid,
		`users_admins`.validcode,
		`users_admins`.email
		)
		VALUES(	?, ?, ?, ?,	?, ?, ?, ?,	? )", array(
			$admin,
			trim($this->input->post('name',	true)),
			md5(md5('secret').$this->input->post('pass')),
			date("Y-m-d	H:i:s"),
			sha1(sha1('uid'.date("DMYHIS").rand(1, 9))),
			$active,
			0,
			$valcode,
			$this->input->post('email'))
		);
	}

	private	function try_send_mail() {
		$errors	= array();
		if ($this->send_mail( $this->input->post('email'), $this->load->view('login/mail_activation', $act , true ))) {
			array_push($errors,	"На	указанный адрес	было выслано письмо	с одноразовым кодом	активации. Воспользуйтесь ссылкой, чтобы установить	новый пароль и продолжить работу с сайтом");
		} else {
			array_push($errors,	"Произошла ошибка при отправке почты, попробуйте позже");
		}
		return $errors;
	}

	function test_restore(){
		$errors	= array();
		$result	= $this->db->query("SELECT
		`users_admins`.valid,
		`users_admins`.active
		FROM
		`users_admins`
		WHERE
		`users_admins`.`email` = ?", array($this->input->post('email', true)));
		if($result->num_rows())	{
			$row = $result->row(0);
			$errors	= $this->check_user_state($row);
		} else {
			array_push($errors,	"Адрес электронной почты не	найден,	проверьте правильность написания адреса");
		}
		if(md5(strtolower($this->input->post('cpt'))) !== $this->session->userdata('cpt')){
			array_push($errors,	"Символы с картинки	введены	неверно");
		}
		if (!sizeof($errors)) {
			$errors	= $this->try_send_mail();
		}
		$this->return_to_login_page($errors, 0,	3);

	}

	public function	send_mail($address,	$text){
		$valcode="c1d5a14".md5(date("DMYU"));
		$act = array(
			'valcode' => $this->config->item('base_url').'/login/activate/'.$valcode
		);
		if($this->db->query("UPDATE	
			`users_admins` 
			SET	
			`users_admins`.valid = 0,
			`users_admins`.validcode = ?
			WHERE 
			`users_admins`.email = ?", array( $valcode,	$this->input->post('email')))) {

			mail($this->input->post('email'),
				"Активация учётной записи на ".$this->config->item('site_friendly_url'),
				$this->load->view('login/mail_activation', $act	, true),
				"From: ".$this->config->item('site_reg_email')."\r\n"."Reply-To: ".$this->config->item('site_reg_email')."\r\n"."X-Mailer: PHP/" . phpversion()
			);
		} else {
			return false;
		};
		return true;
	}
}
#
/* End of file loginmodel.php */
/* Location: ./system/application/models/loginmodel.php	*/