<?php
class Ajax extends CI_Controller{
	public function __construct(){
		parent::__construct();
	}

	public function select_filtered_group($input, $mapset, $current){
		$string       = array();
		$sorted       = array();
		$idset        = array();
		$full         = array(); // массив в который будем складывать все пришедшие параметры в соответствии с алгоритмами :)
		$list         = array(); // массив накопитель найденных объектов. Над ним проводятся операции
		$le_diff      = array();
		$me_diff      = array();
		$ud_diff      = array();
		$d_diff       = array();
		$pr_diff      = array();
		##########################################################################
		###### формирование массива принятых параметров поиска
		##########################################################################
		$idset = array_keys($input);
		foreach($input as $key => $val){
			$sorted[$key] = $val;
		}

		# Выборка алгоритмов поиска и пересортировка параметров в массивы алгоритмов
		$result = $this->db->query("SELECT
		`properties_list`.algoritm,
		`properties_list`.id
		FROM
		`properties_list`
		WHERE 
		`properties_list`.id IN (".implode($idset, ",").")");
		if($result->num_rows()){
			foreach($result->result() as $row){
				if (!isset($full[$row->algoritm])) {
					$full[$row->algoritm] = array();
				};
				$full[$row->algoritm][$row->id] = $sorted[$row->id];
			}
		}

		//print_r($full);

		# разбор по алгоритму U
		if(isset($full['u']) && sizeof($full['u'])){
			$list = $this->select_by_U_algorithm($full['u']);# Формируется список признаков отнесённых к union-алгоритму
			//echo "U checkboxes relevant: ".implode($list,",")."\n";
		}
		
		if(isset($full['ud']) && sizeof($full['ud'])){
			$ud_diff = $this->select_by_UD_algorithm($full['ud']);
			//echo "UD relevant: ".implode($ud_diff, ",")."\n";
		}

		# разбор по алгоритму LE

		## результаты LE-, ME-выборок с нулевой длиной должны останавливать поиск! (?)
		if (isset($full['le']) && sizeof($full['le'])){
			$le_diff = $this->select_by_LE_algorithm($full['le']);
			//echo "LE relevant: ".implode($le_diff, ",")."\n";
		}

		if (isset($full['me']) && sizeof($full['me'])){
			$me_diff = select_by_ME_algorithm($full['me']);
			//echo "ME relevant: ".implode($me_diff,",")."\n";
		}

		if (isset($full['d']) && sizeof($full['d'])){
			$d_diff = $this->select_by_D_algorithm($full['d']);
			//echo "D relevant: ".implode($d_diff,",")."\n";
		}

		if (isset($full['pr']) && sizeof($full['pr'])){ # Цена!
			$pr_diff = $this->select_by_PRICE_algorithm($full['pr']);
		}

		########################################## сравниваем массивы
		if(sizeof($d_diff)) {
			if (sizeof($list)) {
				$list = array_intersect($list, $d_diff);
			} else {
				$list = $d_diff;
			}
		}

		if(sizeof($ud_diff)) {
			if (sizeof($list)) {
				$list = array_intersect($list, $ud_diff);
			} else {
				$list = $ud_diff;
			}
		}

		if(sizeof($le_diff)) {
			if (sizeof($list)) {
				$list = array_intersect($list, $le_diff);
			} else {
				$list = $le_diff;
			}
		}

		if(sizeof($me_diff)) {
			if (sizeof($list)) {
				$list = array_intersect($list, $me_diff);
			} else {
				$list = $me_diff;
			}
		}

		if(sizeof($pr_diff)) {
			if (sizeof($list)) {
				$list = array_intersect($list, $pr_diff);
			} else {
				$list = $pr_diff;
			}
		}

		if(sizeof($list)){
			print implode($list, ",");
		}else{
			return "console.log('No Data')";
		}
	}


###################################################### NEW CONCEPT ################
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

	public function search() {
		if ( $this->input->post('mapset') != 0 ) {
			$this->select_filtered_group($this->input->post('sc'), $this->input->post('mapset'), $current = 0);
		} else {
			print "all";
		}
	}

	public function msearch(){
		print $this->select_by_type($this->input->post('type', true));
	}

	function send_warning($text){
		return true;
	}

	function get_active_layer($layers_array){
		// Layer - эквивалент object_group;
		$result = $this->db->query("SELECT
		(SELECT `images`.`filename` FROM `images` WHERE `images`.`location_id` = `locations`.`id` AND `images`.`order` <= 1 LIMIT 1) as img,
		locations.id,
		CONCAT_WS(' ',IF(locations_types.id IN(10, 12, 13, 15), 'объект', locations_types.name),locations.location_name) AS location_name,
		IF(LENGTH(locations.contact_info), locations.contact_info, 'контактная информация отсутствует') AS contact_info,
		IF(LENGTH(locations.address), locations.address, ?) AS address,
		locations.coord_y,
		locations_types.pr_type,
		CONCAT('/page/gis/', locations.id) AS link,
		objects_groups.array,
		IF(LENGTH(`locations`.`style_override`) > 1, `locations`.`style_override`, IF(LENGTH(locations_types.attributes), locations_types.attributes, 'twirl#houseIcon')) AS attr
		FROM
		locations_types
		INNER JOIN locations ON (locations_types.id = locations.`type`)
		INNER JOIN objects_groups ON (locations_types.object_group = objects_groups.id)
		INNER JOIN users_admins ON (locations.owner = users_admins.uid)
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

	function pack_results($result){
		$out = array();
		foreach($result->result() as $row){
			$image  = (strlen($row->img)) ? $row->img : "nophoto.gif";
			$string = "\t".$row->id.": { img: '".$image."', description: '".$row->address."', name: '".$row->location_name."', attr: '".$row->attr."', coord: '".$row->coord_y."', pr: ".$row->pr_type.", contact: '".$row->contact_info."', link: '".$row->link."' }";
			array_push($out, $string);
		}
		return $out;
	}

	function get_active_type($types_array){
		// Layer - эквивалент object_group;
		$result=$this->db->query("SELECT 
		(SELECT `images`.`filename` FROM `images` WHERE `images`.`location_id` = `locations`.`id` AND `images`.`order` <= 1 LIMIT 1) as img,
		locations.id,
		CONCAT_WS(' ',IF(locations_types.pl_num = 0, 'объект', locations_types.name),locations.location_name) AS location_name,
		IF(LENGTH(locations.contact_info), locations.contact_info, 'контактная информация отсутствует') AS contact_info,
		IF(LENGTH(locations.address), locations.address, ?) AS address,
		TRIM(locations.coord_y) AS coord_y,
		CONCAT('/page/gis/', locations.id) AS link,
		objects_groups.array,
		locations_types.pr_type,
		IF(LENGTH(`locations`.`style_override`) > 1, `locations`.`style_override`, IF(LENGTH(locations_types.attributes), locations_types.attributes, 'default#houseIcon')) AS attr
		FROM
		locations_types
		INNER JOIN locations ON (locations_types.id = locations.`type`)
		INNER JOIN objects_groups ON (locations_types.object_group = objects_groups.id)
		INNER JOIN users_admins ON (locations.owner = users_admins.uid)
		WHERE
		locations.`type` IN (".$types_array.")
		AND locations.active
		AND users_admins.active
		AND LENGTH(locations.coord_y) > 3
		ORDER BY locations.id ASC", array( $this->config->item('maps_def_loc')) );
		$out = array();
		if($result->num_rows()){
			$out = $this->pack_results($result);
		}
		return $out;
	}

	function get_bkg_types($layers_array, $types_array){
		$conditions = array();
		(strlen($types_array))  ? array_push($conditions, "locations.`type` IN (".$types_array.")") : "";
		(strlen($layers_array)) ? array_push($conditions, "locations_types.object_group IN (".$layers_array.")") : "";

		$result = $this->db->query("SELECT
		(SELECT `images`.`filename` FROM `images` WHERE `images`.`location_id` = `locations`.`id` AND `images`.`order` <= 1 LIMIT 1) as img,
		locations.id,
		CONCAT_WS(' ',IF(locations_types.pl_num = 0, 'объект', locations_types.name),locations.location_name) AS location_name,
		IF(LENGTH(locations.contact_info), locations.contact_info, 'контактная информация отсутствует') AS contact_info,
		IF(LENGTH(locations.address), locations.address, ?) AS address,
		locations.coord_y,
		locations_types.pr_type,
		CONCAT('/page/gis/', locations.id) AS link,
		IF(LENGTH(`locations`.`style_override`) > 1, `locations`.`style_override`, IF(LENGTH(locations_types.attributes), locations_types.attributes, 'default#houseIcon')) AS attr
		FROM
		locations_types
		INNER JOIN locations ON (locations_types.id = locations.`type`)
		INNER JOIN objects_groups ON (locations_types.object_group = objects_groups.id)
		INNER JOIN users_admins ON (locations.owner = users_admins.uid)
		WHERE
		(".implode($conditions, " OR ").") 
		AND locations.active 
		AND (users_admins.active AND LENGTH(locations.coord_y) > 3)", array($this->config->item('maps_def_loc'), $types_array));
		$out = array();
		if($result->num_rows()){
			$out = $this->pack_results($result);
		}
		return $out();
	}

	function select_by_type($type){
		$result=$this->db->query("SELECT 
		(SELECT `images`.`filename` FROM `images` WHERE `images`.`location_id` = `locations`.`id` AND `images`.`order` <= 1 LIMIT 1) as img,
		locations.id,
		CONCAT_WS(' ',IF(locations_types.pl_num = 0, 'объект', locations_types.name),locations.location_name) AS location_name,
		IF(LENGTH(locations.contact_info), locations.contact_info, 'контактная информация отсутствует') AS contact_info,
		IF(LENGTH(locations.address), locations.address, ?) AS address,
		locations.coord_y,
		locations_types.pr_type,
		CONCAT('/page/gis/', locations.id) AS link,
		objects_groups.array,
		IF(LENGTH(`locations`.`style_override`) > 1, `locations`.`style_override`, IF(LENGTH(locations_types.attributes), locations_types.attributes, 'default#houseIcon')) AS attr
		FROM
		locations_types
		INNER JOIN locations ON (locations_types.id = locations.`type`)
		INNER JOIN objects_groups ON (locations_types.object_group = objects_groups.id)
		INNER JOIN users_admins ON (locations.owner = users_admins.uid)
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

	function select_by_D_algorithm($list) {
		/*
		$list   = array()
		$output = array()
		*/
		$output = array();
		$string = implode(array_keys($list), ", ");
		$count  = sizeof(array_keys($list));
		$result = $this->db->query("SELECT
		IF(locations.parent = 0, properties_assigned.location_id, locations.parent) AS lid
		FROM
		properties_assigned
		INNER JOIN `locations` ON (properties_assigned.location_id = `locations`.id)
		WHERE
		properties_assigned.property_id IN (".$string.")
		GROUP BY
		properties_assigned.location_id
		HAVING
		COUNT(*) = ?", array($count));
		if($result->num_rows()){
			foreach($result->result() as $row ){
				array_push($output, $row->lid);
			}
		}
		return $output;
		//echo "D relevant: ".implode($d_diff,",")."\n";
	}

	function select_by_UD_algorithm($list) {
		/*
		$list   = array()
		$output = array()
		*/
		$output  = array();
		$string  = implode(array_keys($list), ",");
		$result  = $this->db->query("SELECT
		IF(locations.parent = 0, properties_assigned.location_id, locations.parent) AS lid
		FROM
		properties_assigned
		INNER JOIN `locations` ON (properties_assigned.location_id = `locations`.id)
		WHERE
		properties_assigned.property_id IN (".$string.")");
		if($result->num_rows()){
			foreach($result->result() as $row) {
				array_push($output, $row->lid);
			}
		}
		print $this->db->last_query();
		return $output;
		//echo "UD relevant: ".implode($ud_diff,",")."\n";
	}

	function select_by_ME_algorithm($list) {
		/*
		$list   = array()
		$output = array()
		*/
		$output = array();
		$string = implode(array_keys($list), ", ");
		$count  = sizeof(array_keys($list));
		$result = $this->db->query("SELECT
		IF(properties_list.coef = 1, properties_assigned.value, (properties_assigned.value / properties_list.divider * properties_list.multiplier)) AS value,
		properties_assigned.property_id as `pid`,
		properties_assigned.location_id as `lid`
		FROM
		`properties_list`
		INNER JOIN properties_assigned ON (`properties_list`.id = properties_assigned.property_id)
		WHERE
		properties_assigned.property_id IN (".$string.")
		AND properties_assigned.location_id IN (
			SELECT
			properties_assigned.location_id
			FROM properties_assigned
			WHERE properties_assigned.property_id IN (".$string.")
			GROUP BY properties_assigned.location_id
			HAVING COUNT(*) = ?
		)
		ORDER BY
		properties_assigned.location_id", array($count));
		if($result->num_rows()) {
			$testarray = array();
			foreach($result->result() as $row){
				$testarray[$row->lid][$row->pid] = $row->value;
			}
			foreach ($testarray as $loc=>$val){
				$match     = 1;
				$incounter = 0;
				foreach($list as $prop=>$val2){
					($val[$prop] < $val2) ? $match = 0 : $incounter++;
				}
				if((sizeof($list) - $incounter) === 0 && $match){
					array_push($output, $loc);
				}
			}
		}
		return $output;
		//echo "UD relevant: ".implode($ud_diff,",")."\n";
	}

	function select_by_LE_algorithm($list) {
		/*
		$list   = array()
		$output = array()
		*/
		$output = array();
			$string  = implode(array_keys($list));
			$count   = sizeof($string);	
			/*
			* $string - список свойств
			* $count - количество свойств, соответствие которым должно быть выдержано
			* Учитывается не только соответствие номеру признака (как в случае u-алгоритма), но и соответствие  ВСЕМ введённым "le"-параметрам
			*/
			$result  = $this->db->query("SELECT
			IF(properties_list.coef = 1, properties_assigned.value, (properties_assigned.value / properties_list.divider * properties_list.multiplier)) AS value,
			properties_assigned.property_id as `pid`,
			properties_assigned.location_id as `lid`
			FROM
			`properties_list`
			INNER JOIN properties_assigned ON (`properties_list`.id = properties_assigned.property_id)
			WHERE
			properties_assigned.property_id IN (".$string.")
			AND properties_assigned.location_id IN (
				SELECT
				properties_assigned.location_id
				FROM
				properties_assigned
				WHERE
				properties_assigned.property_id IN (".$string.")
				GROUP BY properties_assigned.location_id
				HAVING COUNT(*) = ?
			)
			ORDER BY
			properties_assigned.location_id", array($count));

			if($result->num_rows()){
				$testarray = array();
				foreach($result->result() as $row){
					$testarray[$row->lid][$row->pid] = $row->value;
				}
				//print_r($testarray);
				foreach ($testarray as $loc => $val){
					$match = 1;
					$incounter = 0;
					foreach($list as $prop => $val2){
						($val[$prop] > $val2) ? $match = 0 : $incounter++;
					}
					if((sizeof($list) - $incounter) === 0 && $match){
						array_push($le_diff, $loc);
					}
				}
			}else{//если не найдено хотя бы что-то - дальнейший поиск не имеет смысла
				return "console.log('No Data')";
			}
		return $output;
		//echo "UD relevant: ".implode($ud_diff,",")."\n";
	}

	function select_by_PRICE_algorithm($list) {
		$output = array();
		$result = $this->db->query("SELECT
		IF(locations.parent, locations.parent, locations.id) AS location_id
		FROM
		timers
		INNER JOIN locations ON (timers.location_id = locations.id)
		WHERE
		NOW() BETWEEN timers.start_point AND timers.end_point
		AND `timers`.`type` = 'price'
		AND `timers`.`price` <= ".implode($full['pr'], ""));
		//echo mysql_num_rows($result)."price_order\n";
		if($result->num_rows()){
			foreach($result->result() as $row ) {
				array_push($output, $row->location_id);
			}
		}
		return $output;
	}

	function select_by_U_algorithm($list) {
		/*
		$list   = array()
		$output = array()
		*/
		$output = array();
		# Формируется список признаков отнесённых к union-алгоритму
		$string = implode(array_keys($list), ", ");
		$result = $this->db->query("SELECT 
		IF(locations.parent = 0, locations.id, locations.parent) AS location_id
		FROM
		locations
		INNER JOIN properties_assigned ON (locations.id = properties_assigned.location_id)
		INNER JOIN `locations_types` ON (locations.`type` = `locations_types`.id)
		WHERE
		(properties_assigned.property_id IN (".$string."))");
		if($result->num_rows()) {
			foreach($result->result() as $row) {
				array_push($output, $row->location_id);
			}
		}
		return $output;
	}
/* NEW CONCEPT */

}

/* End of file ajax.php */
/* Location: ./system/application/controllers/ajax.php */