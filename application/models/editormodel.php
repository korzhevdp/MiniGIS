<?php
class Editormodel extends CI_Model{
	function __construct(){
		parent::__construct();
	}
	
	public function starteditor($mode = "edit", $id = 0){
		//$this->output->enable_profiler(TRUE);
		if ($mode == "edit") {
			if ($id) {
				if(!$this->usefulmodel->check_owner($id)){
					$this->load->helper("url");
					redirect("admin/library");
				}
				$data = $this->get_summary("location", $id);
				$output = array(
					//'pr_type'			=> $data['pr_type'],
					'content'			=> $this->load->view('editor/summary', $data, true),
					'panel'				=> $this->load->view('editor/btncontrol1', $data, true),
					'baspointstypes'	=> $this->get_bas_points_types(),
					'menu'				=> $this->load->view('admin/menu', '', true)
				);
			}
			$this->session->set_userdata('c_l', $id);
		}
		if ($mode == "add") {
			$data = $this->get_summary("type", $id);
			$output = array(
				//'pr_type'			=> $data['pr_type'];
				'content'			=> $this->load->view('editor/summary', $data, true),
				'panel'				=> $this->load->view('editor/btncontrol1', $data, true),
				'baspointstypes'	=> $this->get_bas_points_types(),
				'menu'				=> $this->load->view('admin/menu', array(), true)
			);
		}
		return $output;
	}
	
