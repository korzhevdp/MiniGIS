<?php
class Editormodel extends CI_Model{
	function __construct(){
		parent::__construct();
	}
	
	private	function get_images($location){
		$output	= array();
		$result	= $this->db->query("SELECT
		`images`.`filename`,
		`images`.`hash`,
		`images`.`small`
		FROM
		`images`
		WHERE
		`images`.`location_id` = ?
		AND	`images`.`active`
		ORDER BY `images`.`order`, `images`.`id`", array($location));
		if($result->num_rows()){
			foreach($result->result() as $row){
				$sizes = explode(",", $row->small);
				$string	= '<li class="locationImg" ref='.$row->hash.'><img src="/uploads/small/'.$location."/".$row->filename.'" height="'.$sizes[0].'"	width="'.$sizes[0].'"><i class="icon-remove	icon-white"></i></li>';
				array_push($output,	$string);
			}
		}
		return implode($output,	"\n");
	}

	public function	starteditor($mode =	"edit",	$id	= 0) {
		//$this->output->enable_profiler(TRUE);
		if ($mode == "edit") {
			if ($id) {
				if(!$this->usefulmodel->check_owner($id)){
					$this->load->helper("url");
					redirect("admin/library");
				}
				$data =	$this->get_summary("location", $id);
				$output	= array(
					'images'			=> $this->get_images($id),
					'lid'				 =>	$id,
					'keywords'			  => '',
					'shedule'			 =>	$this->load->view("editor/shedule",	array(), true),
					'pr_type'			 =>	$data['pr_type'],
					'content'			 =>	$this->load->view('editor/summary',	$data, true),
					'panel'				   => $this->load->view('editor/altcontrols', $data, true),
					'baspointstypes'	=> $this->get_bas_points_types(),
					'menu'				  => $this->load->view('cache/menus/menu_'.$this->session->userdata('lang'), array(), true).$this->usefulmodel->admin_menu()
				);
			}
			$this->session->set_userdata('c_l',	$id);
		}
		if ($mode == "add")	{
			$data =	$this->get_summary("type", $id);
			$output	= array(
				'lid'				 =>	0,
				'keywords'			  => '',
				'shedule'			 =>	$this->load->view("editor/shedule",	array(), true),
				'pr_type'			 =>	$data['pr_type'],
				'content'			 =>	$this->load->view('editor/summary',	$data, true),
				'panel'				   => $this->load->view('editor/altcontrols', $data, true),
				'baspointstypes'	=> $this->get_bas_points_types(),
				'menu'				  => $this->load->view('admin/menu', array(), true)
			);
		}
		return $output;
	}
	
	public function	get_schedule() {
		$input = array();
		$schedule =	array();
		$result	= $this->db->query("SELECT
		DATE_FORMAT(`timers_week`.`start`, '%H:%i')	as start,
		DATE_FORMAT(`timers_week`.`end`, '%H:%i') as end,
		`timers_week`.`location_id`,
		`timers_week`.`day`
		FROM
		`timers_week`
		WHERE `timers_week`.`location_id` =	?
		ORDER BY `timers_week`.`day`, `timers_week`.`start`", array($this->input->post('lid')));
		if ($result->num_rows()) {
			foreach($result->result() as $row) {
				if (!isset($input[$row->day])) {
					$input[$row->day] =	array();
				}
				array_push($input[$row->day], "'".$row->start."'");
				array_push($input[$row->day], "'".$row->end."'");
			}
			foreach	($input	as $key=>$val) {
				array_push($schedule, "\t".$key." :	[ ".implode($val, ", ")." ]");
			}
		}
		print "shedule = {\n".implode($schedule, ",\n")."\n}";
	}

	private function fill_in_type_mode($type_id) {
		$output	= array();
		$result	= $this->db->query("SELECT
		locations_types.object_group,
		locations_types.attributes,
		locations_types.pr_type
		FROM
		locations_types
		WHERE
		(locations_types.id	= ?)
		LIMIT 1", array($type_id));
		if($result->num_rows()){
			$row = $result->row(0);
			$output	= array(
				'object_group'	 =>	$row->object_group,
				'pr_type'		 =>	$row->pr_type,
				'attributes'	 =>	$row->attributes,
				'style_override' =>	$row->attributes,
				'type'			 =>	$id
			);
		}
		return $output;
	}

	private function fill_in_location_mode($location_id) {
		$output	= array();
		$result	= $this->db->query("SELECT
		locations.id,
		locations.location_name,
		locations.address,
		locations.active,
		locations.`type`,
		locations.coord_y,
		locations.contact_info,
		`locations_types`.name AS `description`,
		IF(LENGTH(locations.style_override)	> 0, locations.style_override,`locations_types`.attributes)	AS attributes,
		`locations_types`.pr_type,
		`locations_types`.object_group,
		`locations`.comments
		FROM
		`locations_types`
		INNER JOIN locations ON	(`locations_types`.id =	locations.`type`)
		WHERE
		(locations.id =	?)", array($location_id));
		if($result->num_rows()){
			$output	= $result->row_array();
		}
		return $output;
	}

	private function get_object_types_of_group($group, $own_type) {
		$output	= array();
		$result	= $this->db->query("SELECT
		`locations_types`.id,
		`locations_types`.pr_type,
		`locations_types`.name,
		locations_types.attributes AS app
		FROM
		`locations_types`
		WHERE `locations_types`.`object_group` = ?
		AND	`locations_types`.pl_num <>	0",	array($group));
		if($result->num_rows()){
			foreach($result->result() as $row){
				$selected =	($own_type == $row->id)	? '	selected="selected"' : "";
				$string	  =	'<option value="'.$row->id.'" ref="'.$row->pr_type.'" apply="'.$row->app.'"'.$selected.'>'.$row->name.'</option>';
				array_push($output,	$string);
			}
		}
		return implode($output,	"\n");
	}

	private function generate_buttons_lists($input) {
		$output = array(
			'pagelist'		=> array(),
			'pagelist_alt'	=> array()
		);
		$result = $this->db->query("SELECT
		MAX(`properties_list`.page) as `maxpage`
		FROM
		`properties_list`
		WHERE `properties_list`.`object_group` = ?", array($input['object_group']));
		if($result->num_rows()){
			$row = $result->row(0);
			for($page = 1; $page <= $row->maxpage; $page++) {
				if ($page === 1) {
					$button	= '<button type="button" class="btn	btn-info btn-small displayMain"	title="Перейти к началу">'.$page.'</button>';
					$navtab	= '<li class="active displayMain"><a href="#YMapsID" data-toggle="tab">Карта</a></li>';
				} else {
					$button	= '<button type="button" class="btn	btn-info btn-small displayPage"	title="Перейти к странице '.$page.'" ref="'.implode(array($input['object_group'], $output['id'], $page), "/").'">'.$page.'</button>';
					$navtab	= '<li class="displayPage" ref="'.implode(array($input['object_group'], $input['id'],	$page),	"/").'"><a href="#propPage"	data-toggle="tab" >Страница	'.$page.'</a></li>';
				}
				array_push($output['pagelist'], $button);
				array_push($output['pagelist_alt'], $navtab);
			}
		}
		return $output;
	}

	private function get_summary($type = "type", $id = 0) {
		$output = array(
			'id' =>	0,
			'location_name'		=> 'Новое имя',
			'contact_info'		=> 'Контактная информация',
			'address'			=> 'Новый адрес',
			'active'			=> 0,
			'type'				=> 0,
			'typelist'			=> 0,
			'description'		=> 'Новое описание',
			'pr_type'			=> 1,
			'attributes'		=> "not	defined",
			'style_override'	=> "not	defined",
			'coord_y'			=> 0,
			'comments'			=> 0
		);
		if ($type === "type") {
			$output = $this->fill_in_type_mode($id);
		}
		if ($type === "location") {
			$output = $this->fill_in_location_mode($id);
		}
		$output['typelist']	= $this->get_object_types_of_group($output['object_group'],	$output['type']);
		$output['liblink']		= implode(array($output['object_group'], $output['type']), "/");
		$output = array_merge($output, $this->generate_buttons_lists($output));
		return $output;
	}

	public function	get_bas_points_types() {
		$output	= array();
		$result	= $this->db->query("SELECT 
		locations_types.id,
		locations_types.name,
		CASE 
			WHEN locations_types.pr_type = 1 THEN 'Точка'
			WHEN locations_types.pr_type = 2 THEN 'Ломаная'
			WHEN locations_types.pr_type = 3 THEN 'Полигон'
			WHEN locations_types.pr_type = 4 THEN 'Круг'
			WHEN locations_types.pr_type = 5 THEN 'Прямоугольник'
		END	AS `pr_type_l`,
		locations_types.pr_type
		FROM
		locations_types
		INNER JOIN `objects_groups`	ON (locations_types.object_group = `objects_groups`.id)
		WHERE
		(locations_types.pl_num	<> 0) AND
		`objects_groups`.`active`
		ORDER BY
		locations_types.object_group,
		locations_types.name");
		if($result->num_rows()){
			foreach($result->result() as $row){
				$note =	(in_array($row->pr_type, array(4, 5))) ? '<i class="icon-remove" title="Конверсия невозможна. Отображается только контур."></i>' : ""; 
				$string	= '<tbody id="tbody'.$row->id.'"><tr>
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

	public function	get_object_list_by_type() {
		$output	= array();
		$result	= $this->db->query("SELECT
		`locations`.id,
		`locations`.location_name
		FROM
		`locations`
		WHERE `locations`.`type` = ?", array($this->input->post("type")));
		if($result->num_rows()){
			foreach($result->result() as $row){
				$string	= '<tr><td colspan="5" style="background-color:	#f6fff6;padding-left:42px;"><label><input type="checkbox" style="margin-top:-4px;" class="selectedObjects" value="'.$row->id.'">'.$row->location_name.'</label></td></tr>';
				array_push($output,	$string);
			}
		}
		return implode($output,	"\n");
	}
	
	public function	get_objects_by_type() {
		//$this->output->enable_profiler(TRUE);
		$output	= array();
		$run	= 0;
		$points	= "";
		$ids	= "";
		if($this->input->post("points")	&& sizeof($this->input->post("points"))){
			$points	= 'AND (locations.`type` IN	('.implode($this->input->post("points"), ",").'))';
			$run++;
		}
		if($this->input->post("ids") &&	sizeof($this->input->post("ids"))){
			$ids = ((!$run)	? "AND"	: "OR").' (locations.`id` IN ('.implode($this->input->post("ids"), ",").'))';
			$run++;
		}
		
		if(!$run){
			return "data = {  }";
		}

		$result	= $this->db->query("SELECT
		locations.id,
		locations_types.pr_type,
		IF(LENGTH(locations.style_override)	> 0, locations.style_override, locations_types.attributes) AS attributes,
		locations.coord_y,
		CONCAT_WS('	', locations_types.name, locations.location_name) as loc_name
		FROM
		locations
		INNER JOIN locations_types ON (locations.`type`	= locations_types.id)
		WHERE
		(locations.active) "
		.$points
		.$ids);
		if($result->num_rows()){
			foreach($result->result() as $row){
				$object	= $row->id.": {	coords:	'".$row->coord_y."', description: '".$row->loc_name."',	pr:	".$row->pr_type." ,	attributes:	'".$row->attributes."' }";
				array_push($output,	$object);
			}
		}
		return "data = { ".implode($output,	",")." }";
	}

	private function get_assigned_properties($location_id) {
		$assigned =	array();
		if($location_id){
			$result	= $this->db->query("SELECT
			IF(properties_list.fieldtype = 'select', `properties_assigned`.value, `properties_assigned`.property_id) AS	property_id,
			IF(`properties_list`.coef <> 1,
				((`properties_assigned`.value %	`properties_list`.divider) / `properties_list`.multiplier),
				`properties_assigned`.value
			) AS value
			FROM
			`properties_assigned`
			INNER JOIN properties_list ON (`properties_assigned`.property_id = properties_list.id)
			WHERE
			`properties_assigned`.`location_id`	= ?", array($location_id));
			if($result->num_rows()){
				foreach	($result->result() as $row){
					$assigned[$row->property_id] = $row->value;
				}
			}
		}
		return $assigned;
	}

	function show_form_content($object_group = 1, $location_id = 0,	$page =	1, $columns	= 2) {
		//print	$location_id;
		//$this->load->helper('array');
		$assigned =	($location_id) ? $this->get_assigned_properties($location_id) :	array();
		$output	  =	array();
		$query	  =	$this->db->query('SELECT 
		properties_list.id,
		CONCAT(properties_list.page, properties_list.`row`, properties_list.element) AS marker,
		properties_list.label,
		properties_list.selfname,
		properties_list.fieldtype,
		properties_list.parameters,
		properties_list.linked
		FROM
		`properties_bindings`
		RIGHT OUTER	JOIN properties_list ON (`properties_bindings`.property_id = properties_list.id)
		WHERE
		`properties_bindings`.`groups` = ?
		AND	properties_list.active
		AND	properties_list.page = ?
		ORDER BY
		marker,
		properties_list.selfname', array($object_group,	$page));
		if ($query->num_rows() ){
			foreach	($query->result() as $row){
				if(!isset($output[$row->label])){ $output[$row->label] = array(); }
				$output[$row->label][$row->id] = array(
					'name'		 =>	$row->selfname,
					'fieldtype'	 =>	$row->fieldtype,
					'parameters' =>	$row->parameters,
					'linked'	 =>	$row->linked
				);
			}
		}
		//print_r($output);
		$table = $this->generate_form_content($output, $assigned);
		return implode($table,"\n");
	}

	private	function generate_form_content($input, $assigned) {
		$output	= array();
		foreach	($input	as $label => $controls)	{
			$element		= array();
			$elementarray	 = array();
			$values			   = array();//	исключительно для случая, если элемент типа	select
			$options		= array();
			$backcounter	= sizeof($controls);
			$linked			   = 0;
			foreach	($controls as $object => $data)	{
				$value	  =	"";
				$checked  =	"";
				$selected =	"";
				if(isset($assigned[$object])){
					$value	  =	$assigned[$object];
					$checked  =	' checked="checked"';
					$selected =	' selected="selected"';
				}
				if($data['linked'] != 0){
					$linked	= $data['linked'];
				}
				switch ($data['fieldtype']){
					case 'text':
						$string		= '<div><div class="input-prepend"><label class="add-on" for="param_'.$object.'">'.$data['name'].'</label><input type="text" id="ogp4" ref="'.$object.'" id="param_'.$object.'"	'.$data['parameters'].'	value="'.$value.'"></div></div>';
						array_push($element, $string);
					break;
					case 'textarea':
						$string		= $data['name'].'<textarea ref="'.$object.'" id="param_'.$object.'"	'.$data['parameters'].'	rows="5" cols="20">'.(strlen($value) ? $value :	'').'</textarea>';
						array_push($element, $string);
					break;
					case 'select':
						array_push($values,	'<option value="'.$object.'"'.$selected.'>'.$data['name'].'</option>');
						--$backcounter;
						if(!$backcounter){
							array_unshift($values, '<option	value="0"> - - - </option>');
							$string	= '<select ref="'.$object.'" name="sel_'.$object.'"	id="sel_'.$object.'">'.implode($values,"\n").'</select>';
							array_push($element, $string);
						}
					break;
					case 'checkbox':
						$string	= '<label title="'.$label.'	- '.$data['name'].'" for="p'.$object.'"><input type="checkbox" id="p'.$object.'" name="param[]"	'.$checked.' value="'.$object.'">'.$data['name'].'</label>';
						array_push($elementarray, $string);
						--$backcounter;
						if (!$backcounter){
							array_push($element, implode($elementarray,"\n"));
						}
					break;
				}
			}
			$checkLinks	= ($linked)	? '<button type="button" class="btn	btn-small map_calc pull-right" title="Запрос расчёта локации">Расчёт зависимостей</button>'	: "";
			array_push($output,	'<fieldset>
			<legend>
				'.$label.$checkLinks.
			'</legend>'.implode($element, "\n").
			'</fieldset>');
		}
		return $output;
	}

	private	function geoeditor($object_group, $mode	= 1) {
		$data =	array();
		$output	= array(
			'objects'		 =>	$this->get_unbound_objects($object_group, $mode),
			'content'		 =>	$this->load->view('editor/geosemantics', $data,	true),
			'panel'			 =>	$this->load->view('editor/controls', $data,	true),
			'baspointstypes' =>	$this->get_bas_points_types()
		);
		return $output;
	}

	private	function get_unbound_objects($object_group,	$mode =	1) {
		//$this->output->enable_profiler(TRUE);
		$output	= array();
		$mode	= ($mode ==	2) ? "NOT" : "";
		$result	= $this->db->query("SELECT
		`locations`.id,
		`locations`.location_name,
		IF(LENGTH(`locations`.style_override), `locations`.style_override, `locations_types`.attributes) AS	attr,
		`locations`.coord_y,
		IF(LENGTH(`locations`.coord_y),	1, 0) AS `has_coord`,
		`locations_types`.name AS type_name,
		`locations_types`.pr_type
		FROM
		`locations`
		INNER JOIN `locations_types` ON	(`locations`.`type`	= `locations_types`.id)
		WHERE `locations_types`.`object_group` = ?
		AND	".$mode." LENGTH(`locations`.coord_y)
		ORDER BY has_coord", array($object_group));
		if($result->num_rows()){
			foreach($result->result() as $row){
				$coords	= ($row->has_coord)	? "btn-success"	: "btn-warning"	;
				$img	= $this->config->item('icons');
				$object	= '<button class="btn '.$coords.'" ref='.$row->id.'	style="width:98%; margin-bottom:3px;">'.$img['system'][$row->pr_type].$row->type_name.'	'.$row->location_name.'</button>';
				array_push($output,	$object);
			}
		}
		return implode($output,	"\n");
	}

	public function save_shedule(){
		$output	= array();
		foreach	($this->input->post('shedule', true) as	$key =>	$val) {
			if ($this->input->post("h24")) {
				$val = array( '00:00:00','12:00:00','12:00:00','23:59:59' );
			}
		   $string = "('".$this->db->escape_str($val[0])."', '".$this->db->escape_str($val[1])."', '".$this->db->escape_str($this->input->post("lid", true))."', ".$this->db->escape_str($key)."),\n('".$this->db->escape_str($val[2])."', '".$this->db->escape_str($val[3])."', '".$this->db->escape_str($this->input->post("lid",	true))."', ".$this->db->escape_str($key).")";
			array_push($output,	$string);
		}
		//print	implode($output, ",\n");
		$this->db->query("DELETE FROM timers_week WHERE	timers_week.location_id	= ?", array($this->input->post("lid", true)));
		$result	= $this->db->query("INSERT INTO
		`timers_week`(
			`timers_week`.`start`,
			`timers_week`.`end`,
			`timers_week`.`location_id`,
			`timers_week`.`day`
		)
		VALUES ".implode($output, ",\n"));
	}
}
/* End of file editormodel.php */
/* Location: ./system/application/models/editormodel.php */