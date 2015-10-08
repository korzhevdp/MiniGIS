<?php
class Map extends CI_Controller {
	function __construct(){
		parent::__construct();
		$this->load->model('mapmodel');
		$this->load->model('usefulmodel');
		if(!$this->session->userdata('lang')){
			$this->session->set_userdata('lang', 'en');
		}
	}

	public function index(){
		$this->simple(1);
	}

	public function simple($mapset = 1){
		$act = $this->mapmodel->map_data_get($mapset);
		$this->load->view($this->session->userdata('lang').'/frontend/frontend_map2', $act);
	}

	public function set_language(){
		//$this->output->enable_profiler(TRUE);
		$this->session->set_userdata('lang', $this->input->post('lang'));
		$this->load->helper("url");
		redirect($this->input->post('redirect'));
	}

	public function type($type){
		$map_header = "";
		$this->load->config('translations_g');
		$this->load->config('translations_c');
		$groups = $this->config->item('groups');
		$categories = $this->config->item('categories');
		$lang = $this->session->userdata('lang');
		$result = $this->db->query("SELECT 
			`objects_groups`.id,
			`locations_types`.id as type
			FROM
			`locations_types`
			INNER JOIN `objects_groups` ON (`locations_types`.object_group = `objects_groups`.id)
			WHERE `locations_types`.`id` = ? 
			LIMIT 1", array($type));
		if($result->num_rows()){
			$row = $result->row(0);
			$map_header = $groups[$row->id][$lang]." - ".$categories[$row->type][$lang];
		}
		$act = array(
			'footer'		=> $this->load->view($this->session->userdata('lang').'/frontend/page_footer', array(), true),
			'otype'			=> $type,
			'mapset'		=> 0,
			'menu'			=> $this->load->view('cache/menus/menu_'.$this->session->userdata('lang'), array(), true).$this->usefulmodel->rent_menu().$this->usefulmodel->admin_menu(),
			'keywords'		=> $this->config->item('map_keywords'),
			'map_header'	=> $map_header,
			'switches'		=> 'switches = {}',
			'selector'		=> '<div class="altSelector">'.$map_header.'</div>',
			'map_center'	=> $this->config->item('map_center'),
			'title'			=> $this->config->item('site_title_start')." Интерактивная карта"
		);
		$this->load->view($this->session->userdata('lang').'/frontend/frontend_map2', $act);
	}

	public function get_map_content(){
		//$this->output->enable_profiler(TRUE);
		$map_content = array();
		$map_content2 = array();
		$result      = $this->db->query("SELECT 
		`map_content`.a_layers,
		`map_content`.a_types,
		`map_content`.b_types,
		`map_content`.b_layers
		FROM
		`map_content`
		WHERE
		`map_content`.`active` AND
		`map_content`.`id` = ?", array($this->input->post('mapset')));
		if($result->num_rows()){
			$row = $result->row();
			if($row->a_layers){
				$map_content = $map_content + $this->get_active_layer($row->a_layers);
			}
			if($row->a_types){
				$map_content = $map_content + $this->get_active_type($row->a_types);
			}
			if($row->b_layers || $row->b_types){
				$map_content2 = $map_content2 + $this->get_bkg_types($row->b_layers, $row->b_types);
			}
			//print_r($map_content);
			print "ac = {\n".implode($map_content, ",\n")."};\nbg = {".implode($map_content2, ",\n")."\n};";
		}else{
			send_warning("Ошибка целостности в ajax/get_map_content()".$this->db->last_query());
			print "console.log('Кажется, приключилась страшная ошибка. Наши специалисты уже работают над ней. Попробуйте открыть карту чуть позже')";
		}
	}

	private function pack_results($result){
		$out = array();
		foreach($result->result() as $row){
			$attrm = explode("#", $row->attr);
			if($row->paid && $attrm[0] === "free" || $attrm[0] === "paid"){
				$attr = "paid#".$attrm[1];
			} else {
				$attr = $row->attr;
			}
			$image  = (strlen($row->img)) ? $row->img : "nophoto.gif";
			$string = "\t".$row->id.": { img: '".$image."', description: '".$row->address."', type: '".$row->typename."', name: '".$row->location_name."', attr: '".$attr."', coord: '".$row->coord_y."', pr: ".$row->pr_type.", contact: '".$row->contact_info."', link: '".$row->link."', p: ".$row->paid." }";
			array_push($out, $string);
		}
		return $out;
	}

	private function get_active_layer($layers_array){
		// Layer - эквивалент object_group;
		$result = $this->db->query("SELECT
		(SELECT `images`.`filename` FROM `images` WHERE `images`.`location_id` = `locations`.`id` AND `images`.`order` <= 1 LIMIT 1) as img,
		locations.id,
		IF(locations_types.pl_num = 0, 'объект', locations_types.name) AS typename,
		locations.location_name,
		IF(LENGTH(locations.contact_info), locations.contact_info, 'не предоставлены') AS contact_info,
		IF(LENGTH(locations.address), locations.address, ?) AS address,
		locations.coord_y,
		locations_types.pr_type,
		CONCAT('/page/gis/', locations.id) AS link,
		objects_groups.array,
		IF(LENGTH(`locations`.`style_override`) > 1, `locations`.`style_override`, IF(LENGTH(locations_types.attributes), locations_types.attributes, 'twirl#houseIcon')) AS attr,
		IF(ISNULL(payments.paid) OR `payments`.`paid` = 0, 0, 1) AS `paid`
		FROM
		locations_types
		RIGHT OUTER JOIN locations ON (locations_types.id = locations.`type`)
		LEFT OUTER JOIN objects_groups ON (locations_types.object_group = objects_groups.id)
		LEFT OUTER JOIN users_admins ON (locations.owner = users_admins.uid)
		LEFT OUTER JOIN payments ON (locations.id = payments.location_id)
		WHERE
		locations_types.object_group IN (".$layers_array.")
		AND locations.active
		AND users_admins.active
		AND LENGTH(locations.coord_y) > 3
		ORDER BY locations.id ASC", array(
			$this->config->item('maps_def_loc')
		));
		$out = array();
		if($result->num_rows()){
			$out = $this->pack_results($result);
		}
		return $out;
	}

	private function get_active_type($types_array){
		// Layer - эквивалент object_group;
		$result=$this->db->query("SELECT 
		(SELECT `images`.`filename` FROM `images` WHERE `images`.`location_id` = `locations`.`id` AND `images`.`order` <= 1 LIMIT 1) as img,
		locations.id,
		IF(locations_types.pl_num = 0, 'объект', locations_types.name) AS typename,
		locations.location_name,
		IF(LENGTH(locations.contact_info), locations.contact_info, 'контактная информация отсутствует') AS contact_info,
		IF(LENGTH(locations.address), locations.address, ?) AS address,
		TRIM(locations.coord_y) AS coord_y,
		CONCAT('/page/gis/', locations.id) AS link,
		objects_groups.array,
		locations_types.pr_type,
		IF(LENGTH(`locations`.`style_override`) > 1, `locations`.`style_override`, IF(LENGTH(locations_types.attributes), locations_types.attributes, 'default#houseIcon')) AS attr,
		IF(ISNULL(payments.paid) OR `payments`.`paid` = 0, 0, 1) AS `paid`
		FROM
		locations_types
		RIGHT OUTER JOIN locations ON (locations_types.id = locations.`type`)
		LEFT OUTER JOIN objects_groups ON (locations_types.object_group = objects_groups.id)
		LEFT OUTER JOIN users_admins ON (locations.owner = users_admins.uid)
		LEFT OUTER JOIN payments ON (locations.id = payments.location_id)
		WHERE
		locations.`type` IN (".$types_array.")
		AND locations.active
		AND users_admins.active
		AND LENGTH(locations.coord_y) > 3
		ORDER BY users_admins.rating DESC, locations.location_name ASC", array( $this->config->item('maps_def_loc')) );
		$out = array();
		if($result->num_rows()){
			$out = $this->pack_results($result);
		}
		return $out;
	}

	private function get_bkg_types($layers_array, $types_array){
		$conditions = array();
		(strlen($types_array))  ? array_push($conditions, "locations.`type` IN (".$types_array.")") : "";
		(strlen($layers_array)) ? array_push($conditions, "locations_types.object_group IN (".$layers_array.")") : "";

		$result = $this->db->query("SELECT
		(SELECT `images`.`filename` FROM `images` WHERE `images`.`location_id` = `locations`.`id` AND `images`.`order` <= 1 LIMIT 1) as img,
		locations.id,
		IF(locations_types.pl_num = 0, 'объект', locations_types.name) AS typename,
		locations.location_name,
		IF(LENGTH(locations.contact_info), locations.contact_info, 'контактная информация отсутствует') AS contact_info,
		IF(LENGTH(locations.address), locations.address, ?) AS address,
		locations.coord_y,
		locations_types.pr_type,
		CONCAT('/page/gis/', locations.id) AS link,
		IF(LENGTH(`locations`.`style_override`) > 1, `locations`.`style_override`, IF(LENGTH(locations_types.attributes), locations_types.attributes, 'default#houseIcon')) AS attr,
		IF(ISNULL(payments.paid) OR `payments`.`paid` = 0, 0, 1) AS `paid`
		FROM
		locations_types
		RIGHT OUTER JOIN locations ON (locations_types.id = locations.`type`)
		LEFT OUTER JOIN objects_groups ON (locations_types.object_group = objects_groups.id)
		LEFT OUTER JOIN users_admins ON (locations.owner = users_admins.uid)
		LEFT OUTER JOIN payments ON (locations.id = payments.location_id)
		WHERE
		(".implode($conditions, " OR ").") 
		AND locations.active 
		AND (users_admins.active AND LENGTH(locations.coord_y) > 3)", array($this->config->item('maps_def_loc'), $types_array));
		$out = array();
		if($result->num_rows()){
			$out = $this->pack_results($result);
		}
		return $out;
	}
	
	private function select_by_type($type){
		$result = $this->db->query("SELECT 
		(SELECT `images`.`filename` FROM `images` WHERE `images`.`location_id` = `locations`.`id` AND `images`.`order` <= 1 LIMIT 1) as img,
		locations.id,
		IF(locations_types.pl_num = 0, 'объект', locations_types.name) AS typename,
		locations.location_name,
		IF(LENGTH(locations.contact_info), locations.contact_info, 'контактная информация отсутствует') AS contact_info,
		IF(LENGTH(locations.address), locations.address, ?) AS address,
		locations.coord_y,
		locations_types.pr_type,
		CONCAT('/page/gis/', locations.id) AS link,
		objects_groups.array,
		IF(LENGTH(`locations`.`style_override`) > 1, `locations`.`style_override`, IF(LENGTH(locations_types.attributes), locations_types.attributes, 'default#houseIcon')) AS attr,
		IF(ISNULL(payments.paid) OR `payments`.`paid` = 0, 0, 1) AS `paid`
		FROM
		locations_types
		RIGHT OUTER JOIN locations ON (locations_types.id = locations.`type`)
		LEFT OUTER JOIN objects_groups ON (locations_types.object_group = objects_groups.id)
		LEFT OUTER JOIN users_admins ON (locations.owner = users_admins.uid)
		LEFT OUTER JOIN payments ON (locations.id = payments.location_id)
		WHERE
		(locations_types.id = ?)
		AND locations.active
		AND users_admins.active
		AND (LENGTH(locations.coord_y) > 3)
		ORDER BY
		locations.location_name", array($this->config->item('maps_def_loc'), $type));
		$out = array();
		if($result->num_rows()){
			$out = $this->pack_results($result);
		}
		return "data = { ".implode($out, ",\n")."\n}";
	}

	public function msearch(){
		print $this->select_by_type($this->input->post('type', true));
	}

}

/* End of file map.php */
/* Location: ./system/application/controllers/map.php */