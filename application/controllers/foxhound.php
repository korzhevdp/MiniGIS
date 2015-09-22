<?php
class Foxhound extends CI_Controller{
	public function __construct(){
		parent::__construct();
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

	private function select_filtered_group($input, $mapset, $current){
		$full         = array(); // массив в который будем складывать все пришедшие параметры в соответствии с алгоритмами :)
		$list         = array(); // массив накопитель найденных объектов. Над ним проводятся операции
		$result = $this->db->query("SELECT
		`properties_list`.algoritm,
		`properties_list`.id
		FROM
		`properties_list`
		WHERE 
		`properties_list`.id IN (".implode(array_keys($input), ",").")");
		if($result->num_rows()){
			foreach($result->result() as $row){
				if (!isset($full[$row->algoritm])) {
					$full[$row->algoritm] = array();
				};
				$full[$row->algoritm][$row->id] = $input[$row->id];
			}
		}
		if(isset($full['u']) && sizeof($full['u'])) {
			$list = $this->select_by_U_algorithm($full['u']);
		}
		if(isset($full['ud']) && sizeof($full['ud'])) {
			$list = $this->test_search_array($list, $this->select_by_UD_algorithm($full['ud']));
		}
		if (isset($full['le']) && sizeof($full['le'])) {
			$list = $this->test_search_array($list, $this->select_by_LE_algorithm($full['le']));
		}
		if (isset($full['me']) && sizeof($full['me'])) {
			$list = $this->test_search_array($list, $this->select_by_ME_algorithm($full['me']));
		}
		if (isset($full['d']) && sizeof($full['d'])) {
			$list = $this->test_search_array($list, $this->select_by_D_algorithm($full['d']));
		}
		if (isset($full['pr']) && sizeof($full['pr'])) {
			$list = $this->test_search_array($list, $this->select_by_PRICE_algorithm($full['pr']));
		}
		if(sizeof($list)) {
			print implode($list, ",");
		} else {
			print "console.log('No Data')";
		}
	}

	private function select_by_type($type){
		$result=$this->db->query("SELECT 
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

	private function test_search_array($list, $addition){
		if(sizeof($addition)) {
			if (sizeof($list)) {
				$list = array_intersect($list, $addition);
			} else {
				$list = $addition;
			}
		}
		return $list;
	}

	private function send_warning($text){
		return true;
	}

	private function select_by_D_algorithm($list) {
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

	private function select_by_UD_algorithm($list) {
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
		//print $this->db->last_query();
		return $output;
		//echo "UD relevant: ".implode($ud_diff,",")."\n";
	}

	private function select_by_ME_algorithm($list) {
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

	private function select_by_LE_algorithm($list) {
		/*
		$list   = array()
		$output = array()
		*/
		$output = array();
			$string  = implode(array_keys($list));
			$count   = sizeof($string);	
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

	private function select_by_PRICE_algorithm($list) {
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

	private function select_by_U_algorithm($list) {
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

	private function pack_results($result){
		$out = array();
		foreach($result->result() as $row){
			$image  = (strlen($row->img)) ? $row->img : "nophoto.gif";
			$string = "\t".$row->id.": { img: '".$image."', description: '".$row->address."', type: '".$row->typename."', name: '".$row->location_name."', attr: '".$row->attr."', coord: '".$row->coord_y."', pr: ".$row->pr_type.", contact: '".$row->contact_info."', link: '".$row->link."' }";
			array_push($out, $string);
		}
		return $out;
	}
}

/* End of file ajax.php */
/* Location: ./system/application/controllers/ajax.php */