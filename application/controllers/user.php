<?php
class User extends CI_Controller{
	function __construct(){
		parent::__construct();
		//$this->output->enable_profiler(TRUE);
		$this->db->query("SET lc_time_names = 'ru_RU'");
		if(!$this->session->userdata('user_id')){
			$this->load->helper('url');
			redirect('login/index/auth');
		}else{
			$this->load->model('cachemodel');
			$this->load->model('usefulmodel');
			$this->load->model('usermodel');
			$this->load->model('adminmodel');
			$this->load->helper('form');
		}
		if(!$this->session->userdata('lang')){
			$this->session->set_userdata('lang', 'en');
		}
	}

	function index($obj_group = 0, $loc_type = 0){
		$this->library($obj_group, $loc_type);
	}

	public function paydata(){
		$this->load->model('paymodel');
		$output = array(
			'menu'    => $this->usefulmodel->show_admin_menu(),
			'content' => $this->paymodel->get_locations_pay_summary()
		);
		$this->load->view('admin/view', $output);
	}

	public function set_payment(){
		$this->usefulmodel->check_admin_status();
		$this->load->model('paymodel');
		$this->paymodel->set_payment();
	}

	function library($obj_group = 0, $loc_type = 0){
		$output = array(
			'menu'    => $this->usefulmodel->show_admin_menu(),
			'content' => $this->adminmodel->get_full_index($obj_group, $loc_type)
		);
		$this->load->view('admin/view', $output);
	}

	function help($page = 0){
		$output = array(
			'menu'    => $this->usefulmodel->show_admin_menu(),
			'content' => $this->usermodel->get_help_page($page)
		);
		$this->load->view('admin/view', $output);
	}
	
	//работа с сессиями и профилем
	function profile() {
		$output = array(
			'menu'    => $this->usefulmodel->show_admin_menu(),
			'content' => $this->usermodel->user_edit()
		);
		$this->load->view('admin/view', $output);
	}

	function user_exit(){
		$this->session->sess_destroy();
		$this->load->helper('url');
		redirect('admin');
	}

	function user_save(){
		$this->usermodel->user_save();
		$this->load->helper('url');
		redirect('user/profile');
	}

	function user_newpassword(){
		$errors = array();
		if($this->input->post('pass1') !== $this->input->post('pass2')){
			array_push($errors, "Пароль не совпадает с проверкой");
		}
		$result = $this->db->query("SELECT
		users_admins.passw
		FROM
		users_admins
		WHERE
		`users_admins`.`uid` = ?", array(
			$this->session->userdata('user_id')
		));
		if ($result->num_rows()) {
			$row = $result->row();
			if($row->passw !== md5(md5('secret').$this->input->post('oldpass'))){
				array_push($errors, "Текущий пароль указан неверно");
			}
		} else {
			array_push($errors, "Идентификатор пользователя некорректен. Завершите сессию, а затем авторизуйтесь повторно");
		}
		if (!sizeof($errors)) {
			if($this->db->query("UPDATE 
				users_admins
				SET
				users_admins.`passw` = ?
				WHERE
				`users_admins`.`uid` = ?", array(
					md5(md5('secret').$this->input->post('pass1')),
					$this->session->userdata('user_id')))
			) {
				array_push($errors, "Пароль успешно изменён");
			}
		}
		$output = array(
			'menu'    => $this->usefulmodel->show_admin_menu(),
			'content' => $this->usermodel->user_edit($this->session->userdata('user_id')).implode($errors, "<li>\n</li>")
		);
		$this->load->view('admin/view', $output);
	}



##########################################################################################
	function photomanager($location_id=0, $image_id=0){
		// 37 = длина md5 хэша + длина "_.jpg";
		if( $this->input->post('frm_img_order') 
			&& strlen($this->input->post('frm_img_order')) > 37
			&& $this->usefulmodel->_check_owner($location_id)
		){
			$this->usermodel->photoeditor_order_save();
		}

		$options = array();
		foreach($this->usermodel->photoeditor_locations($location_id) as $key=>$val){
			array_push($options, '<option value="'.$key.'">'.$val.'</option>');
		}
		//form_dropdown('location', $options, $location_id, 'id="location" class="span9" size=6 onchange="show_l_table(this.value);"')
		$photoeditor = array(
			'location_id' => $location_id,
			'image_id'    => $image_id.".jpg",
			'locations'   => implode($options, "\n"),
			'list'        => $this->usermodel->photoeditor_list($location_id)
		);
		$output = array(
			'menu'    => $this->usefulmodel->show_admin_menu(),
			'content' => $this->load->view('admin/photoeditor', $photoeditor, true)
		);
		header("Pragma: no-cache");
		$this->load->view('admin/view', $output);
	}

	function commentmanager() {
		$this->load->model('docmodel');
		$output = array(
			'menu'    => $this->usefulmodel->show_admin_menu(),
			'content' => $this->docmodel->comments_show($this->session->userdata("user_id"))
		);
		$this->load->view('admin/view', $output);
	}

}
/* End of file usermodules.php */
/* Location: ./system/application/controllers/usermodules.php */