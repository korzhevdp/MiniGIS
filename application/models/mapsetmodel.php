<?php
class Mapsetmodel extends CI_Model{
	function __construct(){
		parent::__construct();
		$this->load->helper('file');
	}

	private function make_common_caching($result) {
		$input = array();
		if ($result->num_rows()) {
			foreach($result->result() as $row) {
				array_push($input, $row->id);
			}
		}
		return $this->pack_results($input);
	}

	public function cache_layers($layers) {
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
		return $this->make_common_caching($result);
	}

	public function cache_types($types, $output) {
		$result = $this->db->query("SELECT 
		locations.id
		FROM
		locations
		WHERE
		LENGTH(locations.coord_y)
		AND locations.active
		AND (locations.`type` IN (".$types."))");
		return $this->make_common_caching($result, $output);
	}

	public function cache_objects($objects, $output) {
		$result = $this->db->query("SELECT
		locations.id
		FROM
		locations
		WHERE
		LENGTH(locations.coord_y)
		AND locations.active
		AND (locations.id IN (".$objects."))");
		$this->make_common_caching($result, $output);
	}

	private function get_child_nodes($input) {
		$ids = implode($input, ",");
		$composites = array();
		$result = $this->db->query("SELECT 
		`composites`.location
		FROM
		`composites`
		WHERE
		`composites`.parent IN (".$ids.")
		AND `composites`.location NOT IN (".$ids.")");
		if($result->num_rows()){
			foreach($result->result() as $row){
				array_push($composites, $row->location);
			}
		}
		return $this->get_properties_by_ids($composites);
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

	private function pack_results($input) {
		$output = array(
			'cdata' => array(),
			'pdata' => array()
		);
		$this->load->config("translations_c");
		$translation = $this->config->item("categories");
		$composites  = $this->get_composites_array($input);
		$result      = $this->get_properties_by_ids($input);
		if ($result !== "No data") {
			if ($result->num_rows()) {
				foreach ($result->result() as $row) {
					foreach ($this->config->item("lang") as $lang=>$langname) {
						$output['pdata'][$lang][$row->id] = $this->get_cache_string( $row, $composites, $translation, $lang );
					}
				}
			}
		}
		$result->free_result();
		$result = $this->get_child_nodes($input);
		if ($result !== "No data") {
			if ($result->num_rows()) {
				foreach ($result->result() as $row) {
					foreach ($this->config->item("lang") as $lang=>$langname) {
						$output['cdata'][$lang][$row->id] = $this->get_cache_string( $row, $composites, $translation, $lang );
					}
				}
			}
		}
		return $output;
	}

	private function get_cache_string( $row, $composites, $translation, $lang ){
		$comp = (isset($composites[$row->id])) ? implode($composites[$row->id], ",") : "";
		$type = (isset($translation[$row->type_id][$lang]) && strlen($translation[$row->type_id][$lang]))
			? $translation[$row->type_id][$lang]
			: $translation[$row->type_id][$this->config->item("native_lang")] ;
		# ready to return???
		return $row->id.": { img: '".$row->image."', description: '".preg_replace("/'/", "&quot;", $row->address)."', type: '".preg_replace("/'/", "&quot;", $type)."', name: '".preg_replace("/'/", "&quot;", $row->location_name)."', attr: '".$row->attributes."', coord: '".$row->coord_y."', pr: ".$row->pr_type.", contact: '".preg_replace("/'/", "&quot;", $row->contact_info)."', link: '/page/gis/".$row->id."', p: ".$row->paid.", comp: [".$comp."] }";
	}

	private function get_properties_by_ids($ids = array()) {
		if (!sizeof($ids)) {
			return "No data";
		}
		$result = $this->db->query("SELECT
		(SELECT `images`.filename FROM `images` WHERE `images`.`location_id` = `locations`.`id` ORDER BY `images`.`order` ASC LIMIT 1) AS `image`,
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
		$this->check_directories();
		$this->write_mapset_cache($mapset, $a_output, $b_output);
	}

	private function write_mapset_cache($mapset, $a_output, $b_output) {
		$this->load->helper("file");
		foreach ($a_output['pdata'] as $lang=>$data) {
			$content = (isset($a_output['pdata'][$lang])) ? implode($a_output['pdata'][$lang], ",\n") : "";
			$ac_filepart = "ac = {\n".$content."\n};\n";
			$content = (isset($b_output['pdata'][$lang])) ? implode($b_output['pdata'][$lang], ",\n") : "";
			$bg_filepart = "bg = {\n".$content."\n};";
			$content  = (isset($a_output['ñdata'][$lang])) ? implode($a_output['ñdata'][$lang], ",\n") : "";
			$content .= (isset($b_output['ñdata'][$lang])) ? implode($b_output['ñdata'][$lang], ",\n") : "";
			$cn_filepart = "\ncdata = {\n".$content."\n}";
			write_file("application/views/cache/mapsets/mapset".$mapset."_".$lang.".src", $ac_filepart.$bg_filepart.$cn_filepart);
		}
	}

	private function check_directories() {
		if (!file_exists(getcwd()."/application/views/cache/mapsets")) {
			mkdir( getcwd()."/application/views/cache/mapsets", 0775, true );
		}
	}

	private function write_cache($output, $filename) {
		$this->load->helper("file");
		$this->check_directories();
		foreach ($output['pdata'] as $lang=>$data) {
			$points   = (isset($output['pdata'][$lang])) ? implode($output['pdata'][$lang], ",\n\t") : "" ;
			$children = (isset($output['cdata'][$lang])) ? implode($output['cdata'][$lang], ",\n\t") : "" ;
			$output_file = "data = {\n\t".$points."\n};\ncdata = {\n\t".$children."\n};";
			write_file("application/views/cache/mapsets/".$filename."_".$lang.".src", $output_file);
		}
	}

	public function cache_type($type) {
		$output = array();
		$output = $this->cache_types($type, $output);
		$this->write_cache($output, "type".$type);
	}

	public function cache_group($group) {
		$output = array();
		$output = $this->cache_layers($group, $output);
		//print_r($output);
		$this->write_cache($output, "group".$group);
	}

	private function cache_affected_mapsets($group, $type) {
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
		$this->cache_affected_mapsets($group, $type);
	}
}
#
/* End of file mapsetmodel.php */
/* Location: ./application/models/mapsetmodel.php */