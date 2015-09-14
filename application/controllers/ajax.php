<?php
class Ajax extends CI_Controller{
	public function __construct(){
		parent::__construct();
	}

	public function select_filtered_group($input, $mapset, $current){
		$string       = array();
		$vals         = array();
		$sels         = array();
		$specs        = array();
		$le           = array();
		$me           = array();
		$sorted       = array();
		$idset        = array();
		$full         = array(); // массив в который будем складывать все пришедшие параметры в соответствии с алгоритмами :)
		$difference   = array();
		$p_difference = array();
		$s_difference = array();
		$list         = array(); // массив накопитель найденных объектов. Над ним проводятся операции
		$by_price     = 0;
		$truncate     = 0;
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
		`properties_list`.id IN (".implode($idset,",").")");
		if($result->num_rows()){
			foreach($result->result() as $row){
				(!isset($full[$row->algoritm])) ? $full[$row->algoritm] = array() : "";
				$full[$row->algoritm][$row->id] = $sorted[$row->id];
			}
		}
		//print_r($full);
		#####################################################################################################################
		//if((isset($sels[2]) || isset($sels[3]) || isset($sels[4])) && (!isset($vals[1]))){$vals[1]=4000000;}
		##########################################################################
		###### первичная выборка: в случае, когда непустой список чекбоксов.
		##########################################################################
		# разбор по алгоритму U
		if(isset($full['u']) && sizeof($full['u'])){
			# Формируется список признаков отнесённых к union-алгоритму
			$string = implode(array_keys($full['u']), ",");
			$result = $this->db->query("SELECT 
			IF(locations.parent = 0, locations.id, locations.parent) AS location_id
			FROM
			locations
			INNER JOIN properties_assigned ON (locations.id = properties_assigned.location_id)
			INNER JOIN `locations_types` ON (locations.`type` = `locations_types`.id)
			WHERE
			(properties_assigned.property_id IN (".$string."))");
		}
		#### обратный случай: список чекбоксов пуст - выбираем все объекты
		else{
			$result=$this->db->query("SELECT 
			`locations`.id AS `location_id`,
			`locations`.parent 
			FROM `locations`");
			//return $this->_rno($rno_val);
		}

		if($result->num_rows()){
			foreach($result->result() as $row){
				array_push($list, $row->location_id);
			}
		}else{
			return "console.log('No Data')";
		}
		///echo "U checkboxes relevant: ".implode($list,",")."\n";
		//print_r($full);
		///return implode($idset,",");
		##########################################################################
		###### режем лишнее тут в соответствии с дополнительными условиями поиска:
		###### перебор le полей (Less OR Equal)
		##########################################################################
		# разбор по алгоритму LE
		if(isset($full['le']) && sizeof($full['le'])){
			$le_diff = array();
			$string  = array();
			$string  = array_keys($full['le']);
			$count   = sizeof($string);			// количество свойств, соответствие которым должно быть выдержано
			$inline  = implode($string, ",");	// список свойств, которым надо соответствовать
			// выбираются данные. Учитывается не только соответствие номеру признака (как в случае u-алгоритма), но и соответствие "le" ВСЕМ введённым параметрам
			$result  = $this->db->query("SELECT
			IF(properties_list.coef = 1,properties_assigned.value,properties_assigned.value % properties_list.coef) AS value,
			properties_assigned.property_id as `pid`,
			properties_assigned.location_id as `lid`
			FROM
			`properties_list`
			INNER JOIN properties_assigned ON (`properties_list`.id = properties_assigned.property_id)
			WHERE
			(properties_assigned.property_id IN (".$inline.")) AND
			(properties_assigned.location_id IN (SELECT properties_assigned.location_id FROM properties_assigned WHERE (properties_assigned.property_id IN (".$inline.")) GROUP BY properties_assigned.location_id HAVING (COUNT(*) = ?)))
			ORDER BY
			properties_assigned.location_id", array($count));

			if($result->num_rows()){
				$testarray = array();
				foreach($result->result() as $row){
					$testarray[$row->lid][$row->pid] = $row->value;
				}
				//print_r($testarray);
				foreach ($testarray as $loc => $val){
					$go=1;
					$incounter = 0;
					foreach($full['le'] as $prop => $val2){
						($val[$prop] > $val2) ? $go = 0 : $incounter++;
					}
					if(!(sizeof($full['le']) - $incounter) && $go){
						array_push($le_diff, $loc);
					}
				}
			}else{//если не найдено хотя бы что-то - дальнейший поиск не имеет смысла
				return "console.log('No Data')";
			}
			//echo "LE relevant: ".implode($le_diff,",")."\n";
		}

		if(isset($full['me']) && sizeof($full['me'])){
			$me_diff = array();
			$string  = array();
			$string  = array_keys($full['me']);
			$count   = sizeof($string);
			$inline  = implode($string, ",");
			$result  = $this->db->query("SELECT
			IF(properties_list.coef = 1, properties_assigned.value, properties_assigned.value % properties_list.coef) AS value,
			properties_assigned.property_id as `pid`,
			properties_assigned.location_id as `lid`
			FROM
			`properties_list`
			INNER JOIN properties_assigned ON (`properties_list`.id = properties_assigned.property_id)
			WHERE
			(properties_assigned.property_id IN (".$inline.")) AND
			(properties_assigned.location_id IN (SELECT properties_assigned.location_id FROM properties_assigned WHERE (properties_assigned.property_id IN (".$inline.")) 
			GROUP BY properties_assigned.location_id HAVING (COUNT(*) = ?)))
			ORDER BY
			properties_assigned.location_id", array($count));
			//print $query;
			if($result->num_rows()){
				$testarray = array();
				foreach($result->result() as $row){
					$testarray[$row->lid][$row->pid] = $row->value;
				}
				//print_r($testarray);
				foreach ($testarray as $loc=>$val){
					$go = 1;
					$incounter = 0;
					foreach($full['me'] as $prop=>$val2){
						($val[$prop] < $val2) ? $go = 0 : $incounter++;
					}
					if(!(sizeof($full['me']) - $incounter) && $go){
						array_push($me_diff, $loc);
					}
				}
			} else {//если не найдено хотя бы что-то - дальнейший поиск не имеет смысла
				return "console.log('No Data')";
			}
			//echo "ME relevant: ".implode($me_diff,",")."\n";
		}

		if(isset($full['ud']) && sizeof($full['ud'])){
			$ud_diff = array();
			$string  = array_keys($full['ud']);
			$result  = $this->db->query("SELECT 
			IF(locations.parent = 0,properties_assigned.location_id,locations.parent) as lid
			FROM
			properties_assigned
			INNER JOIN `locations` on (properties_assigned.location_id = `locations`.id)
			WHERE
			(properties_assigned.property_id IN (".implode($string, ',')."))");
			//print $this->db->last_query();
			if($result->num_rows()){
				foreach($result->result() as $row){
					array_push($ud_diff,$row->lid);
				}
			}
			//echo "UD relevant: ".implode($ud_diff,",")."\n";
		}

		if(isset($full['d']) && sizeof($full['d'])){
			$d_diff = array();
			$string = array();
			$string = array_keys($full['d']);
			$count  = sizeof($string);
			$result = $this->db->query("SELECT 
			IF(locations.parent = 0, properties_assigned.location_id, locations.parent) as lid
			FROM
			properties_assigned
			INNER JOIN `locations` ON (properties_assigned.location_id = `locations`.id)
			WHERE
			(properties_assigned.property_id IN (".implode($string, ",")."))
			GROUP BY
			properties_assigned.location_id
			HAVING
			(COUNT(*) = ?)", array($count));
			if($result->num_rows()){
				foreach($result->result() as $row ){
					array_push($d_diff,$row->lid);
				}
			}
			//echo "D relevant: ".implode($d_diff,",")."\n";
		}

		//echo sizeof($difference)."-1\n";
		//Цена - это главное безумие сезона :)
		if(isset($full['pr']) && sizeof($full['pr'])){ # Цена!
			$pr_diff = array();
			$result = $this->db->query("SELECT
			IF(locations.parent, locations.parent, locations.id) AS location_id
			FROM
			timers
			INNER JOIN locations ON (timers.location_id = locations.id)
			WHERE
			(NOW() BETWEEN timers.start_point AND timers.end_point) AND
			`timers`.`type` = 'price' AND
			`timers`.`price` <= ".implode($full['pr'],""));
			//echo mysql_num_rows($result)."price_order\n";
			if($result->num_rows()){
				foreach($result->result() as $row ) {
					array_push($pr_diff, $row->location_id);
				}
			}
		}
		$list = (isset($d_diff)  && sizeof($d_diff))  ? array_intersect($list, $d_diff)  : $list;
		$list = (isset($ud_diff) && sizeof($ud_diff)) ? array_intersect($list, $ud_diff) : $list;
		$list = (isset($le_diff) && sizeof($le_diff)) ? array_intersect($list, $le_diff) : $list;
		$list = (isset($me_diff) && sizeof($me_diff)) ? array_intersect($list, $me_diff) : $list;
		$list = (isset($pr_diff) && sizeof($pr_diff)) ? array_intersect($list, $pr_diff) : $list;
		($current) ? array_push($list, $current) : "";
		if(sizeof($list)){
			print implode($list, ",");
		}else{
			return "console.log('No Data')";
		}
	}


###################################################### NEW CONCEPT ################
	public function send_warning($text){
		return true;
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

	public function get_active_layer($layers_array){
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
			foreach($result->result() as $row){
				$image  = (strlen($row->img)) ? $row->img : "nophoto.gif";
				$string = "\t".$row->id.": { img: '".$image."', description: '".$row->address."', name: '".$row->location_name."', attr: '".$row->attr."', coord: '".$row->coord_y."', pr: ".$row->pr_type.", contact: '".$row->contact_info."', link: '".$row->link."' }";
				array_push($out, $string);
			}
		}
		return $out;
	}

	public function get_active_type($types_array){
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
		$ats = array();
		if($result->num_rows()){
			foreach($result->result() as $row){
				$image  = (strlen($row->img)) ? $row->img : "nophoto.gif";
				$string = "\t".$row->id.": { img: '".$image."', description: '".$row->address."', name: '".$row->location_name."', attr: '".$row->attr."', coord: '".$row->coord_y."', pr: ".$row->pr_type.", contact: '".$row->contact_info."', link: '".$row->link."' }";
				array_push($out, $string);
			}
		}
		return $out;
	}

	public function get_bkg_types($layers_array, $types_array){
		$conditions = array();
		(strlen($types_array))  ? array_push($conditions, "locations.`type` IN (".$types_array.")") : "";
		(strlen($layers_array)) ? array_push($conditions, "locations_types.object_group IN (".$layers_array.")") : "";// запись условий через IN избыточна, но универсальна.
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
			foreach($result->result() as $row){
				$image  = (strlen($row->img)) ? $row->img : "nophoto.gif";
				$string = "\t".$row->id.": { img: '".$image."', description: '".$row->address."', name: '".$row->location_name."', attr: '".$row->attr."', coord: '".$row->coord_y."', pr: ".$row->pr_type.", contact: '".$row->contact_info."', link: '".$row->link."' }";
				array_push($out, $string);
			}
		}
		return $out;
	}
	
	public function select_by_type($type){
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
		(locations_types.id = ?) AND
		(locations.active = 1) AND 
		(users_admins.active = 1) AND 
		(LENGTH(locations.coord_y) > 3)
		ORDER BY
		locations.location_name",array($this->config->item('maps_def_loc'),$type));
		$out = array();
		if($result->num_rows()){
			foreach($result->result() as $row){
				$image  = (strlen($row->img)) ? $row->img : "nophoto.gif";
				$string = $row->id.": { img: '".$image."', description: '".$row->address."', name: '".$row->location_name."', attr: '".$row->attr."', coord: '".$row->coord_y."', pr: ".$row->pr_type.", contact: '".$row->contact_info."', link: '".$row->link."'}";
				array_push($out, $string);
			}
		}
		return "data = { ".implode($out, ",\n")."}";
	}
	
	public function search(){
		//$this->output->enable_profiler(TRUE);
		//$this->db->query("INSERT INTO `users_searches` (`users_searches`.`userid`,`users_searches`.`string`) VALUES (?,?)", array($user,$input));
		if(!$this->input->post('mapset')){
			print "all";
			exit;
		}
		$this->select_filtered_group($this->input->post('sc'), $this->input->post('mapset'), $current = 0);
	}

	public function msearch(){
		$type = $this->input->post('type', true);
		print $this->select_by_type($type);
	}
/* NEW CONCEPT */

}

/* End of file ajax.php */
/* Location: ./system/application/controllers/ajax.php */