<?php
class Login extends CI_Controller{
	function __construct(){
		parent::__construct();
		$this->load->model('loginmodel');
		$this->load->model('usefulmodel');
		$this->load->library('email');
		if(!$this->session->userdata('common_user')){
			$this->session->set_userdata('common_user', md5(rand(0, 9999).'zy'.$this->input->ip_address()));
		}
		if(!$this->session->userdata('lang')){
			$this->session->set_userdata('lang', 'en');
		}
	}

	function index($mode = 'auth'){
		$this->login($mode);
	}

	public function login($mode){
		if($this->input->post('name') && $this->input->post('pass')){
			$this->loginmodel->test_user();
		}else{
			$this->loginmodel->index($mode);
		}
	}

	function register(){
		if (!$this->config->item('reg_active')) {
			print "Регистрация новых пользователей не производится.<br>User registration was disabled";
			return false;
		}
		if($this->loginmodel->new_user_data_test()){
			//send mail
			$valcode="c1d5a14".md5(date("DMYU"));
			$this->loginmodel->user_add($valcode);
			$act = array(
				'valcode'  => $this->config->item('base_url').'/login/activate/'.$valcode,
				'errors'   => "",
				'username' => $this->input->post('name', true),
				'pass'     => $this->input->post('pass', true)
			);
			//отсылка почты
			mail($this->input->post('email'),
				"Активация учётной записи на ".$this->config->item('site_friendly_url'),
				$this->load->view('login/mail_activation' , $act, true),
			"From: ".$this->config->item('site_reg_email')."\r\n"
			."Reply-To: ".$this->config->item('site_reg_email')."\r\n"
			."X-Mailer: PHP/" . phpversion());
			// итог работы
			$this->load->view('login/login_regresult', $act);
		}
	}

	function activate($code){
		$act = array();
		$result = $this->db->query("SELECT 
		`users_admins`.nick,
		CONCAT(`users_admins`.name_i,`users_admins`.name_o) as io,
		`users_admins`.uid,
		`users_admins`.valid,
		`users_admins`.class_id,
		`users_admins`.id
		FROM
		`users_admins`
		WHERE users_admins.validcode = ? 
		LIMIT 1", array($code));
		if($result->num_rows()){
			$row = $result->row();
			if (!$row->valid) {
				$result = $this->db->query("UPDATE
				users_admins
				SET
				users_admins.valid = 1
				WHERE 
				users_admins.validcode = ?", array($code));
				if (!$this->db->affected_rows()) {
					array_push($act, "<li>Указанный код валидации не существует</li>");
				}
			}else{
				array_push($act, "<li>Код активации уже был однажды использован. Если вы забыли пароль, воспользуйтесь сервисом восстановления пароля</li>");
			}
		}else{
			array_push($act, "<li>Не найден код активации. Такой учётной записи пользователя не существует</li>");
		}
		if(sizeof($act)){
			$act['errors'] = implode($act, "\n");
			$act['return'] = '<u><a href="/login/index/auth" style="color:blue;">На страницу авторизации</a></u>';
			$this->load->view('login/login_errors', $act);
		}else{
			$session = array(
				'user_id'	 => $row->uid,
				'io'		 => $row->io,
				'user_name'  => $row->nick,
				'user_class' => md5("secret_userclass".$row->class_id)
			);
			$this->session->set_userdata($session);
			$this->load->helper("url");
			redirect("user/profile");
		}
	}

	function rpass($mode = "form"){
		if (!$this->config->item('reg_active')) {
			print "Регистрация новых пользователей не производится.<br>User registration was disabled";
			return false;
		}
		$act = array(
			'captcha'   => '',
			'errorlist' => '',
			'page'      => 3,
			'menu'      => $this->load->view('cache/menus/menu', array(), true)
		);
		if($mode == "form"){
			$this->usefulmodel->captcha_make();
			$this->load->view('login/login_view2', $act);
		}
		if($mode == "run"){
			$act['errorlist'] = $this->loginmodel->test_restore();
		}
	}
}

/* End of file login.php */
/* Location: ./system/application/controllers/login.php */