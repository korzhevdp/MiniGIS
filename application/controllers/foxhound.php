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
		$string       = array();
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
				$full[$row->algoritm][$row->id] = $input[$row->id];
			}
		}
		# разбор по алгоритму U
		if(isset($full['u']) && sizeof($full['u'])) {
			$list = $this->select_by_U_algorithm($full['u']);# Формируется список признаков отнесённых к union-алгоритму
		}
		if(isset($full['ud']) && sizeof($full['ud'])) {
			$this->test_search_array($this->select_by_UD_algorithm($full['ud']));
		}
		## результаты LE-, ME-выборок с нулевой длиной должны останавливать поиск! (?)
		if (isset($full['le']) && sizeof($full['le'])) {
			$this->test_search_array($this->select_by_LE_algorithm($full['le']));
		}
		if (isset($full['me']) && sizeof($full['me'])) {
			$this->test_search_array($this->select_by_ME_algorithm($full['me']));
		}
		if (isset($full['d']) && sizeof($full['d'])) {
			$this->test_search_array($this->select_by_D_algorithm($full['d']));
		}
		if (isset($full['pr']) && sizeof($full['pr'])) { # Цена!
			$this->test_search_array($pr_diff = $this->select_by_PRICE_algorithm($full['pr']));
		}
		########################################## сравниваем массивы
		if(sizeof($list)) {
			print implode($list, ",");
		} else {
			print "console.log('No Data')";
		}
	}

	private function test_search_array($addition){
		if(sizeof($addition)) {
			if (sizeof($list)) {
				$list = array_intersect($list, $addition);
			} else {
				$list = $addition;
			}
		}
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
}

/* End of file ajax.php */
/* Location: ./system/application/controllers/ajax.php */