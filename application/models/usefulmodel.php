<?php
class Usefulmodel extends CI_Model{
	function __construct(){
		parent::__construct();
	}

	public function check_owner($location_id){
		//на будущее: создание хэша// UPDATE `users_admins` SET `users_admins`.`uid` = sha1(concat(sha1('uid'),`users_admins`.`id`))
		// or GOLDEN hash :)
		if($this->session->userdata('user_id') == $this->config->item('golden_hash')){
			return TRUE;
		}
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
	################################################################################
	public function create_db($db_name){
		$this->db->query("CREATE DATABASE ?
			CHARACTER SET 'utf8'
			COLLATE 'utf8_general_ci'", array($db_name));
	}

	public function create_table_comments(){
		$this->db->query("CREATE TABLE `comments` 
		(
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`location_id` int(11) DEFAULT NULL,
			`auth_name` tinytext COMMENT 'имя автора (никнейм)',
			`contact_info` tinytext COMMENT 'контактная информация об авторе комментария',
			`text` text,
			`date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			`status` enum('N','A','D','I') DEFAULT NULL,
			`ip` int(14) DEFAULT NULL,
			`uid` text,
			`hash` varchar(32) DEFAULT NULL,
			PRIMARY KEY (`id`),
			KEY `location_id` (`location_id`)
		) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8");
	}

	public function create_table_images(){
		$this->db->query("CREATE TABLE `images` 
		(
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`filename` text,
			`owner_id` text,
			`location_id` int(11) DEFAULT '0',
			`order` int(11) DEFAULT '0',
			`orig_filename` text,
			`active` tinyint(1) DEFAULT '1',
			`full` tinytext,
			`med` tinytext,
			`small` tinytext,
			`comment` text,
			PRIMARY KEY (`id`)
		) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 PACK_KEYS=0 ROW_FORMAT=COMPACT");
	}

	public function create_table_locations(){
		$this->db->query("CREATE TABLE `locations` 
		(
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`location_name` text,
			`owner` text,
			`type` int(11) DEFAULT '7',
			`style_override` text,
			`contact_info` text COMMENT 'описание контактного лица',
			`address` text COMMENT 'почтовый адрес объекта',
			`coord_y` longtext COMMENT 'координата в системе Яндекс-карты',
			`coord_obj` longtext,
			`date` date DEFAULT NULL COMMENT 'дата регистрации',
			`parent` int(11) DEFAULT '0' COMMENT 'ссылка на родительское размещение для помещений и номеров. 0 - означает размещение как само по себе',
			`active` tinyint(1) NOT NULL DEFAULT '1',
			`comments` tinyint(1) DEFAULT '0' COMMENT 'включить/выключить комментарии',
			`cache` longtext,
			`cache_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			`loc_hash` tinytext,
			PRIMARY KEY (`id`),
			KEY `type` (`type`),
			KEY `owner` (`owner`(1))
		) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 PACK_KEYS=0 ROW_FORMAT=DYNAMIC");
	}

	public function create_table_locations_types(){
		$this->db->query("CREATE TABLE `locations_types` 
		(
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`has_child` tinyint(1) DEFAULT '1' COMMENT 'Уровень вложенности зазмещений:\r\nздание -> номер -> угол в номере - > койко-место',
			`name` text COMMENT 'Название типа Размещения',
			`attributes` text COMMENT 'строка атрибутов (если есть или будут)',
			`object_group` int(11) DEFAULT NULL COMMENT 'Группа объёктов карты',
			`pl_num` int(11) DEFAULT NULL COMMENT 'Индекс, которому соответствует данный объект в таблице свойств. Применяется для поиска, о добавлении новых объектов см. напоминания по разработке',
			`pr_type` int(11) DEFAULT '1' COMMENT 'presentation type: 1 - point; 2 - route; 3 - area;',
			PRIMARY KEY (`id`),
			KEY `pl_num` (`pl_num`),
			KEY `has_child` (`has_child`),
			KEY `object_group` (`object_group`)
		) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8");
	}

	public function create_table_map_content(){
		$this->db->query("CREATE TABLE `map_content` 
		(
			`id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'индекс страницы типового вызова.',
			`a_layers` int(11) DEFAULT NULL COMMENT 'индексы слоёв (object groups) в отображении, разделённые запятой',
			`a_types` int(11) DEFAULT NULL COMMENT 'индексы типов в активном отображении.',
			`b_layers` text COMMENT 'индексы слоёв (object groups) в отображении, разделённые запятой',
			`objects` text COMMENT 'индексы объектов в активном отображении',
			`b_types` text,
			`name` text COMMENT 'имя набора объектов. Исключительно для справки',
			`hash` text COMMENT 'хэш карты для передачи',
			`active` tinyint(1) DEFAULT '0',
			PRIMARY KEY (`id`)
		) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 PACK_KEYS=0 ROW_FORMAT=DYNAMIC");
	}

	public function create_table_objects_groups(){
		$this->db->query("CREATE TABLE `objects_groups` 
		(
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`name` text,
			`active` tinyint(1) DEFAULT '0',
			`array` text,
			`function` tinytext,
			`adm_array` text,
			PRIMARY KEY (`id`)
		) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 PACK_KEYS=0 ROW_FORMAT=DYNAMIC");
	}

	public function create_table_properties_assigned(){
		$this->db->query("CREATE TABLE `properties_assigned` (
			`location_id` int(11) DEFAULT NULL COMMENT 'индекс локации',
			`property_id` int(11) DEFAULT NULL COMMENT 'индекс назначенного свойства',
			`value` text COMMENT 'значение назначенного свойства'
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 PACK_KEYS=0 ROW_FORMAT=COMPACT");
	}

	public function create_table_properties_list(){
		$this->db->query("CREATE TABLE `properties_list` (
			`id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'генеральный идентификатор свойства',
			`row` int(11) DEFAULT NULL COMMENT 'ряд в таблице генерации элементов',
			`element` int(11) DEFAULT '1' COMMENT 'положение эелемента в строке в таблице генерации ',
			`label` text COMMENT 'имя поля (предварительное описание)',
			`algoritm` tinytext COMMENT 'алгоритм обработки поля',
			`selfname` text COMMENT 'имя поля (подпись)',
			`page` int(11) DEFAULT NULL COMMENT 'страница генерации где размещается элемент',
			`property_group` varchar(20) DEFAULT NULL COMMENT 'группа свойств',
			`fieldtype` enum('text','select','textarea','checkbox','radio') DEFAULT NULL COMMENT 'тип поля при генерации',
			`cat` varchar(20) DEFAULT NULL COMMENT 'признак категории',
			`style` varchar(20) DEFAULT NULL COMMENT 'применяемый стиль',
			`object_group` int(11) DEFAULT '1' COMMENT 'соотносимая свойству группа объектов (генеральная классификация объектов на карте)',
			`parameters` text COMMENT 'дополнительные параметры генерируемого поля',
			`active` tinyint(1) DEFAULT '0',
			`searchable` tinyint(1) DEFAULT '1',
			`multiplier` int(11) DEFAULT '1',
			`divider` int(11) DEFAULT '1',
			`coef` tinytext,
			`linked` int(11) DEFAULT NULL,
			PRIMARY KEY (`id`),
			KEY `cat` (`cat`),
			KEY `page` (`page`),
			KEY `object_group` (`object_group`)
		) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8");
	}

	public function create_table_sheets(){
		$this->db->query("CREATE TABLE `sheets` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`root` int(11) DEFAULT NULL,
			`parent` int(11) DEFAULT NULL,
			`header` text,
			`text` text,
			`owner` char(32) DEFAULT NULL,
			`date` date DEFAULT NULL,
			`ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			`active` tinyint(1) DEFAULT '0',
			`pageorder` int(11) DEFAULT '0',
			`redirect` text,
			`comment` text,
			PRIMARY KEY (`id`)
		) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8");
	}

	public function create_table_timers(){
		$this->db->query("CREATE TABLE `timers`
		(
			`start_point` datetime DEFAULT NULL,
			`end_point` datetime DEFAULT NULL,
			`price` int(11) DEFAULT NULL,
			`type` enum('price','zone','order','request') DEFAULT NULL,
			`location_id` int(11) DEFAULT NULL,
			`order` int(11) DEFAULT NULL,
			KEY `price` (`price`),
			KEY `start_point` (`start_point`),
			KEY `end_point` (`end_point`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8");
	}

	public function create_table_users_admins(){
		$this->db->query("CREATE TABLE `users_admins` 
		(
			`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			`class_id` int(10) unsigned NOT NULL,
			`nick` varchar(255) DEFAULT NULL,
			`passw` varchar(255) DEFAULT NULL,
			`registration_date` date DEFAULT NULL,
			`name_f` tinytext,
			`name_i` tinytext,
			`name_o` tinytext,
			`info` text,
			`uid` text,
			`active` tinyint(1) NOT NULL DEFAULT '0',
			`rating` int(11) DEFAULT '0',
			`valid` tinyint(1) NOT NULL DEFAULT '0',
			`validcode` text NOT NULL,
			`email` text,
			`map_center` text,
			`map_zoom` int(11) DEFAULT '11',
			`map_type` int(11) DEFAULT '2',
			PRIMARY KEY (`id`),
			KEY `uid` (`uid`(1))
		) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 PACK_KEYS=0 ROW_FORMAT=COMPACT");
	}

	public function create_table_users_searches(){ // proposed for deprecation
		$this->db->query("CREATE TABLE `users_searches` 
		(
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`userid` tinytext COMMENT 'сессия из которой пришёл запрос',
			`string` text COMMENT 'строка инициализации, переданная поисковику',
			`ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`),
			KEY `userid` (`userid`(1))
		) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8");
	}

	public function create_table_modx_sessions(){
		$this->db->query("CREATE TABLE `modx_sessions` (
			`session_id` varchar(40) NOT NULL DEFAULT '0',
			`ip_address` varchar(16) NOT NULL DEFAULT '0',
			`user_agent` text NOT NULL,
			`last_activity` int(10) unsigned NOT NULL DEFAULT '0',
			`user_data` text,
			PRIMARY KEY (`session_id`),
			KEY `last_activity` (`last_activity`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8");
	}

	public function create_table_usermaps(){
		$this->db->query("CREATE TABLE `usermaps` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`center_lon` tinytext,
			`center_lat` tinytext,
			`hash_a` tinytext,
			`hash_e` tinytext,
			`zoom` int(3) DEFAULT 10,
			`maptype` tinytext,
			`name` text,
			`author` tinytext,
			`public` tinyint(1) DEFAULT '0',
			PRIMARY KEY (`id`),
			KEY `hash_a` (`hash_a`(4)),
			KEY `hash_e` (`hash_e`(4))
		) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8");
	}

	public function create_table_userobjects(){
		$this->db->query("CREATE TABLE `userobjects` (
			`map_id` tinytext,
			`hash` tinytext,
			`name` tinytext,
			`description` longtext,
			`coord` longtext,
			`attributes` tinytext,
			`address` text,
			`type` int(11) DEFAULT 1,
			`timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			`ip` int(11) DEFAULT NULL,
			`uagent` text,
			`link` text,
			KEY `map_id` (`map_id`(1)),
			KEY `type` (`type`),
			KEY `hash` (`hash`(1))
		) ENGINE=MyISAM DEFAULT CHARSET=utf8");
	}

	public function create_table_userobjects_heap(){	// proposed for deprecation
		$this->db->query("CREATE TABLE `userobjects_heap` (
			`map_id` tinytext,
			`hash` tinytext,
			`name` tinytext,
			`description` longtext,
			`coord` longtext,
			`attributes` tinytext,
			`address` text,
			`type` int(11) DEFAULT NULL,
			`timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			`ip` int(11) DEFAULT NULL,
			`uagent` text,
			KEY `map_id` (`map_id`(1)),
			KEY `type` (`type`),
			KEY `hash` (`hash`(1))
		) ENGINE=MyISAM DEFAULT CHARSET=utf8");
	}
}
#
/* End of file usefulmodel.php */
/* Location: ./system/application/models/usefulmodel.php */