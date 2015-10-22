<?php
class Semanticsmodel extends CI_Model{
	function __construct(){
		parent::__construct();
	}

	private function get_group_result($object_group){
			return $this->db->query("SELECT
			IF(properties_list.page = 1, 0, 1) AS editable,
			properties_list.id,
			properties_list.`label`,
			properties_list.selfname,
			properties_list.property_group,
			`properties_list`.`algoritm`,
			properties_list.cat,
			properties_list.linked,
			(SELECT `properties_bindings`.searchable FROM `properties_bindings` WHERE `properties_bindings`.property_id = properties_list.id AND `properties_bindings`.groups = ?) AS searchable,
			properties_list.active
			FROM
			properties_list
			".(($object_group) ? "WHERE (properties_list.id IN (SELECT properties_bindings.property_id FROM properties_bindings WHERE properties_bindings.groups = ?))" : "").
			" ORDER BY
			properties_list.page,
			properties_list.`row`,
			properties_list.element", array($object_group, $object_group));
	}

	private function get_nongroup_result(){
		return $this->db->query("SELECT
			IF(properties_list.page = 1, 0, 1) AS editable,
			properties_list.id,
			properties_list.`label`,
			properties_list.selfname,
			properties_list.property_group,
			`properties_list`.`algoritm`,
			properties_list.cat,
			properties_list.linked,
			properties_list.active
			FROM
			properties_list
			ORDER BY
			properties_list.page,
			properties_list.`row`,
			properties_list.element");
	}

	public function show_semantics($object_group = 0, $type_id = 0) {
		$table  = array();
		if ($object_group) {
			$result = $this->get_group_result($object_group);
		} else {
			$result = $this->get_nongroup_result();
		}
		if ($result->num_rows()) {
			foreach ($result->result_array() as $row) {
				$row['object_group'] = $object_group;
				$row['infoclass']	 = ($row['editable'])	? ' title="Назначаемое свойство"' : ' class="warning" title="Главный признак типа/категории объекта"';
				if ($object_group) {
					$row['pic1']	 = ($row['searchable'])	? 'find.png'			: 'lightbulb_off.png';
					$row['title1']	 = ($row['searchable'])	? 'Доступно для поиска' : 'Поиск по параметру не производится';
				} else {
					$row['pic1']	 = "";
					$row['title1']	 = "";
				}
				$row['pic2']		 = ($row['active'])		? 'lightbulb.png'		: 'lightbulb_off.png';
				$row['title2']		 = ($row['active'])		? 'Параметр активен'	: 'Параметр отключен';
				$row['type_id']		 = $type_id;
				array_push($table, $this->load->view("admin/parameterline", $row, true));
			}
		}
		$out = (sizeof($table)) ? implode($table, "\n") : "<tr><td colspan=6>Nothing Found!</td></tr>";
		return $out;
	}

	public function show_semantics_values($object_group = 1, $type = 0, $property = 0) {
		//$this->output->enable_profiler(TRUE);
		$output = array(
			'row'				=> '',
			'element'			=> '',
			'label'				=> '',
			'selfname'			=> '',
			'algoritm'			=> '',
			'page'				=> '',
			'property_group'	=> '',
			'fieldtype'			=> '',
			'row'				=> '',
			'cat'				=> '',
			'parameters'		=> '',
			'searchable'		=> '',
			'active'			=> '',
			'divider'			=> '',
			'multiplier'		=> '',
			'og_name'			=> '',
			'linked'			=> '',
			'property'			=> 0
		);

		$result = $this->db->query("SELECT 
		properties_list.`row`,
		properties_list.element,
		properties_list.label,
		properties_list.selfname,
		properties_list.page,
		properties_list.parameters,
		properties_list.algoritm,
		IF(LENGTH(properties_list.linked) = 0, 0, properties_list.linked) AS linked,
		properties_list.searchable,
		properties_list.property_group,
		properties_list.fieldtype,
		properties_list.cat,
		properties_list.divider,
		properties_list.multiplier,
		properties_list.active,
		`objects_groups`.name AS `og_name`
		FROM
		`objects_groups`
		INNER JOIN properties_list ON (`objects_groups`.id = properties_list.object_group)
		WHERE
		(properties_list.id = ?)", array($property));
		if ($result->num_rows()) {
			$output = $result->row_array();
		}
		$result->free_result();

		$output['object_group']			= $object_group;
		$output['property']				= $property;
		$output['searchable']			= (($output['searchable']) ? 'checked="checked"' : '');
		$output['active']				= (($output['active']) ? 'checked="checked"' : '');
		$output['property_group_name']	= $output['property_group'];
		$output['cat_name']				= $output['cat'];
		$output['property_group']		= $this->pack_datalist($this->db->query("SELECT DISTINCT properties_list.property_group AS vals FROM properties_list ORDER BY vals"));
		$output['cat']					= $this->pack_datalist($this->db->query("SELECT DISTINCT properties_list.cat AS vals FROM properties_list ORDER BY vals"));
		$output['linked']				= $this->get_geosemantic_links($output['linked']);
		$output['groups']				= $this->get_bound_groups($property);
		return $output;
	}
	
	private function get_bound_groups($property) {
		$output = array();
		$result = $this->db->query("SELECT
		objects_groups.id,
		objects_groups.active,
		objects_groups.name,
		IF(objects_groups.id in (SELECT `properties_bindings`.groups FROM `properties_bindings` WHERE `properties_bindings`.`property_id` = ?), 1 , 0 ) AS bind
		FROM
		objects_groups", array($property));
		if($result->num_rows()){
			foreach($result->result() as $row) {
				$checked   = ($row->bind)   ? ' checked="checked"' : "";
				$active    = ($row->active) ? "" : ' disabled="disabled"';
				$liactive  = ($row->active) ? "" : ' class="muted"';
				$string    = '<li'.$liactive.'><label class="checkbox" for="g'.$row->id.'"><input type="checkbox" form="ogp_edit_form" name="group[]" id="g'.$row->id.'" value="'.$row->id.'"'.$checked.$active.'>'.$row->name.'</label></li>';
				array_push($output, $string);
			}
		}
		return '<div><ul class="groupBindings">'.implode($output, "").'</ul></div>';
	}

	private function pack_datalist($result) {
		$array = array();
		if($result->num_rows()){
			foreach($result->result() as $row){
				$string = '<option value="'.$row->vals.'">'.$row->vals.'</option>';
				array_push($array, $string);
			}
		}
		$result->free_result();
		return implode($array, "\n");
	}

	private function get_geosemantic_links($link) {
		$output = array('<option value=0>Не установлена</option>');
		$result = $this->db->query("SELECT
		locations.id,
		CONCAT_WS(' ', locations_types.name, locations.location_name) AS name,
		IF(locations.id = ?, 1, 0) AS act
		FROM
		locations
		INNER JOIN locations_types ON (locations.`type` = locations_types.id)
		WHERE
		(locations_types.pr_type = 3)
		ORDER BY name", array($link));
		if($result->num_rows()){
			foreach($result->result() as $row){
				$string = '<option value="'.$row->id.'"'.(($row->act) ? ' selected="selected"' : '').'>'.$row->name.'</option>';
				array_push($output, $string);
			}
		}
		$result->free_result();
		return implode($output, "\n");
	}

	private function update_semantics() {
		$this->db->query("UPDATE
		`properties_list`
		SET
		`properties_list`.`row`          = ?,
		`properties_list`.element        = ?,
		`properties_list`.label          = ?,
		`properties_list`.selfname       = ?,
		`properties_list`.page           = ?,
		`properties_list`.parameters     = ?,
		`properties_list`.property_group = ?,
		`properties_list`.fieldtype      = ?,
		`properties_list`.cat            = ?,
		`properties_list`.`algoritm`     = ?,
		`properties_list`.`linked`       = ?,
		`properties_list`.`multiplier`   = ?,
		`properties_list`.`divider`      = ?
		WHERE
		`properties_list`.`id` = ?", array(
			$this->input->post('row'),
			$this->input->post('element'),
			$this->input->post('label'),
			$this->input->post('selfname'),
			$this->input->post('page'),
			$this->input->post('parameters'),
			$this->input->post('property_group'),
			$this->input->post('fieldtype'),
			$this->input->post('cat'),
			$this->input->post('algoritm'),
			$this->input->post('linked'),
			$this->input->post('multiplier'),
			$this->input->post('divider'),
			$this->input->post('property')
		));
		return $this->input->post('property');
	}

	private function create_semantics() {
		$this->db->query("INSERT INTO
		`properties_list` (
		`properties_list`.`row`,
		`properties_list`.`element`,
		`properties_list`.`label`,
		`properties_list`.`selfname`,
		`properties_list`.`page`,
		`properties_list`.`parameters`,
		`properties_list`.`property_group`,
		`properties_list`.`fieldtype`,
		`properties_list`.`cat`,
		`properties_list`.`algoritm`,
		`properties_list`.`linked`,
		`properties_list`.`multiplier`,
		`properties_list`.`divider`
		) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )",
		array(
			$this->input->post('row', true),
			$this->input->post('element', true),
			$this->input->post('label', true),
			$this->input->post('selfname', true),
			$this->input->post('page', true),
			$this->input->post('parameters', true),
			$this->input->post('property_group', true),
			$this->input->post('fieldtype', true),
			$this->input->post('cat', true),
			$this->input->post('algoritm', true),
			$this->input->post('linked', true),
			(strlen($this->input->post('multiplier', true))) ? $this->input->post('multiplier', true) : 1,
			(strlen($this->input->post('divider', true)))    ? $this->input->post('divider', true)    : 1
		));
		return $this->db->insert_id();
	}

	private function set_semantics_bindings($property) {
		$groups   = $this->input->post('group', true);
		$ingroups = array();
		foreach($groups as $val) {
			$string = '('.$property.', '.$val.', 1)';
			array_push($ingroups, $string);
		}
		$this->db->query("DELETE FROM `properties_bindings` WHERE `properties_bindings`.property_id = ?", array($property));
		$this->db->query("INSERT INTO `properties_bindings` (
			`properties_bindings`.property_id,
			`properties_bindings`.groups,
			`properties_bindings`.searchable
		) VALUES ".implode($ingroups, ",\n"));
	}

	private function set_semantics_linkage($property) {
		$data = array($this->input->post('linked'), $property);
		$this->db->query("DELETE
		FROM
		`properties_assigned`
		WHERE
		`properties_assigned`.location_id = ?
		AND `properties_assigned`.property_id = ?", $data);
		$this->db->query("INSERT INTO 
		`properties_assigned` (
			`properties_assigned`.location_id,
			`properties_assigned`.property_id,
			`properties_assigned`.value
		) VALUES (?, ?, 1)", $data);
	}

	public function save_semantics() {
		//$this->output->enable_profiler(TRUE);
		$mode    = $this->input->post('mode');
		$group   = $this->input->post('object_group'); // для редиректа :)
		if ($mode === "save") {
			$property = $this->update_semantics();
		}
		if ($mode === "new") {
			$property = $this->create_semantics();
		}
		$this->set_semantics_bindings($property);
		if ($this->input->post('linked')) {
			$this->set_semantics_linkage($property);
		}
		redirect('admin/library/'.$group."/0/".$property."/2");
	}
}

/* End of file gismodel.php */
/* Location: ./system/application/models/semanticsmodel.php */
