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
			$this->load->helper('form');
			$this->load->library('upload');
		}
	}

	function index($obj_group=1, $loc_type=0){
		$this->library($obj_group=1,$loc_type=0);
	}

	function library($obj_group=1, $loc_type=0){
		$output['menu']=$this->load->view('admin/menu','',true);
		if($this->session->userdata('user_class') == md5("secret_userclass1")){
			$supermenu=$this->usefulmodel->semantics_supermenu();
			$output['menu'].=$this->load->view('admin/supermenu',$supermenu,true);
		}
		$output['content']=$this->usermodel->get_index($obj_group,$loc_type);
		$this->load->view('admin/view',$output);
	}

	function datatrap($page, $location_id, $obj_group=1){
		if($this->usefulmodel->_check_owner($location_id)){
			switch($obj_group){
				case 1:
				switch($page){
					case 1 :
					$this->usermodel->save_location($location_id);
					break;
					case 5 :
					$this->usermodel->save_onmap($location_id);
					break;
					case 7 :
					$this->usermodel->save_params($page,$location_id);
					$this->db->query("UPDATE locations
					SET locations.location_name = '".$this->db->escape_like_str($this->input->post('subloc_selfname'))."'
					WHERE locations.id = '".$location_id."'");
					break;
					case "pp" ://не забудь, траверса здесь должна быть под условием!
						$traverse = ($this->input->post('frm_pp_traverse')) ? 1 : 0;
						$this->usermodel->pp_save($location_id,$traverse);
						$page=8;
					break;
					case 8 :
					$this->usermodel->pp_save($location_id,0);
					break;
					default :
					$this->usermodel->save_params($page,$location_id);
					$this->usermodel->_params_sync_traversal($location_id);
				}
				break;
				default:
				switch($page){
					case 1 :
					$this->usermodel->save_location($location_id);
					break;
					case 5 :
					$this->usermodel->save_onmap($location_id);
					break;
					default :
					$this->usermodel->save_params($page,$location_id);
				}
			}
			$this->cachemodel->_make_cache($location_id,0);
			$this->cachemodel->_site_tree_build();
		}
		$this->load->helper('url');
		redirect('/admin/pages/'.$location_id."/".$page.'/'.$obj_group);
		//exit;
	}

	function pages($location_id='none', $page=1, $obj_group=1, $loc_type=0 ){ //фиксируем умолчательные выборки по объектам "Жильё"
		if($location_id=='new'){
			if($page==1){$page=0;}
			$location_id=$this->usermodel->new_location($page,$obj_group,$loc_type);	//редирект внутри функции
		}elseif($location_id=='none' || !$this->usefulmodel->_check_owner($location_id)){//			print "внимание! срабатывание защиты checkOwner()<BR><BR>";
			$location_id=$this->usermodel->set_init_location();	//редирект внутри функции
		}
		//сборка страницы (если запроса на создание объекта не было)
		$output['menu']=$this->load->view('admin/menu','',true);
		if($this->session->userdata('user_class') == md5("secret_userclass1")){
			$supermenu=$this->usefulmodel->semantics_supermenu();
			$output['menu'].=$this->load->view('admin/supermenu',$supermenu,true);
		}
		$page = (abs($page) > 8 || !$page) ? 1 : $page; // не делайте больше 8 страничек-накопителей для признаков объектов! или поправьте здесь.
		$output['content']=$this->usermodel->show_modes($location_id,$page,$obj_group);
		$this->load->view('admin/view',$output);
	}

	function help($page = 0){
		$output['content']=$this->usermodel->_get_help_page($page);
		$output['menu']=$this->load->view('admin/menu','',true);
		if($this->session->userdata('user_class') == md5("secret_userclass1")){
			$supermenu=$this->usefulmodel->semantics_supermenu();
			$output['menu'].=$this->load->view('admin/supermenu',$supermenu,true);
		}
		$this->load->view('admin/view',$output);
	}

	function maps(){
		/* modes: show, save, new */
		$output['menu']=$this->load->view('admin/menu','',true);
		if($this->session->userdata('user_class') == md5("secret_userclass1")){
			$supermenu=$this->usefulmodel->semantics_supermenu();
			$output['menu'].=$this->load->view('admin/supermenu',$supermenu,true);
		}else{
			$this->load->helper('url');
			$this->session->sess_destroy();
			redirect('admin');
		}
		if($this->input->post('save')){
			$this->usermodel->mc_save();
			$this->cachemodel->_menu_build(1,$this->config->item('mod_housing_root'),$this->config->item('mod_gis'));
		}
		if($this->input->post('new')){
			$this->usermodel->mc_new();
			$this->cachemodel->_menu_build(1,$this->config->item('mod_housing_root'),$this->config->item('mod_gis'));
		}
		
		$mapset=($this->input->post('map_view')) ? $this->input->post('map_view') : 1;
		$output['content']=$this->usermodel->mc_show($mapset);
		$this->load->view('admin/view',$output);
	}

	//работа с сессиями
	function profile() {
		$output = array();
		$output['menu'] = $this->load->view('admin/menu', array(), true);
		if($this->session->userdata('user_class') == md5("secret_userclass1")){
			$supermenu = $this->usefulmodel->semantics_supermenu();
			$output['menu'].=$this->load->view('admin/supermenu', $supermenu, true);
		}
		$output['content'] = $this->usermodel->user_edit();
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
		$userid = $this->session->userdata('user_id');
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
			$row=$result->row();
			if($row->passw !== md5(md5('secret').$this->input->post('oldpass'))){
				array_push($errors, "Текущий пароль указан неверно");
			}
		} else {
			array_push($errors, "Идентификатор пользователя некорректен. Завершите сессию, а затем авторизуйтесь повторно");
		}
		if (!sizeof($errors)) {
			if($result = $this->db->query("UPDATE 
				users_admins
				SET
				users_admins.`passw` = ?
				WHERE
				`users_admins`.`uid` = ?", array(
					md5(md5('secret').$this->input->post('pass1')),
					$this->session->userdata('user_id')))
			){
				array_push($errors, "Пароль успешно изменён");
			}
		}
		$output['menu']      = $this->load->view('admin/menu', array(), true);
		if($this->session->userdata('user_class') == md5("secret_userclass1")){
			$supermenu       = $this->usefulmodel->semantics_supermenu();
			$output['menu'] .= $this->load->view('admin/supermenu', $supermenu, true);
		}
		$output['content']   = $this->usermodel->user_edit($this->session->userdata('user_id')).implode($errors, "<li>\n</li>");
		$this->load->view('admin/view',$output);
	}

