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

	private function perform_tests($full){
		$list = array(); // массив накопитель найденных объектов. Над ним проводятся операции
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
		return $list;
	}

	private function select_filtered_group($input, $mapset, $current){
		$full         = array(); // массив в который будем складывать все пришедшие параметры в соответствии с алгоритмами :)
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
		$list = $this->perform_tests($full);
		if(sizeof($list)) {
			print implode($list, ",");
			return true;
		}
		print "console.log('No Data')";
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

	private function make_test_array($result){
		$output = array();
		foreach($result->result() as $row){
			$testarray[$row->lid][$row->pid] = $row->value;
		}
		return $output;
	}

	private function get_me_le_result($string, $count) {
		return $this->db->query("SELECT
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
	}

	private function select_by_ME_algorithm($list) {
		/*
		$list   = array()
		$output = array()
		*/
		$output = array();
		$string = implode(array_keys($list), ", ");
		$count  = sizeof(array_keys($list));
		$result = $this->get_me_le_result($string, $count);
		if($result->num_rows()) {
			foreach ($this->make_test_array($result) as $loc=>$val){
				$match     = 1;
				$incounter = 0;
				foreach($list as $prop=>$val2){
					($val[$prop] < $val2) ? $match = 0 : $incounter++;
				}
				if((sizeof($list) === $incounter) && $match){
					array_push($output, $loc);
				}
			}
		}
		return $output;
		//echo "UD relevant: ".implode($ud_diff,",")."\n";
	}

	private function select_by_LE_algorithm($list) {
		$output = array();
		$string = implode(array_keys($list));
		$count  = sizeof($string);
		$result = $this->get_me_le_result($string, $count);
		if($result->num_rows()){
			foreach ($this->make_test_array($result) as $loc => $val){
				$match = 1;
				$incounter = 0;
				foreach($list as $prop => $val2){
					($val[$prop] > $val2) ? $match = 0 : $incounter++;
				}
				if((sizeof($list) === $incounter) && $match){
					array_push($output, $loc);
				}
			}
		}else{//если не найдено хотя бы что-то - дальнейший поиск не имеет смысла
			return "console.log('No Data')";
		}
		return $output;
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