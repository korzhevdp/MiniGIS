<?php
class Loginmodel extends CI_Model{
	function __construct(){
		parent::__construct();
		$this->load->helper('url');
		//$this->output->enable_profiler(TRUE);
	}

	function index($mode='auth'){
		$act = Array();
		if($mode=='auth'){
			$act['reg']=0;
			$act['captcha'] = $this->_captcha_make();
		}else{
			$act['reg']=1;
			$act['captcha'] = $this->_captcha_make();
		}
		$act['errorlist']="";
		$act['menu']=$this->load->view('cache/menus/menu',array(),TRUE);
		$this->load->view('login/login_view2',$act);
	}

	function _test_user(){
		$ack    = array();
		$errors = array();
		$result = $this->db->query("SELECT 
		users_admins.passw,
		users_admins.uid,
		CONCAT_WS(' ', users_admins.name_i, users_admins.name_o) AS io,
		users_admins.class_id,
		users_admins.valid,
		users_admins.active,
		users_admins.map_center,
		users_admins.map_zoom,
		users_admins.map_type,
		MIN(`locations`.id) AS `init_loc`
		FROM
		`locations`
		INNER JOIN users_admins ON (`locations`.owner = users_admins.uid)
		WHERE
		(users_admins.nick = ?)
		LIMIT 1", array($this->input->post('name', true)));

		if($result->num_rows()){
			$row = $result->row();
			if(md5(md5('secret').$this->input->post('pass')) == $row->passw){ // если пароль верен
				if(!$row->valid){//была ли проведена валидация
					array_push($errors, '<div class="alert alert-error span5" style="clear:both;margin:40px;"><a class="close" data-dismiss="alert" href="#">x</a><h4 class="alert-heading">Ошибка!</h4>Пользователь с указанными именем и паролем ещё не был проверен. Чтобы начать работу, проверьте свой ящик электронной почты и перейдите по присланной ссылке для завершения проверки.</div>');
				}
				if(!$row->active){//а может быть пользователя мы отключили?
					array_push($errors, "Пользователь с указанными именем и паролем неактивен. Напишите письмо в администрацию сайта по адресу: <u><a href=\"mailto:korzhevdp@gmail.com\">korzhevdp@gmail.com</a></u> если Вы желаете активировать его.");
				}
				if(!sizeof($errors)){
					$session = array(
						'user_id'		=> $row->uid,
						'user_name'		=> $this->input->post('name'),
						'io'			=> $row->io,
						'user_class'	=> md5("secret_userclass".$row->class_id),
						'init_loc'		=> $row->init_loc,
						'map_center'	=> (strlen($row->map_center) > 3 ? $row->map_center : $this->config->item('map_center')),
						'map_zoom'		=> (strlen($row->map_zoom) ? $row->map_zoom : $this->config->item('map_zoom')),
						'map_type'		=> (strlen($row->map_type) ? $row->map_type : $this->config->item('map_type'))
					);
					$this->session->set_userdata($session);
					redirect('admin');
				}
			}else{
				array_push($errors,'<div class="alert alert-error span5" style="clear:both;margin:40px;"><a class="close" data-dismiss="alert" href="#">x</a>
				<h4 class="alert-heading">Ошибка!</h4>
				Пользователь с указанными именем и паролем не найден. Проверьте правильность ввода имени пользователя и пароля. Обратите внимание, что прописные и строчные буквы различаются
				</div>');
			}
		}else{
			array_push($errors,'<div class="alert alert-error span5" style="clear:both;margin:40px;"><a class="close" data-dismiss="alert" href="#">x</a><h4 class="alert-heading">Ошибка!</h4>Пользователь с указанными именем и паролем не найден. Проверьте правильность ввода имени пользователя и пароля. Обратите внимание, что прописные и строчные буквы различаются</div>');
		}
		$act['captcha']   = $this->_captcha_make();
		$act['menu']      = $this->load->view('cache/menus/menu', array(), TRUE);
		$act['errorlist'] = implode($errors, "<br>\n");
		$this->load->view('login/login_view2', $act);
	}

	function _captcha_make(){
		$imgname          = "captcha/src.gif";
		$im               = @ImageCreateFromGIF($imgname);
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
	}

	function _new_user_data_test(){
		$errors  = array();
		$query   = $this->db->query("SELECT COUNT(*) as qty FROM users_admins WHERE LOWER(users_admins.nick) = ?", array($this->input->post('name')));
		if($query->num_rows()){
			$row = $query->row();
			if($row->qty){
				array_push($errors,'<div class="notice error"><span class="icon gray small" data-icon="X"></span>'.(sizeof($errors)+1).". Пользователь с таким именем уже существует. Выберите другое имя</div>");
			}
		}
		$query = $this->db->query("SELECT COUNT(*) as qty FROM users_admins WHERE LOWER(users_admins.email) = ?", array($this->input->post('email')));
		if($query->num_rows()){
			$row = $query->row();
			if($row->qty){
				array_push($errors,'<div class="notice error"><span class="icon gray small" data-icon="X"></span>'.(sizeof($errors)+1).". На этот адрес электронной почты уже регистрировалось другое имя пользователя. В целях обеспечения безопасности данных выберите другой почтовый адрес</div>");
			}
		}
		if(strlen($this->input->post('name')) < 6){
			array_push($errors, '<div class="notice error"><span class="icon gray small" data-icon="X"></span>'.(sizeof($errors)+1).". Для обеспечения безопасности данных имя пользователя должно быть длиной не менее 6 символов</div>");
		}
		if($this->input->post('pass') !== $this->input->post('pass2')){
			array_push($errors, '<div class="notice error"><span class="icon gray small" data-icon="X"></span>'.(sizeof($errors)+1).". Пароли не совпадают</div>");
		}else{
			if(strlen($this->input->post('pass'))<6){
				array_push($errors, '<div class="notice error"><span class="icon gray small" data-icon="X"></span>'.(sizeof($errors)+1).". Пароль должен быть длиной не менее 6 символов</div>");
			}
		}
		if(!preg_match("/([a-z\-_\.0-9])@([a-z\-_\.0-9]+)\.(.+)/", $this->input->post('email'))){
			array_push($errors, '<div class="notice error"><span class="icon gray small" data-icon="X"></span>'.(sizeof($errors)+1).". Адрес электронной почты не похож на настоящий</div>");
		}
		if(md5(strtolower($this->input->post('cpt')))!==$this->session->userdata('cpt')){
			array_push($errors, '<div class="notice error"><span class="icon gray small" data-icon="X"></span>'.(sizeof($errors)+1).". Код с картинки введён неправильно</div>");
		}
		if(!sizeof($errors)){
			return true;
		}else{
			$act = array(
				'captcha'   => $this->_captcha_make(),
				'reg'       => 1,
				'errorlist' => '<div class="errorlist_header">Допущены следующие ошибки: </div>'.implode($errors, "")
			);
			$this->load->view('login/login_view2', $act);
		}
	}

	function _user_add($valcode){
		$query=$this->db->query("INSERT INTO 
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
		VALUES( ?, ?, ?, ?, ?, ?, ?, ?, ? )", array(
			2,
			trim($this->input->post('name', true)),
			md5(md5('secret').$this->input->post('pass')),
			date("Y-m-d H:i:s"),
			sha1(sha1('uid'.date("DMYHIS").rand(1, 9))),
			0,
			0,
			$valcode,
			$this->input->post('email'))
		);
	}

	function _email_send($valcode){
			$this->email->from('korzhevdp@gmail.com', 'Администрация '.$this->config->item('site_friendly_url'));
			$this->email->to('korzhevdp@gmail.com');
			$this->email->subject('Активация учётной записи на '.$this->config->item('site_friendly_url'));
			$act['valcode'] = base_url().'/login/activate/'.$valcode;
			$this->email->message($this->load->view('login/mail_activation',$act));
			$this->email->send();
			echo $this->email->print_debugger();
	}

	function _test_restore(){
		$errors = array();
		if(strlen($this->input->post('email') < 6 )){
			$result=$this->db->query("SELECT
			`users_admins`.valid
			FROM
			`users_admins`
			WHERE
			`users_admins`.`email` = ?", array($this->input->post('email', true)));
			if($result->num_rows()){
				$row = $result->row(0);
				if(!$row->valid){
					array_push($errors,'<div class="notice error"><span class="icon gray small" data-icon="X"></span>'.(sizeof($errors)+1).". Учётная запись ещё не была проверена, проверьте почтовый ящик на предмет письма о регистрации и воспользуйтесь находящейся там ссылкой для завершения регистрации</div>");
				}
			} else {
				array_push($errors,'<div class="notice error"><span class="icon gray small" data-icon="X"></span>'.(sizeof($errors)+1).". Адрес электронной почты не найден, проверьте правильность написания адреса</div>");
			}
			if(md5(strtolower($this->input->post('cpt')))!==$this->session->userdata('cpt')){
				array_push($errors,'<div class="notice error"><span class="icon gray small" data-icon="X"></span>'.(sizeof($errors)+1).". Символы с картинки введены неверно</div>");
			}

			if(sizeof($errors)){
				$act['errorlist'] = implode($errors,"<br>\n");
				$act['return']    = '<u><a style="color:blue;" href="/login">Обратно на страницу восстановления пароля</a></u>';
			}else{
				$valcode="c1d5a14".md5(date("DMYU"));
				if($this->db->query("UPDATE 
					`users_admins` 
					SET 
					`users_admins`.valid = 0,
					`users_admins`.validcode = ?
					WHERE 
					`users_admins`.email = ?",
				array(
					$valcode,
					$this->input->post('email')
				))) {
					$act['valcode'] = $this->config->item('base_url').'login/activate/'.$valcode;
					if($this->send_mail( $this->input->post('email'), $this->load->view('login/mail_activation', $act , true))) {
						$act['errorlist'] = "На указанный адрес было выслано письмо с одноразовым кодом активации. Воспользуйтесь ссылкой, чтобы установить новый пароль и продолжить работу с сайтом";
						$act['return']    = '<u><a style="color:blue;" href="/login">Обратно на страницу восстановления пароля</a></u>';
					}
				}else{
					array_push($errors, (sizeof($errors)+1).". Произошла критическая ошибка, попробуйте позже");
					$act['errorlist']     = implode($errors,"<br>\n");
					$act['return']        = '<u><a style="color:blue;" href="/login">Обратно на страницу авторизации</a></u>';
				}
			}
			$act['captcha']               = $this->_captcha_make();
			$this->load->view('login/login_view2', $act);
		}
	}

	public function send_mail($address, $text){
		mail($this->input->post('email'),
			"Активация учётной записи на ".$this->config->item('site_friendly_url'),
			$this->load->view('login/mail_activation', $act , true),
			"From: ".$this->config->item('site_reg_email')."\r\n"."Reply-To: ".$this->config->item('site_reg_email')."\r\n"."X-Mailer: PHP/" . phpversion()
		);
		return true;
	}
}
#
/* End of file loginmodel.php */
/* Location: ./system/application/models/loginmodel.php */