##########################################################################################
	function photomanager($location_id=0,$image_id=0){
		$output['menu']=$this->load->view('admin/menu','',true);
		if($this->session->userdata('user_class') == md5("secret_userclass1")){
			$supermenu=$this->usefulmodel->semantics_supermenu();
			$output['menu'].=$this->load->view('admin/supermenu',$supermenu,true);
		} // 37 = длина md5 хэша + длина "_.jpg";
		if($this->input->post('frm_img_order') && strlen($this->input->post('frm_img_order'))>37 && $this->usefulmodel->_check_owner($location_id)){
			$this->usermodel->_photoeditor_order_save();
		}
		$photoeditor=Array();
		$photoeditor['location_id']=$location_id;
		$photoeditor['image_id']=$image_id.".jpg";
		$options=$this->usermodel->_photoeditor_locations($location_id);
		$photoeditor['locations']=form_dropdown('location', $options, $location_id, 'id="location" class="span9" size=6 onchange="show_l_table(this.value);"');
		$photoeditor['list']=$this->usermodel->_photoeditor_list($location_id);
		$output['content']=$this->load->view('admin/photoeditor',$photoeditor,true);
		header("Pragma: no-cache");
		$this->load->view('admin/view',$output);
	}

	function commentmanager(){
		$owner=$this->session->userdata("user_id");
		$output['content']=$this->usermodel->_comments_show($owner);
		$output['menu']=$this->load->view('admin/menu','',true);
		if($this->session->userdata('user_class') == md5("secret_userclass1")){
			$supermenu=$this->usefulmodel->semantics_supermenu();
			$output['menu'].=$this->load->view('admin/supermenu',$supermenu,true);
		}
		$this->load->view('admin/view',$output);
	}

}
/* End of file usermodules.php */
/* Location: ./system/application/controllers/usermodules.php */