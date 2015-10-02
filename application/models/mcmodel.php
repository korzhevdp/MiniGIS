<?php
class Mcmodel extends CI_Model{
	function __construct(){
		parent::__construct();
	}

	private function get_maps_list($mapset){
		$options = array('<option value = "0">Выберите представление карты</option>');
		$setname = "";
		$result = $this->db->query("SELECT
		`map_content`.name,
		`map_content`.id
		FROM
		`map_content`
		ORDER BY `map_content`.name ASC");
		if($result->num_rows()){
			foreach($result->result() as $row) {
				$selected = "";
				if ($row->id == $mapset) {
					$selected = ' selected="selected"';
					$setname = $row->name;
				}
				$string   = '<option value="'.$row->id.'"'.$selected.'>'.$row->name.'</option>';
				array_push($options, $string);
			}
		}
		return array('setname' => $setname, 'options' => implode($options, "\n"));
	}
	
	private function get_map_contents($mapset){
		$mapcontent   = array(
			'a_layers' => '',
			'a_types'  => '',
			'b_layers' => '',
			'b_types'  => '',
			'disabled_layers' => array()
		);
		$result = $this->db->query("SELECT
		`map_content`.a_layers,
		`map_content`.b_layers,
		`map_content`.a_types,
		`map_content`.b_types
		FROM
		`map_content`
		WHERE
		`map_content`.id = ?
		ORDER BY 
		`map_content`.name
		LIMIT 1", ($mapset));
		if($result->num_rows()){
			$mapcontent = $result->row_array(0);
			$mapcontent['disabled_layers'] = array();
		}
		return $mapcontent;
	}

	private function map_layers_get(){
		$output = array(
			'foreground' => array(),
			'background' => array(),
			'disabled'   => array()
		);
		$result = $this->db->query("SELECT
		`objects_groups`.id,
		`objects_groups`.name,
		`objects_groups`.active
		FROM
		`objects_groups`
		WHERE `objects_groups`.active");
		if($result->num_rows()){
			foreach($result->result() as $row){
				# активный слой
				array_push($output['foreground'], '<li><label class="checkbox" for="a_layer'.$row->id.'"><input type="checkbox" class="a_layers" name="a_layer[]" value="'.$row->id.'" ref="'.$row->id.'" id="a_layer'.$row->id.'">'.$row->name.'</label></li>');
				# фоновый слой
				array_push($output['background'], '<li><label class="checkbox" for="b_layer'.$row->id.'"><input type="checkbox" class="b_layers" name="b_layer[]" value="'.$row->id.'" ref="'.$row->id.'" id="b_layer'.$row->id.'">'.$row->name.'</label></li>');
				if (!$row->active) {
					array_push($output['disabled'], $row->id);
				}
			}
			array_push($output['foreground'], '<li><label class="checkbox" for="a_layer0"><input type="checkbox" id="a_layer0" ref="0" value="0"><b>Показывать объекты по типам</b></label></li>');
		}
		return $output;
	}

	private function map_types_get(){
		$output = array(
			'foreground' => array(),
			'background' => array(),
			'groups'	 => array()
		);
		$result = $this->db->query("SELECT 
		`locations_types`.id,
		`locations_types`.`name`,
		`locations_types`.object_group as `gid`,
		`objects_groups`.`name` as `group`
		FROM
		`objects_groups`
		INNER JOIN `locations_types` ON (`objects_groups`.id = `locations_types`.object_group)
		WHERE
		`locations_types`.`pl_num`
		AND `objects_groups`.active");
		if($result->num_rows()){
			# В $a_types и $b_types помещаются объекты переднего плана.
			# Помещаются в соответствии с группой объектов с соответствующие подмассивы, потом из них будут формироваться выходные таблицы.
			foreach($result->result() as $row){
				//disabled если: 1. Выбран активным слоем
				# активный слой
				if (!isset($output['foreground'][$row->gid])) {
					$output['foreground'][$row->gid] = array();
				}
				if (!isset($output['background'][$row->gid])) {
					$output['background'][$row->gid] = array();
				}
				array_push($output['foreground'][$row->gid], '<label class="checkbox"><input type="checkbox" class="a_types" name="a_type[]" value="'.$row->id.'" id="atype'.$row->id.'" ref="'.$row->id.'">'.$row->name.'</label>');
				# фоновый слой

				array_push($output['background'][$row->gid], '<label class="checkbox"><input type="checkbox" class="b_types" name="b_type[]" value="'.$row->id.'" id="btype'.$row->id.'" ref="'.$row->id.'">'.$row->name.'</label>');
				$output['groups'][$row->gid] = $row->group;
			}
		}
		return $output;
	}

	private function generate_fore_types($source, $groups) {
		$output = array();
		foreach($source as $gid => $table) {
			array_push($output, '<li class="object_list atab" id="atab'.$gid.'"><h5>'.$groups[$gid].'</h5>');
			array_push($output, implode($table,"\n"));
			if (!sizeof($table)) {
				array_push($output, 'Не было создано ни одного объекта');
			};
			array_push($output, '</li>');
		}
		return implode($output, "");
	}

	private function generate_back_types($source, $groups) {
		$output = array();
		foreach($source as $gid => $table){
			array_push($output,'<li class="object_list btab" id="btab'.$gid.'"><h5>'.$groups[$gid].'</h5>');
			array_push($output, implode($table,"\n"));
			if (!sizeof($table)) {
				array_push($output, 'Не было создано ни одного объекта');
			}
			array_push($output,'</li>');
		}
		return implode($output, "");
	}

	###################### start of map content section ##########################
	function mc_show($mapset = 0){
		$setname   = "";
		$groups    = array();
		$mapcontent = $this->get_map_contents($mapset);
		$layers = $this->map_layers_get();
		$types = $this->map_types_get();
		$list = $this->get_maps_list($mapset);
		return array(
			'mapset'    => $mapset,
			'mapname'   => $list['setname'],
			'options'   => $list['options'],
			'a_layers'  => $mapcontent['a_layers'],
			'a_types'   => $mapcontent['a_types'],
			'b_layers'  => $mapcontent['b_layers'],
			'b_types'   => $mapcontent['b_types'],
			'disabled_layers' => implode($layers['disabled'], ", "),
			'ca_layers' => implode($layers['foreground'], "\n"),
			'cb_layers' => implode($layers['background'], "\n"),
			'ca_types'  => $this->generate_fore_types($types['foreground'], $types['groups']),
			'cb_types'  => $this->generate_back_types($types['background'], $types['groups'])
		);
	}

	function mc_new(){
		$a_layers = (is_array($this->input->post('a_layer'))) ? implode($this->input->post('a_layer'), ",") : "0";
		$a_types  = (is_array($this->input->post('a_type')))  ? implode($this->input->post('a_type'), ",")  : "0";
		$b_layers = (is_array($this->input->post('a_layer'))) ? implode($this->input->post('b_layer'), ",") : "0";
		$b_types  = (is_array($this->input->post('b_type')))  ? implode($this->input->post('b_type'), ",")  : "0";

		$this->db->query("INSERT INTO
		`map_content`(
			`map_content`.a_layers,
			`map_content`.a_types,
			`map_content`.b_types,
			`map_content`.b_layers,
			`map_content`.name,
			`map_content`.active
		) VALUES ( ?, ?, ?, ?, ?, 1 )", array(
			$a_layers,
			$a_types,
			$b_types,
			$b_layers,
			$this->input->post('mapset_name')
		));
		$map_id = $this->db->insert_id();
		$text = "Администратором ".$this->session->userdata("user_name")." создана карта #".$map_id." - al: ".$a_layers.", at: ".$a_types.", bl: ".$b_layers.", bt: ".$b_types." с именем: ".$this->input->post('mapset_name');
		$this->usefulmodel->insert_audit($text);
		return $map_id;
	}

	function mc_save(){
		$a_layers = (is_array($this->input->post('a_layer'))) ? implode($this->input->post('a_layer'), ",") : "0";
		$a_types  = (is_array($this->input->post('a_type')))  ? implode($this->input->post('a_type'), ",")  : "0";
		$b_layers = (is_array($this->input->post('a_layer'))) ? implode($this->input->post('b_layer'), ",") : "0";
		$b_types  = (is_array($this->input->post('b_type')))  ? implode($this->input->post('b_type'), ",")  : "0";
		$this->db->query("UPDATE
		`map_content`
		SET
		`map_content`.a_layers = ?,
		`map_content`.a_types  = ?,
		`map_content`.b_types  = ?,
		`map_content`.b_layers = ?,
		`map_content`.name     = ?
		WHERE
		`map_content`.id = ?", array(
			$a_layers,
			$a_types,
			$b_types,
			$b_layers,
			$this->input->post('mapset_name'),
			$this->input->post('mapset')
		));
		$text = "Администратором ".$this->session->userdata("user_name")." сохранена карта #".$this->input->post('mapset')." - al: ".$a_layers.", at: ".$a_types.", bl: ".$b_layers.", bt: ".$b_types." с именем: ".$this->input->post('mapset_name');
		$this->usefulmodel->insert_audit($text);
	}
}

/* End of file mcmodel.php */
/* Location: ./system/application/models/mcmodel.php */
