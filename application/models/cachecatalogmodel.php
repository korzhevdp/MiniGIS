<?php
class Cachecatalogmodel extends CI_Model{
	function __construct(){
		parent::__construct();
		$this->load->helper('file');
	}
	private function get_statmap($type = 1, $coords = "0,0"){
		$output = array();
		
		$maps   = array(
			1 => "http://static-maps.yandex.ru/1.x/?z=13&l=map&size=128,128&pt=".$coords.",vkbkm",
			2 => "http://static-maps.yandex.ru/1.x/?l=map&size=128,128&pl=".$coords,
			3 => "http://static-maps.yandex.ru/1.x/?l=map&size=128,128&pl=c:ec473fFF,f:FF660020,w:3,".$coords,
			4 => '',
			5 => ''
		);
		$output['statmap'] = $maps[$type];
		if ($type == 1) {
			$act = explode(",", $coords);
			$output['lat'] = $act[1];
			$output['lon'] = $act[0];
		}
		return $output;
	}

	private function get_category_icon($category){
		$out   = "";
		$icons = array(
			'business' => '<img src="'.$this->config->item('api').'/images/icons/briefcase.png" width="16" height="16" border="0" alt="">',
			'health'   => '<img src="'.$this->config->item('api').'/images/icons/health.png" width="16" height="16" border="0" alt="">',
			'services' => '<img src="'.$this->config->item('api').'/images/icons/service-bell.png" width="16" height="16" border="0" alt="">',
			'other'    => '<img src="'.$this->config->item('api').'/images/icons/information.png" width="16" height="16" border="0" alt="">',
			'sport'    => '<img src="'.$this->config->item('api').'/images/icons/sports.png" width="16" height="16" border="0" alt="">',
			'sights'   => '<img src="'.$this->config->item('api').'/images/icons/photo.png" width="16" height="16" border="0" alt="">'
		);
		if (isset($icons[$category])) {
			$out = $icons[$category];
		}
		return $out;
	}

	private function get_location_properties_result($location){
		return $this->db->query("SELECT DISTINCT
		properties_assigned.property_id,
		properties_assigned.location_id,
		properties_assigned.value,
		properties_list.selfname,
		properties_list.property_group,
		properties_list.fieldtype,
		properties_list.multiplier,
		properties_list.divider,
		properties_list.coef,
		properties_list.algoritm,
		properties_list.label
		FROM
		properties_assigned
		INNER JOIN properties_list ON (properties_assigned.property_id = properties_list.id)
		WHERE
		properties_assigned.location_id = ?
		GROUP BY properties_assigned.property_id
		ORDER BY properties_list.label, properties_list.selfname", array($location));
	}

	private function get_location_properties($location){
		$input  = array();
		$output = array();
		$result = $this->get_location_properties_result($location);
		if($result->num_rows()){
			foreach($result->result() as $row){
				if (!isset($input[$row->label])){
					$input[$row->label] = array();
				}
				if ($row->fieldtype === "checkbox") {
					$value = '<span class="line">'.$row->selfname.'</span>';
				}
				if ($row->fieldtype === "textarea") {
					$value = '<p class="line">'.str_replace("\n", "</p><p>", $row->value).'</p>';
				}
				if ($row->fieldtype === "text") {
					$value = $row->value;
					if ($row->algoritm === "me" || $row->algoritm === "le") {
						if ($row->coef != 1) {
							$value = $value * $row->multiplier / $row-> divider;
						}
					}
					$value = '<span class="line">'.$row->selfname.' '.$value.'</span><br>';
				}
				if ($row->fieldtype === "select") {
					$value = '<p class="line">'.$row->selfname.'</p>';
				}
				array_push($input[$row->label], $value);
			}
		}
		foreach ($input as $key =>$val) {
			array_push($output, '<h4>'.$key.'</h4>'.implode($val, ""));
		}
		return implode($output, "\n");
	}

	public function cache_location($location = 0, $with_output = 0, $mode = 'file'){
		$act      = array();
		$result = $this->db->query("SELECT
		`locations`.`address`,
		`locations`.`contact_info` as contact,
		`locations`.`coord_y`,
		`locations`.`coord_obj`,
		`locations`.`coord_array`,
		`locations`.`parent`,
		`locations`.`location_name`,
		IF(`locations_types`.`pl_num` = 0, '', `locations_types`.`name`) AS `name`,
		`locations_types`.`object_group`,
		`locations_types`.pr_type,
		`locations_types`.id as typeid
		FROM
		`locations`
		INNER JOIN `locations_types` ON (`locations`.`type` = `locations_types`.`id`)
		WHERE locations.id = ?", array($location));
		if($result->num_rows()){
			$act = $result->row_array();
			if(in_array($act['pr_type'], array(2, 3))) {
				$act['lat'] = $act['coord_array'];
				$act['lon'] = $act['coord_y'];
			}
		}
		$act = array_merge($act, $this->get_statmap($act['pr_type'], $act['coord_y']));
		$act['content'] = $this->get_location_properties($location);

		if($mode === 'file') {
			write_file('application/views/cache/locations/location_'.$location.".src", $this->load->view('ru/frontend/std_view', $act, true), "w");
		}
		
		if($with_output){
			$this->load->view('ru/frontend/std_view', $act, true);
		}
	}
}
#
/* End of file cachecatalogmodel.php */
/* Location: ./application/models/cachecatalogmodel.php */