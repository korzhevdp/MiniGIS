<?php
class Login extends CI_Controller{
	function __construct(){
		parent::__construct();
		$this->load->model('loginmodel');
		$this->load->library('email');
	}

	function index($mode='auth'){
		$this->login($mode);
	}

	public function login($mode){
		if($this->input->post('name') && $this->input->post('pass')){
			$this->loginmodel->_test_user();
		}else{
			$this->loginmodel->index($mode);
		}
	}

	function register(){
		print "Регистрация новых пользователей не производится.<br>User registration was disabled";
		return false;
		if($this->loginmodel->_new_user_data_test()){
			//send mail
			$valcode="c1d5a14".md5(date("DMYU"));
			$act['valcode']  = $this->config->item('base_url').'/login/activate/'.$valcode;
			//$this->loginmodel->_email_send($valcode); #### это тестовая отсылка почты. Боевой вариант прямо здесь.
			mail($this->input->post('email'), "Активация учётной записи на ".$this->config->item('site_friendly_url'), $this->load->view('login/mail_activation', $act, true),
			"From: ".$this->config->item('site_reg_email')."\r\n"
			."Reply-To: ".$this->config->item('site_reg_email')."\r\n"
			."X-Mailer: PHP/" . phpversion());
			//add_user
			$this->loginmodel->_user_add($valcode);
			$act['errors']   = "";
			$act['username'] = $this->input->post('name', true);
			$act['pass']     = $this->input->post('pass',true);
			$this->load->view('login/login_regresult', $act);
		}
	}

	function activate($code){
		$act = array();
		$result=$this->db->query("SELECT 
		`users_admins`.nick,
		CONCAT(`users_admins`.name_i,`users_admins`.name_o) as io,
		`users_admins`.uid,
		`users_admins`.valid,
		`users_admins`.class_id,
		`users_admins`.id
		FROM
		`users_admins`
		WHERE users_admins.validcode = ? LIMIT 1",Array($code));
		if($result->num_rows()){
			$row = $result->row();
			if(!$row->valid){
				if (!$this->db->query("UPDATE users_admins SET users_admins.valid = 1 WHERE users_admins.validcode = ?",Array($code))){
					array_push($act,"<li>В процессе активации произошла критическая ошибка</li>");
				}
			}else{
				array_push($act,"<li>Код активации уже был однажды использован. Если вы забыли пароль, воспользуйтесь сервисом восстановления пароля</li>");
			}
		}else{
			array_push($act,"<li>Не найден код активации. Такой учётной записи пользователя не существует</li>");
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
			$this->load->view('login/login_regcomplete', array());
		}
	}

	function rpass($mode="form"){
		print "Регистрация новых пользователей не производится.<br>User registration was disabled";
		return false;
		if($mode=="form"){
			$act['captcha'] = "";
			$this->loginmodel->_captcha_make();
			$act['errorlist']="";
			$this->load->view('login/login_view2', $act);
		}
		if($mode=="run"){
			$act['errorlist']=$this->loginmodel->_test_restore();
		}
	}
}

/* End of file login.php */
/* Location: ./system/application/controllers/login.php */