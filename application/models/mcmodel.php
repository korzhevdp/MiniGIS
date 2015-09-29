<?php
class Mcmodel extends CI_Model{
	function __construct(){
		parent::__construct();
	}

	###################### start of map content section ##########################
	function mc_show($mapset = 0){
		//$this->output->enable_profiler(TRUE);
		$mapcontent   = array(
			'a_layers' => '',
			'a_types'  => '',
			'b_layers' => '',
			'b_types'  => '',
			'disabled_layers' => array()
		);
		$options   = array('<option value = "0">Выберите представление карты</option>');
		$setname   = "";
		$a_types   = array();
		$b_types   = array();
		$cf_layers   = array();
		$cb_layers   = array();
		$cf_types   = array();
		$cb_types   = array();
		$groups    = array();
		########### выборка списка  слоёв
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
		########### выборка свойств слоёв
		if ($mapset) {
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
		}
		###########
		############# формирование таблиц слоёв (активный/фоновый)
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
				array_push($cf_layers,'<li><label class="checkbox" for="a_layer'.$row->id.'"><input type="checkbox" class="a_layers" name="a_layer[]" value="'.$row->id.'" ref="'.$row->id.'" id="a_layer'.$row->id.'">'.$row->name.'</label></li>');
				# фоновый слой
				array_push($cb_layers,'<li><label class="checkbox" for="b_layer'.$row->id.'"><input type="checkbox" class="b_layers" name="b_layer[]" value="'.$row->id.'" ref="'.$row->id.'" id="b_layer'.$row->id.'">'.$row->name.'</label></li>');
				if (!$row->active) {
					array_push($mapcontent['disabled_layers'], $row->id);
				}
			}
			array_push($cf_layers, '<li><label class="checkbox" for="a_layer0"><input type="checkbox" id="a_layer0" ref="0" value="0"><b>Показывать объекты по типам</b></label></li>');
		}
		#
		############# формирование таблиц типов (активный/фоновый слой)
		#
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
				if (!isset($a_types[$row->gid])) {
					$a_types[$row->gid] = array();
				}
				array_push($a_types[$row->gid],'<label class="checkbox"><input type="checkbox" class="a_types" name="a_type[]" value="'.$row->id.'" id="atype'.$row->id.'" ref="'.$row->id.'">'.$row->name.'</label>');
				# фоновый слой
				if (!isset($b_types[$row->gid])) {
					$b_types[$row->gid] = array();
				}
				array_push($b_types[$row->gid],'<label class="checkbox"><input type="checkbox" class="b_types" name="b_type[]" value="'.$row->id.'" id="btype'.$row->id.'" ref="'.$row->id.'">'.$row->name.'</label>');
				$groups[$row->gid] = $row->group;

			}
			# окончательная сортировка и оформление списков
			$cf_types = array();
			$cb_types = array();
			foreach($a_types as $gid => $table){
				array_push($cf_types,'<li class="object_list atab" id="atab'.$gid.'"><h5>'.$groups[$gid].'</h5>');
				array_push($cf_types, implode($table,"\n"));
				if (!sizeof($table)) {
					array_push($cf_types, 'Не было создано ни одного объекта');
				};
				array_push($cf_types,'</li>');
			}
			foreach($b_types as $gid => $table){
				array_push($cb_types,'<li class="object_list btab" id="btab'.$gid.'"><h5>'.$groups[$gid].'</h5>');
				array_push($cb_types, implode($table,"\n"));
				if (!sizeof($table)) {
					array_push($cb_types, 'Не было создано ни одного объекта');
				}
				array_push($cb_types,'</li>');
			}
		}
		#форма выбора слоя началась
		return array(
			'mapset'    => $mapset,
			'mapname'   => $setname,
			'a_layers'  => $mapcontent['a_layers'],
			'a_types'   => $mapcontent['a_types'],
			'b_layers'  => $mapcontent['b_layers'],
			'b_types'   => $mapcontent['b_types'],
			'disabled_layers' => implode($mapcontent['disabled_layers'], ", "),
			'options'   => implode($options,   "\n"),
			'ca_layers' => implode($cf_layers, "\n"),
			'cb_layers' => implode($cb_layers, "\n"),
			'ca_types'  => implode($cf_types,  "\n"),
			'cb_types'  => implode($cb_types,  "\n")
		);
	}

	function mc_new(){
		$a_layers = ($this->input->post('a_layer') !== FALSE && is_array($this->input->post('a_layer'))) ? implode($this->input->post('a_layer'), ",") : "0";
		$a_types  = ($this->input->post('a_type')  !== FALSE && is_array($this->input->post('a_type')))  ? implode($this->input->post('a_type'), ",")  : "0";
		$b_layers = ($this->input->post('b_layer') !== FALSE && is_array($this->input->post('a_layer'))) ? implode($this->input->post('b_layer'), ",") : "0";
		$b_types  = ($this->input->post('b_type')  !== FALSE && is_array($this->input->post('b_type')))  ? implode($this->input->post('b_type'), ",")  : "0";

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
		$id = $this->db->insert_id();
		$text = "Администратором ".$this->session->userdata("user_name")." создана карта #".$id." - al: ".$a_layers.", at: ".$a_types.", bl: ".$b_layers.", bt: ".$b_types." с именем: ".$this->input->post('mapset_name');
		$this->usefulmodel->insert_audit($text);
		return $id;
	}

	function mc_save(){
		$a_layers = ($this->input->post('a_layer') !== FALSE && is_array($this->input->post('a_layer'))) ? implode($this->input->post('a_layer'), ",") : "0";
		$a_types  = ($this->input->post('a_type')  !== FALSE && is_array($this->input->post('a_type')))  ? implode($this->input->post('a_type'), ",")  : "0";
		$b_layers = ($this->input->post('b_layer') !== FALSE && is_array($this->input->post('a_layer'))) ? implode($this->input->post('b_layer'), ",") : "0";
		$b_types  = ($this->input->post('b_type')  !== FALSE && is_array($this->input->post('b_type')))  ? implode($this->input->post('b_type'), ",")  : "0";
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