	private function get_summary($type, $id){
		$output = array(
			'id' => 0,
			'location_name'		=> 'Новое имя',
			'contact_info'		=> 'Контактная информация',
			'address'			=> 'Новый адрес',
			'active'			=> 0,
			'type'				=> 0,
			'typelist'			=> 0,
			'description'		=> 'Новое описание',
			'pr_type'			=> 1,
			'attributes'		=> "not defined",
			'style_override'	=> "not defined",
			'coord_y'			=> 0,
			'comments'			=> 0
		);

		if ($type == "location") {
			$result = $this->db->query("SELECT
			locations.id,
			locations.location_name,
			locations.address,
			locations.active,
			locations.`type`,
			locations.coord_y,
			locations.contact_info,
			`locations_types`.name AS `description`,
			IF(LENGTH(locations.style_override) > 0, locations.style_override,`locations_types`.attributes) AS attributes,
			`locations_types`.pr_type,
			`locations_types`.object_group,
			`locations`.comments
			FROM
			`locations_types`
			INNER JOIN locations ON (`locations_types`.id = locations.`type`)
			WHERE
			(locations.id = ?)", array($id));
			if($result->num_rows()){
				$output = $result->row_array();
			}
		}
		
		if ($type == "type") {
			$result = $this->db->query("SELECT 
			locations_types.object_group,
			locations_types.attributes,
			locations_types.pr_type
			FROM
			locations_types
			WHERE
			(locations_types.id = ?)
			LIMIT 1", array($id));
			if($result->num_rows()){
				$row = $result->row(0);
				$output['object_group']   = $row->object_group;
				$output['pr_type']        = $row->pr_type;
				$output['attributes']     = $row->attributes;
				$output['style_override'] = $row->attributes;
				$output['type']           = $id;
			}
		}

		$typelist = array();
		$result = $this->db->query("SELECT
		`locations_types`.id,
		`locations_types`.pr_type,
		`locations_types`.name,
		locations_types.attributes AS app
		FROM
		`locations_types`
		WHERE `locations_types`.`object_group` = ?
		AND `locations_types`.pl_num <> 0", array($output['object_group']));
		if($result->num_rows()){
			foreach($result->result() as $row){
				$selected = ($output['type'] == $row->id) ? ' selected="selected"' : "";
				$string   = '<option value="'.$row->id.'" ref="'.$row->pr_type.'" apply="'.$row->app.'"'.$selected.'>'.$row->name.'</option>';
				array_push($typelist, $string);
			}
		}
		$output['typelist'] = implode($typelist, "\n");

		$pagelist = array();
		$result = $this->db->query("SELECT 
		MAX(`properties_list`.page) as `maxpage`
		FROM
		`properties_list`
		WHERE `properties_list`.`object_group` = ?", array($output['object_group']));
		if($result->num_rows()){
			$row  = $result->row();
			$page = 1;
			while($page <= $row->maxpage){
				$button = ($page === 1)
					? '<button type="button" class="btn btn-info btn-small displayMain" title="Перейти к началу">'.$page.'</button>'
					: '<button type="button" class="btn btn-info btn-small displayPage" title="Перейти к странице '.$page.'" ref="'.implode(array($output['object_group'], $output['id'], $page), "/").'">'.$page.'</button>';
				array_push($pagelist, $button);
				$page++;
			}
		}
		$output['pagelist'] = implode($pagelist, "&nbsp;");
		$output['liblink']  = implode(array($output['object_group'], $output['type']), "/");
		//print_r($output);
		//exit;
		return $output;
	}

	private function get_bas_points_types(){
		$output = array();
		$result = $this->db->query("SELECT 
		locations_types.id,
		locations_types.name,
		CASE 
			WHEN locations_types.pr_type = 1 THEN 'Точка'
			WHEN locations_types.pr_type = 2 THEN 'Ломаная'
			WHEN locations_types.pr_type = 3 THEN 'Полигон'
			WHEN locations_types.pr_type = 4 THEN 'Круг'
			WHEN locations_types.pr_type = 5 THEN 'Прямоугольник'
		END AS `pr_type_l`,
		locations_types.pr_type
		FROM
		locations_types
		INNER JOIN `objects_groups` ON (locations_types.object_group = `objects_groups`.id)
		WHERE
		(locations_types.pl_num <> 0) AND
		`objects_groups`.`active`
		ORDER BY
		locations_types.object_group,
		locations_types.name");
		if($result->num_rows()){
			foreach($result->result() as $row){
				$note = (in_array($row->pr_type, array(4, 5))) ? '<i class="icon-remove" title="Конверсия невозможна. Отображается только контур."></i>' : ""; 
				$string = '<tbody id="tbody'.$row->id.'"><tr>
					<td><input type="checkbox" class="typechecker" id="n'.$row->id.'" value="'.$row->id.'"></td>
					<td><label for="n'.$row->id.'">'.$row->name.'</label></td>
					<td>'.$row->pr_type_l.'</td>
					<td>'.$note.'</td>
					<td><i class="icon-plus-sign typefetcher" ref="'.$row->id.'"></i></td>
				</tr></tbody>
				<tbody id="tbodyn'.$row->id.'"></tbody>';
				array_push($output,$string);
			}
		}
		return implode($output,"\n");
	}

	private function get_object_list_by_type(){
		$output = array();
		$result = $this->db->query("SELECT
		`locations`.id,
		`locations`.location_name
		FROM
		`locations`
		WHERE `locations`.`type` = ?", array($this->input->post("type")));
		if($result->num_rows()){
			foreach($result->result() as $row){
				$string = '<tr><td colspan="5" style="background-color: #f6fff6;padding-left:42px;"><label><input type="checkbox" style="margin-top:-4px;" class="selectedObjects" value="'.$row->id.'">'.$row->location_name.'</label></td></tr>';
				array_push($output, $string);
			}
		}
		return implode($output, "\n");
	}
	
	private function get_objects_by_type(){
		//$this->output->enable_profiler(TRUE);
		$output = array();
		$run    = 0;
		$points = "";
		$ids    = "";
		if($this->input->post("points") && sizeof($this->input->post("points"))){
			$points = 'AND (locations.`type` IN ('.implode($this->input->post("points"), ",").'))';
			$run++;
		}
		if($this->input->post("ids") && sizeof($this->input->post("ids"))){
			$ids = ((!$run) ? "AND" : "OR").' (locations.`id` IN ('.implode($this->input->post("ids"), ",").'))';
			$run++;
		}
		
		if(!$run){
			return "data = {  }";
		}

		$result = $this->db->query("SELECT
		locations.id,
		locations_types.pr_type,
		IF(LENGTH(locations.style_override) > 0, locations.style_override, locations_types.attributes) AS attributes,
		locations.coord_y,
		CONCAT_WS(' ', locations_types.name, locations.location_name) as loc_name
		FROM
		locations
		INNER JOIN locations_types ON (locations.`type` = locations_types.id)
		WHERE
		(locations.active) "
		.$points
		.$ids);
		if($result->num_rows()){
			foreach($result->result() as $row){
				$object = $row->id.": { coords: '".$row->coord_y."', description: '".$row->loc_name."', pr: ".$row->pr_type." , attributes: '".$row->attributes."' }";
				array_push($output, $object);
			}
		}
		return "data = { ".implode($output, ",")." }";
	}

	function get_assigned_properties($location_id){
		$assigned = array();
		if($location_id){
			$result = $this->db->query('SELECT
			`properties_assigned`.property_id,
			IF(`properties_list`.multiplier <> 1, ((`properties_assigned`.value % `properties_list`.divider) / `properties_list`.multiplier), `properties_assigned`.value) as value
			FROM
			`properties_assigned`
			INNER JOIN properties_list ON (`properties_assigned`.property_id = properties_list.id)
			WHERE
			`properties_assigned`.`location_id` = ?', array($location_id));
			if($result->num_rows()){
				foreach ($result->result() as $row){
					$assigned[$row->property_id]=$row->value;
				}
			}
		}
		return $assigned;
	}

	function show_form_content($object_group = 1, $location_id = 0, $page = 1, $columns = 2){
		//print $location_id;
		//$this->load->helper('array');
		$assigned = ($location_id) ? $this->get_assigned_properties($location_id) : array();
		$output   = array();
		$query    = $this->db->query('SELECT
		properties_list.id,
		properties_list.`row`,
		properties_list.element,
		properties_list.label,
		properties_list.selfname,
		properties_list.fieldtype,
		properties_list.parameters
		FROM
		properties_list
		WHERE
		properties_list.object_group = ?
		AND properties_list.active = 1
		AND properties_list.page = ?
		ORDER BY
		properties_list.`row`,
		properties_list.element,
		properties_list.cat,
		properties_list.selfname', array($object_group, $page));
		if ($query->num_rows() ){
			foreach ($query->result() as $row){
				if(!isset($output[$row->row]))							{ $output[$row->row] = array(); }
				if(!isset($output[$row->row]['label']))					{ $output[$row->row]['label'] = $row->label; }
				if(!isset($output[$row->row][$row->element]))			{ $output[$row->row][$row->element] = array(); }
				if(!isset($output[$row->row][$row->element][$row->id]))	{ $output[$row->row][$row->element][$row->id] = array(); }

				$output[$row->row][$row->element][$row->id]['name']       = $row->selfname;
				$output[$row->row][$row->element][$row->id]['fieldtype']  = $row->fieldtype;
				$output[$row->row][$row->element][$row->id]['parameters'] = $row->parameters;
			}
		}
		//print_r($assigned);
		$table = array();
		foreach($output as $key => $val){
			$element		= array();
			$elementarray	= array();
			foreach($val as $key2 => $val2){
				if($key2 !== "label"){
					$values			= array();// исключительно для случая, если элемент типа select
					$backcounter	= sizeof($val2);
					foreach($val2 as $obj => $val3){
						$value   = "";
						$options = array();
						if(isset($assigned[$obj])){
							$value = $assigned[$obj];
						}
						switch ($val3['fieldtype']){
							case 'text' :
								$sting      = '<input type="text" ref="'.$obj.'" id="param_'.$obj.'" '.$val3['parameters'].' value="'.$value.'">'.$val3['name'];
								array_push($element,$string);
							break;
							case 'textarea' :
								$string     = $val3['name'].'<textarea ref="'.$obj.'" id="param_'.$obj.'" '.$val3['parameters'].' rows="5" cols="20">'.(strlen($value) ? $value : '').'</textarea>';
								array_push($element,$string);
							break;
							case 'select' :
								$selected   = (isset($assigned[$obj])) ? 'selected="selected"' : "";
								array_push($values,'<option value="'.$obj.'" '.$selected.'>'.$val3['name'].'</option>');
								--$backcounter;
								if(!$backcounter){
									$string = '<select name="sel_'.$obj.'" id="sel_'.$obj.'">'.implode($values,"\n").'</select>';
									array_push($element,$string);
								}
							break;
							case 'checkbox' :
								$value      = (isset($assigned[$obj])) ? 'checked="checked"' : '';
								array_push($elementarray,'<label title="'.$val['label'].' - '.$val3['name'].'" for="p'.$obj.'"><input type="checkbox" id="p'.$obj.'" name="param[]" '.$value.' value="'.$obj.'">'.$val3['name'].'</label>');
								--$backcounter;
								if (!$backcounter){
									array_push($element, implode($elementarray,"\n"));
								}
							break;
						}
					}
				}
			}
			(!strlen($val['label'])) ? $val['label'] = "&nbsp;" : "";
			array_push($table, '<fieldset style="width:99%">
			<legend>
				'.$val['label'].
			'</legend>'.implode($element, "\n").
			'</fieldset>');
		}

		return implode($table,"\n");
	}

	private function geoeditor($object_group, $mode=1){
		//$this->output->enable_profiler(TRUE);
		$output = array();
		$data = array();
		$output['objects'] = $this->get_unbound_objects($object_group, $mode);
		$output['content'] = $this->load->view('editor/geosemantics',$data,true);
		$output['panel'] = $this->load->view('editor/btncontrol1', $data, true);
		$output['baspointstypes'] = $this->get_bas_points_types();
		return $output;
	}

	private function get_unbound_objects($object_group, $mode=1){
		//$this->output->enable_profiler(TRUE);
		$output	= array();
		$mode	= ($mode == 2) ? "AND LENGTH(`locations`.coord_y) = 0" : "AND LENGTH(`locations`.coord_y) > 0";
		$result	= $this->db->query("SELECT 
		`locations`.id,
		`locations`.location_name,
		IF(LENGTH(`locations`.style_override) > 0, `locations`.style_override, `locations_types`.attributes) AS attr,
		`locations`.coord_y,
		IF(LENGTH(`locations`.coord_y) > 0, 1, 0) AS `has_coord`,
		`locations_types`.name AS type_name,
		`locations_types`.pr_type
		FROM
		`locations`
		INNER JOIN `locations_types` ON (`locations`.`type` = `locations_types`.id)
		WHERE `locations_types`.`object_group` = ?
		".$mode."
		ORDER BY has_coord", array($object_group));
		if($result->num_rows()){
			foreach($result->result() as $row){
				$coords	= ($row->has_coord) ? "btn-success" : "btn-warning" ;
				$img	= $this->config->item('icons');
				$object	= '<button class="btn '.$coords.'" ref='.$row->id.' style="width:98%; margin-bottom:3px;">'.$img['system'][$row->pr_type].$row->type_name.' '.$row->location_name.'</button>';
				array_push($output, $object);
			}
		}
		return implode($output, "\n");
	}

	private function get_context(){
		/*
		возвращает js-объект с информацией о всех объектах, связанных в рамках карты-отображения с текущим
		по итогам обработки этих данных составляется описание связей в пределах карты отображения.

		
		SELECT 
		  `locations`.`type`
		FROM
		  `locations`
		WHERE
		`locations`.`id` = 511

		SELECT
		  `locations`.location_name,
		  `locations`.`type`,
		  `locations`.style_override,
		  `locations`.coord_y,
		  `locations`.coord_obj,
		  `locations`.contact_info,
		  `locations`.address
		FROM
		  `locations`
		WHERE `locations`.`type` IN(
				SELECT CONCAT_WS(',', `map_content`.`a_types`,`map_content`.`b_types`)
				FROM `map_content`
				WHERE `map_content`.`a_types` AND `map_content`.`b_types`
		  )
		*/
		
		//список типов объектов на выходе
		$output_types = array();
		$location = ($this->input->post("id")) ? $this->input->post("id") : 511;
		$result = $this->db->query("SELECT 
		`locations_types`.object_group as og,
		`locations`.`type`
		FROM
		`locations_types`
		INNER JOIN `locations` ON (`locations_types`.id = `locations`.`type`)
		WHERE `locations`.id = ?", array($location));
		if($result->num_rows()){
			$row2 = $result->row();
		}

		$result = $this->db->query("SELECT 
		CONCAT_WS(',', map_content.a_types, map_content.b_types) AS typelist,
		map_content.a_layers
		FROM
		map_content");
		if($result->num_rows()){
			foreach($result->result() as $row){
				$types = explode(",", $row->typelist);
				if(in_array($row2->type, $types)){
					$output_types = array_merge($output_types, $types);
				}
				if($row->a_layers == $row2->og ){
					$result = $this->db->query("SELECT 
					CONCAT_WS(',', `locations_types`.`id`) 
					FROM `locations_types` 
					WHERE 
					`locations_types`.`object_group`");
					if($result->num_rows()){
						foreach($result->result() as $row3){
							$types = explode(",", $row->typelist);
							$output_types = array_merge($output_types, $types);
						}
					}
				}
			}
		}
		$output = array();
		$result = $this->db->query("SELECT 
		locations.id,
		locations.location_name,
		locations.`type`,
		locations.coord_y,
		locations.coord_obj,
		locations.contact_info,
		locations.address,
		`locations_types`.name,
		`locations_types`.pr_type
		FROM
		locations
		INNER JOIN `locations_types` ON (locations.`type` = `locations_types`.id)
		WHERE
		locations.`type` IN (".implode($output_types, ",").")");
		if($result->num_rows()){
			foreach($result->result() as $obj){
				$coord = "[".$obj->coord_y."]";
				if($obj->pr_type == 2 || $obj->pr_type == 3){
					$coord = "'".$obj->coord_y."'";
				}
				$string = $obj->id.": { n: '".$obj->location_name."', typename: '".$obj->name."', type: ".$obj->type.", c: ".$coord.", co: [".$obj->coord_obj."], info: '".$obj->contact_info."', addr: '".$obj->address."'}";
				array_push($output, $string);
			}
		}
		print "{\n\t".implode($output, ",\n\t")."\n}";
	}
}
/* End of file editormodel.php */
/* Location: ./system/application/models/editormodel.php */