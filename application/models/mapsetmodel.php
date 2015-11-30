<?php
class Mapsetmodel extends CI_Model{
	function __construct(){
		parent::__construct();
		$this->load->helper('file');
	}

	public function cache_layers($layers, $output = array()) {
		if(!is_array($output)){
			$output = array();
		}
		$input = array();
		$result = $this->db->query("SELECT
		locations.id
		FROM
		locations_types
		RIGHT OUTER JOIN locations ON (locations_types.id = locations.`type`)
		WHERE
		LENGTH(locations.coord_y)
		AND locations.active
		AND (locations_types.pr_type IS NOT NULL)
		AND (locations_types.object_group IN (".$layers."))");
		if($result->num_rows()){
			foreach($result->result() as $row){
				array_push($input, $row->id);
			}
		}
		return $this->pack_results($input, $output);
	}

	public function cache_types($types, $output) {
		if (!is_array($output)) {
			$output = array();
		}
		$input = array();
		$result = $this->db->query("SELECT 
		locations.id
		FROM
		locations
		WHERE
		LENGTH(locations.coord_y)
		AND locations.active
		AND (locations.`type` IN (".$types."))");
		if($result->num_rows()){
			foreach($result->result() as $row){
				array_push($input, $row->id);
			}
		}
		return $this->pack_results($input, $output);
	}

	public function cache_objects($objects, $output) {
		if (!is_array($output)) {
			$output = array();
		}
		$input = array();
		$result = $this->db->query("SELECT
		locations.id
		FROM
		locations
		WHERE
		LENGTH(locations.coord_y)
		AND locations.active
		AND (locations.id IN (".$objects."))");
		if($result->num_rows()){
			foreach($result->result() as $row){
				array_push($input, $row->id);
			}
		}
		return $this->pack_results($input, $output);
	}

	private function add_child_nodes($input) {
		$ids = implode($input, ",");
		$result = $this->db->query("SELECT 
		`composites`.location
		FROM
		`composites`
		WHERE
		`composites`.parent IN (".$this->db->escape($ids).")
		AND `composites`.location NOT IN (".$this->db->escape($ids).")");
		if($result->num_rows()){
			foreach($result->result() as $row){
				array_push($input, $row->location);
			}
		}
		return $input;
	}

	private function get_composites_array($input) {
		$output = array();
		$result = $this->db->query("SELECT 
		`composites`.location,
		`composites`.parent
		FROM
		`composites`
		WHERE
		`composites`.parent IN (".implode($input, ",").")");
		if($result->num_rows()){
			foreach($result->result() as $row){
				if (!isset($output[$row->parent])){
					$output[$row->parent] = array();
				}
				array_push($output[$row->parent], $row->location);
			}
		}
		return $output;
	}

	private function pack_results($input, $output) {
		$composites = $this->get_composites_array($input);
		$input      = $this->add_child_nodes($input);
		$result     = $this->get_properties_by_ids($input);
		if ($result->num_rows()) {
			$this->load->config("translations_c");
			$translations = $this->config->item("categories");
			foreach ($result->result() as $row) {
				foreach ($this->config->item("lang") as $lang=>$langname) {
					$comp = (isset($composites[$row->id])) ? implode($composites[$row->id], ",") : "";
					$type = (isset($translations[$row->type_id][$lang]) && strlen($translations[$row->type_id][$lang]))
						? $translations[$row->type_id][$lang]
						: $translations[$row->type_id][$this->config->item("native_lang")] ;
					$output[$lang][$row->id] = $row->id.": { description: '".$row->address."', type: '".$type."', name: '".$row->location_name."', attr: '".$row->attributes."', coord: '".$row->coord_y."', pr: ".$row->pr_type.", contact: '".$row->contact_info."', link: '/page/gis/".$row->id."', p: ".$row->paid.", comp: [".$comp."] }";
				}
			}
		}
		return $output;
	}

	private function get_properties_by_ids($ids = array()) {
		$result = $this->db->query("SELECT
		locations.id,
		locations.location_name,
		locations_types.id AS type_id,
		locations.address,
		locations.contact_info,
		locations_types.pr_type,
		IF(LENGTH(locations.style_override), locations.style_override, locations_types.attributes) AS attributes,
		locations.coord_y,
		IF(ISNULL(`payments`.paid), 0, 1) as paid
		FROM
		locations_types
		RIGHT OUTER JOIN locations ON (locations_types.id = locations.`type`)
		LEFT OUTER JOIN `payments` ON (locations.id = `payments`.location_id)
		WHERE
		locations.id IN (".implode($ids, ",").")
		ORDER BY locations.id");
		return $result;
	}

	public function cache_mapset($mapset) {
		$a_output = array();
		$b_output = array();
		$result = $this->db->query("SELECT
		`map_content`.a_layers,
		`map_content`.a_types,
		`map_content`.b_types,
		`map_content`.b_layers
		FROM
		`map_content`
		WHERE
		`map_content`.`active` 
		AND `map_content`.`id` = ?", array($mapset));
		if ($result->num_rows()) {
			foreach($result->result() as $row) {
				if (strlen($row->a_layers) && $row->a_layers) {
					$a_output = $this->cache_layers($row->a_layers, $a_output);
				}
				if (strlen($row->a_types) && $row->a_types) {
					$a_output = $this->cache_types($row->a_types , $a_output);
				}
				if (strlen($row->b_layers) && $row->b_layers) {
					$b_output = $this->cache_layers($row->b_layers, $b_output);
				}
				if (strlen($row->b_types) && $row->b_types) {
					$b_output = $this->cache_types($row->b_types , $b_output);
				}
			}
		}
		foreach ($a_output as $lang=>$data) {
			$content = (isset($a_output[$lang])) ? implode($a_output[$lang], ",\n") : "";
			$ac = "ac = {\n".$content."\n};\n";
			$content = (isset($b_output[$lang])) ? implode($b_output[$lang], ",\n") : "";
			$bg = "bg = {\n".$content."\n}";
			//print getcwd()."<br>";
			if (!file_exists(getcwd()."/application/views/cache/mapsets")) {
				//print "directory not exists<br>Trying to create: ".getcwd()."/views/cache/mapsets<br>";
				if (mkdir( getcwd()."/application/views/cache/mapsets", 0775, true )) {
					//print "directory created<br>";
				}
			}
			$this->load->helper("file");
			if ( write_file("application/views/cache/mapsets/mapset".$mapset."_".$lang.".src", $ac.$bg)) {
				//print "file written<br>";
			}
		}
	}

	public function cache_type($type) {
		$output = array();
		$output = $this->cache_types($type, $output);
		foreach ($output as $lang=>$data) {
			$output_file = "data = {\n".implode($data, ",\n")."\n};";
			//print getcwd()."<br>";
			if (!file_exists(getcwd()."/application/views/cache/mapsets")) {
				//print "directory not exists<br>Trying to create: ".getcwd()."/views/cache/mapsets<br>";
				if (mkdir( getcwd()."/application/views/cache/mapsets", 0775, true )) {
					//print "directory created<br>";
				}
			}
			$this->load->helper("file");
			if ( write_file("application/views/cache/mapsets/type".$type."_".$lang.".src", $output_file)) {
				//print "file written<br>";
			}
		}
	}

	public function cache_group($group) {
		$output = array();
		$output = $this->cache_layers($group, $output);
		foreach ($output as $lang=>$data) {
			$output_file = "data = {\n".implode($data, ",\n")."\n};";
			//print getcwd()."<br>";
			if (!file_exists(getcwd()."/application/views/cache/mapsets")) {
				//print "directory not exists<br>Trying to create: ".getcwd()."/views/cache/mapsets<br>";
				if (mkdir( getcwd()."/application/views/cache/mapsets", 0775, true )) {
					//print "directory created<br>";
				}
			}
			$this->load->helper("file");
			if ( write_file("application/views/cache/mapsets/group".$group."_".$lang.".src", $output_file)) {
				//print "file written<br>";
			}
		}
	}
	
	public function recache_datasets($location) {
		$layer = 0;
		$type  = 0;
		$result = $this->db->query("SELECT 
		`locations`.`type`,
		`locations_types`.object_group
		FROM
		`locations`
		INNER JOIN `locations_types` ON (`locations`.`type` = `locations_types`.id)
		WHERE `locations`.id = ?
		LIMIT 1", array($location));
		if ($result->num_rows()) {
			foreach ($result->result() as $row) {
				$type  = $row->type;
				$group = $row->object_group;
			}
		}
		if ($type) {
			$this->cache_type($type);
		}
		if ($group) {
			$this->cache_group($group);
		}
		$result = $this->db->query("SELECT 
		`map_content`.id,
		`map_content`.a_layers,
		`map_content`.a_types,
		`map_content`.b_layers,
		`map_content`.b_types
		FROM
		`map_content`");
		if ($result->num_rows()) {
			foreach ($result->result() as $row) {
				$a_layers = explode(",", $row->a_layers);
				$a_types  = explode(",", $row->a_types);
				$b_layers = explode(",", $row->a_layers);
				$b_types  = explode(",", $row->b_types);
				if (in_array($group, $a_layers) || in_array($group, $b_layers) || in_array($type, $a_types) || in_array($type, $b_types)) {
					$this->cache_mapset($row->id);
				}
			}
		}

	}
}
#
/* End of file mapsetmodel.php */
/* Location: ./application/models/mapsetmodel.php */