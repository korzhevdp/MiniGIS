<?php
class Adminmodel extends CI_Model{
	function __construct(){
		parent::__construct();
		//$this->output->enable_profiler(TRUE);
	}
	/* Library set */


	private function get_type_name($loc_type) {
		$output = "Тип не определён";
		$result = $this->db->query("SELECT
		`locations_types`.name AS `type_name`
		FROM
		`locations_types`,
		`objects_groups`
		WHERE `locations_types`.`id` = ?", array($loc_type));
		if($result->num_rows()){
			$row    = $result->row();
			$output = $row->type_name;
		}
		return $output;
	}

	private function get_group_name($obj_group){
		$output = "Нет названия группы";
		$result = $this->db->query("SELECT 
		`objects_groups`.name
		FROM
		`objects_groups`
		WHERE
		`objects_groups`.id = ?", array($obj_group));
		if($result->num_rows()){
			$row    = $result->row();
			$output = $row->name;
		}
		return $output;
	}

	private function get_library_group_list($obj_group, $controller){
		$output = array();
		$result = $this->db->query('SELECT 
		`objects_groups`.name,
		`objects_groups`.id
		FROM
		`objects_groups`
		WHERE `objects_groups`.`active`
		AND `objects_groups`.`id` IN ('.$this->session->userdata('access').')
		ORDER BY `objects_groups`.name ASC');
		if($result->num_rows()){
			foreach ($result->result_array() as $row){
				$row['title']     = "";
				$row['obj_group'] = $obj_group;
				$row['img']       = '<img src="'.$this->config->item("api").'/images/folder.png" alt="">';
				$row['link']      = '/'.$controller.'/library/'.$row['id'];
				array_push($output, $this->load->view("admin/libraryitem", $row, true));
			}
		}
		return $output;
	}

	private function get_library_type_list($obj_group, $controller){
		$output = array();
		$result = $this->db->query("SELECT 
		locations_types.id,
		locations_types.name AS `title`,
		IF(LENGTH(locations_types.name) > 49, CONCAT(LEFT(locations_types.name, 46) ,'...'), locations_types.name) AS name
		FROM
		locations_types
		WHERE
		`locations_types`.`pl_num` 
		AND `locations_types`.`object_group` = ?
		ORDER BY title", array($obj_group));
		if($result->num_rows()){
			foreach ($result->result_array() as $row){
				$row['obj_group'] = $obj_group;
				$row['img']  = '<img src="'.$this->config->item("api").'/images/folder.png" alt="">';
				$row['link'] = '/'.$controller.'/library/'.$obj_group.'/'.$row['id'];
				array_push($output, $this->load->view("admin/libraryitem", $row, true));
			}
		}
		return $output;
	}

	private function get_library_locations_list_by_type($loc_type){
		$output = array();
		$view_user_locations = "AND `locations`.owner = ?";
		if($this->config->item('admin_can_edit_user_locations') === true){
			if($this->session->userdata('admin')){
				$view_user_locations = "";
			}
		}
		$result = $this->db->query("SELECT 
		IF(LENGTH(`locations`.location_name) > 49, CONCAT(LEFT(`locations`.location_name, 46), '...'),`locations`.location_name) AS name,
		`locations`.location_name AS title,
		`locations`.id
		FROM
		`locations`
		WHERE
		`locations`.`type`    = ?"
		.$view_user_locations."
		ORDER BY title", array(
			$loc_type,
			$this->session->userdata("user_id"))
		);
		if($result->num_rows()){
			foreach ($result->result_array() as $row){
				$row['img']  = '<img src="'.$this->config->item("api").'/images/location_pin.png" alt="">';
				$row['link'] = '/editor/edit/'.$row['id'];
				array_push($output, $this->load->view("admin/libraryitem", $row, true));
			}
		}
		$row = array(
			'img'   => '<img src="'.$this->config->item("api").'/images/location_pin.png" alt="">',
			'name'  => 'Добавить объект',
			'link'  => '/editor/add/'.$loc_type,
			'title' => "Добавить новый объект этого класса"
		);
		array_push($output, $this->load->view("admin/libraryitem", $row, true));
		return $output;
	}

	public function get_composite_indexes($obj_group, $loc_type, $param = 1, $page = 1){
		$values         = $this->adminmodel->show_semantics_values($obj_group, $loc_type, $param);
		$values['list'] = $this->adminmodel->show_semantics($obj_group, $loc_type);
		$output = array(
			'content'  => $this->adminmodel->get_full_index($obj_group, $loc_type),
			'content2' => $this->load->view('admin/prop_control_table', $values, true),
			'page'     => $page
		);
		return $this->load->view("admin/library2", $output, true);
	}
	
	public function get_full_index($obj_group = 0, $loc_type = 0, $page = 1){
		$controller = ($this->session->userdata('admin')) ? "admin" : "user";
		$output = array();
		$out    = array(
			'loc_type'	 => $loc_type,
			'obj_group'	 => $obj_group,
			'controller' => $controller
		);

		$out['type_name'] = $this->get_type_name($loc_type);
		$out['name']      = $this->get_group_name($obj_group);
		
		if(!$obj_group) {
			$output = $this->get_library_group_list($obj_group, $controller);
		} else {
			if(!$loc_type){
				$output = $this->get_library_type_list($obj_group, $controller);
			} else {
				$output = $this->get_library_locations_list_by_type($loc_type);
			}
		}
		$out['library'] = implode($output, "\n");
		return $this->load->view("admin/library", $out, true);
	}

	#######################################################################
	### управление списками параметров VERIFIED
	#######################################################################
	public function show_semantics($object_group = 0, $type_id = 0) {
		$table  = array();
		if ($object_group) {
			$result = $this->db->query("SELECT
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
		} else {
			$result = $this->db->query("SELECT
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
		if ($result->num_rows()) {
			//array_push($table,'<ul class="span12 row-fluid" style="list-style-type: none; margin-left:0px;">');
			foreach ($result->result_array() as $row){
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
	
	private function get_bound_groups($property){
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

	public function save_semantics() {
		//$this->output->enable_profiler(TRUE);
		//return false;
		$mode     = $this->input->post('mode');
		$group    = $this->input->post('object_group'); // для редиректа :)
		$property = $this->input->post('property');
		if($mode == "save"){
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
				$property
			));
		}
		if($mode == "new"){
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
			) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )",
			array(
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
				$this->input->post('divider')
			));
		}
		
		$groups   = $this->input->post('group');
		$ingroups = array();
		foreach($groups as $val){
			$string = '('.$property.', '.$val.', 1)';
			array_push($ingroups, $string);
		}
		$this->db->query("DELETE FROM `properties_bindings` WHERE `properties_bindings`.property_id = ?", array($property));
		$this->db->query("INSERT INTO `properties_bindings` (
			`properties_bindings`.property_id,
			`properties_bindings`.groups,
			`properties_bindings`.searchable
		) VALUES ".implode($ingroups, ",\n"));

		if($this->input->post('linked')) {
			$this->db->query("DELETE
			FROM 
			`properties_assigned`
			WHERE 
			`properties_assigned`.location_id = ?
			AND `properties_assigned`.property_id = ?", array(
				$this->input->post('linked'),
				$property
			));
			$this->db->query("INSERT INTO 
			`properties_assigned` (
				`properties_assigned`.location_id,
				`properties_assigned`.property_id,
				`properties_assigned`.value
			) VALUES (?, ?, 1)", array(
				$this->input->post('linked'),
				$property
			));
		}
		redirect('admin/library/'.$group."/0/".$property."/2");
	}
	#######################################################################
	### объекты ГИС VERIFIED
	#######################################################################
	function gis_objects_show($obj = 0) {
		//Выбираем объект
		$object = array(
			'id'			=> 0,
			'has_child'		=> 0,
			'name'			=> '',
			'attributes'	=> '',
			'table2'		=> '',
			'object_group'	=> 0,
			'obj'			=> 0,
			'pr_type'		=> 0,
			'pl_num'		=> 0
		);
		if($obj){
			$result = $this->db->query("SELECT 
			locations_types.id,
			locations_types.has_child,
			locations_types.name,
			locations_types.attributes,
			locations_types.object_group,
			locations_types.object_group AS `obj`,
			locations_types.pl_num,
			locations_types.pr_type
			FROM
			locations_types
			WHERE 
			locations_types.id = ?", array($obj));
			if($result->num_rows()){
				$object = $result->row_array();
			}
		}
		// группы объектов для формы редактирования
		$object['obj_group'] = array('<option value="0">Выберите группу объектов</option>');
		$result = $this->db->query("SELECT 
		`objects_groups`.id,
		`objects_groups`.name
		FROM
		`objects_groups`
		WHERE
		`objects_groups`.`active`");
		if($result->num_rows()){
			foreach($result->result() as $row){
				array_push($object['obj_group'], '<option value="'.$row->id.'" '.(($row->id == $object['object_group']) ? 'selected="selected"' : '').'>'.$row->name.'</option>');
			}
		}
		$object['obj_group'] = implode($object['obj_group'],"\n");
		//для таблицы свойств

		$result=$this->db->query("SELECT 
		locations_types.id,
		locations_types.has_child,
		locations_types.name,
		locations_types.attributes,
		locations_types.object_group,
		locations_types.pl_num,
		locations_types.pr_type,
		objects_groups.name AS object_group_name
		FROM
		objects_groups
		RIGHT OUTER JOIN locations_types ON (objects_groups.id = locations_types.object_group)
		ORDER BY
		locations_types.object_group,
		locations_types.name");
		$table2 = array();
		if($result->num_rows()){
			foreach ($result->result_array() as $row){
				$row['pic']		 = "marker.png";
				$row['pictitle'] = "точечная метка";
				switch($row['pr_type']){
					case 2:
						$row['pic']		 = 'layer-shape-polyline.png';
						$row['pictitle'] = "Ломаная";
					break;
					case 3:
						$row['pic']		 = 'layer-shape-polygon.png';
						$row['pictitle'] = "Полигон";
					break;
					case 4:
						$row['pic']		 = 'layer-shape-ellipse.png';
						$row['pictitle'] = "Круг";
					break;
					case 5:
						$row['pic']		 = 'layer-select.png';
						$row['pictitle'] = "Прямоугольник";
					break;
				}
				array_push($table2, $this->load->view('admin/parameterline2', $row, true));
			}
			$object['table2'] = implode($table2, "\n");
		}else{
			$out = "<tr><td colspan=5>Справочник объектов пуст.</td></tr>";
		}
		return $this->load->view('admin/object_types_control_table', $object, true);
	}

	function gis_save() {
		//print $this->input->post('obj');
		$hc = ($this->input->post('has_child')) ? 1 : 0;
		if(!$this->input->post('obj')){
			$this->db->query("INSERT INTO
			`properties_list` (
				`properties_list`.`row`,
				`properties_list`.element,
				`properties_list`.label,
				`properties_list`.algoritm,
				`properties_list`.selfname,
				`properties_list`.page,
				`properties_list`.property_group,
				`properties_list`.fieldtype,
				`properties_list`.cat,
				`properties_list`.object_group,
				`properties_list`.parameters,
				`properties_list`.active,
				`properties_list`.searchable,
				`properties_list`.coef
			) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", array(
				1,
				1,
				"Укажите метку",
				'ud',
				$this->input->post('name', true),
				1,
				"",
				"checkbox",
				"",
				$this->input->post('obj_group', true),
				"",
				0,
				1,
				1
			));
			$this->db->query("INSERT INTO
			locations_types(
				locations_types.has_child,
				locations_types.name,
				locations_types.attributes,
				locations_types.object_group,
				locations_types.pl_num,
				locations_types.pr_type
			) VALUES ( ?, ?, ?, ?, ?, ? )", array(
				$hc,
				$this->input->post('name',       true),
				$this->input->post('attributes', true),
				$this->input->post('obj_group',  true),
				$this->db->insert_id(),
				$this->input->post('pr_type',    true)
			));
			$text = "Администратором ".$this->session->userdata("user_name")." создан тип объекта #".$this->db->insert_id()." - ".$this->input->post('name');
			$this->usefulmodel->insert_audit($text);
		}else{
			$this->db->query("UPDATE
			locations_types
			SET
			locations_types.has_child = ?,
			locations_types.name = ?,
			locations_types.attributes = ?,
			locations_types.object_group = ?,
			locations_types.pl_num = ?,
			locations_types.pr_type = ?
			WHERE
			locations_types.id = ?", array(
				$hc,
				$this->input->post('name',       true),
				$this->input->post('attributes', true),
				$this->input->post('obj_group',  true),
				$this->input->post('pl_num',     true),
				$this->input->post('pr_type',    true),
				$this->input->post('obj',        true)
			));

			$this->db->query("UPDATE
			properties_list
			SET
			properties_list.selfname = ?,
			properties_list.object_group = ?
			WHERE
			properties_list.id = ?", array(
				$this->input->post('name',      true),
				$this->input->post('obj_group', true),
				$this->input->post('pl_num',    true)
			));
			$text = "Администратором ".$this->session->userdata("user_name")." сохранены параметры типа объекта #".$this->input->post('obj')." - ".$this->input->post('name');
			$this->usefulmodel->insert_audit($text);
		}
	}

#
######################### BEGIN usermanager section ############################
#
	function users_show($id = 0){
		$access = "";
		$output = array(
			'admin'  => '',
			'valid'  => '',
			'active' => '',
			'rating' => '',
			'name'   => '',
			'id'     => $id
		);
		$result = $this->db->query("SELECT 
		`users_admins`.id,
		`users_admins`.class_id,
		`users_admins`.nick,
		DATE_FORMAT(`users_admins`.registration_date, '%d.%m.%Y') AS registration_date,
		CONCAT_WS(' ',`users_admins`.name_f,`users_admins`.name_i,`users_admins`.name_o) AS `fio`,
		SUBSTRING(`users_admins`.`info`, 1, 400) as info,
		`users_admins`.active,
		`users_admins`.rating,
		`users_admins`.valid,
		`users_admins`.access
		FROM
		`users_admins`
		ORDER BY `users_admins`.`class_id` ASC, fio ASC");
		$users  = array();
		if($result->num_rows()){
			foreach($result->result() as $row){
				$fio = (strlen($row->fio)) ? $row->fio : '<em class="muted">ФИО не указано</em>';
				if ($row->id == $id){
					$access = $row->access;
					$output['admin']  = (($row->class_id === "1") ? ' checked="checked"' : '');
					$output['valid']  = (($row->valid) ? ' checked="checked"' : '');
					$output['active'] = (($row->active) ? ' checked="checked"' : '');
					$output['rating'] = $row->rating;
					$output['name']   = $row->nick."&nbsp;&nbsp;&nbsp;&nbsp;<small>".$row->fio.",&nbsp;".$row->info."</small>";
					$output['id']     = $row->id;
				}
				$string = '<tr>
					<td>'.$row->nick.'</td>
					<td><small>'.$fio.'<br>'.$row->info.'</small></td>
					<td>'.$row->rating.'</td>
					<td>'.(($row->class_id === "1") ? 'Да' : 'Нет').'</td>
					<td>'.(($row->active)   ? 'Да' : 'Нет').'</td>
					<td>'.(($row->valid)    ? 'Да' : 'Нет').'</td>
					<td><a href="/admin/usermanager/'.$row->id.'" class="btn btn-primary btn-mini">Редактировать</span></td>
				</tr>';
				array_push($users, $string);
			}
		}
		$layers = array();
		$result = $this->db->query('SELECT 
		`objects_groups`.name,
		`objects_groups`.id,'.
		(strlen($access) 
			? 'IF(`objects_groups`.`id` IN('.$access.'), 1, 0) AS granted'
			: '0 AS granted'
		).
		' FROM
		`objects_groups`
		WHERE `objects_groups`.`active`');
		if($result->num_rows()){
			foreach($result->result() as $row){
				$checked = ($row->granted == "1") ? ' checked="checked"' : "";
				$string = '<li><label class="checkbox"><input type="checkbox" name="groups[]" value="'.$row->id.'"'.$checked.'>'.$row->name.'</label></li>';
				array_push($layers, $string);
			}
		}
		$output['table']  = implode($users,  "\n");
		$output['layers'] = implode($layers, "\n");
		return $this->load->view("admin/usermanager", $output, true);
	}

	function users_save($id){
		//$this->output->enable_profiler(TRUE);
		//return false;
		$admin = ($this->input->post('admin') == "1") ? 1 : 2;
		$result = $this->db->query("UPDATE users_admins 
		SET
		users_admins.active   = ?,
		users_admins.valid    = ?,
		users_admins.rating   = ?,
		users_admins.access   = ?,
		users_admins.class_id = ?
		WHERE
		users_admins.id = ?", array(
			$this->input->post('active', true),
			$this->input->post('valid' , true),
			$this->input->post('rating', true),
			implode($this->input->post('groups', true), ", "),
			$admin,
			$this->input->post('id')
		));
		$text = "Администратором ".$this->session->userdata("user_name")." изменены характеристики пользователя #".$this->input->post('id').": active: ".$this->input->post('active', true).", valid: ".$this->input->post('valid' , true).", rating: ".$this->input->post('rating' , true).", class: ".$admin.", access: ".implode($this->input->post('groups', true), ", ");
		$this->usefulmodel->insert_audit($text);
	}
#
######################### END usermanager section ############################
#
#
######################### BEGIN groupmanager section ############################
#
	function groups_show($group = 0){
		$groups = array();
		$output = array(
			'coord'  => $this->session->userdata("map_center"),
			'zoom'   => $this->session->userdata("map_zoom"),
			'icon'   => '',
			'name'   => '',
			'id'     => 0,
			'active' => '<input type="checkbox" value="1" name="active">'
		);

		$result = $this->db->query("SELECT 
		`objects_groups`.id,
		`objects_groups`.name,
		`objects_groups`.active,
		`objects_groups`.icon,
		`objects_groups`.refcoord,
		`objects_groups`.refzoom
		FROM
		`objects_groups`
		ORDER BY `objects_groups`.active DESC, `objects_groups`.name ASC");
		if($result->num_rows()){
			foreach($result->result() as $row){
				if($row->active) {
					$checked  = 'checked="checked"';
					$checkbox = "Да";
					$class    = ' class="success"';
				} else {
					$checked  = '' ;
					$checkbox = "Нет";
					$class    = '';
				}
				$string = '<tr'.$class.'>
					<td>'.$row->name.'</td>
					<td>'.$row->icon.'</td>
					<td>'.$row->refcoord.'</td>
					<td>'.$row->refzoom.'</td>
					<td>'.$checkbox.'</td>
					<td><a href="/admin/groupmanager/'.$row->id.'" class="btn btn-small btn-primary">Редактировать</a></td>
				</tr>';
				if($row->id == $group) {
					$output = array(
						'coord'  => (strlen($row->refcoord) ? $row->refcoord : $this->session->userdata("map_center")),
						'zoom'   => (strlen($row->refzoom)  ? $row->refzoom  : $this->session->userdata("map_zoom")),
						'icon'   => $row->icon,
						'name'   => $row->name,
						'id'     => $row->id,
						'active' => '<input type="checkbox" value="1" name="active" '.$checked.'>'
					);
				}
				array_push($groups, $string);
			}
		}
		$output['table'] = implode($groups, "\n");
		return $this->load->view("admin/groupmanager", $output, true);
	}

	function group_save(){
		//$this->output->enable_profiler(TRUE);
		//return false;
		$id = $this->input->post('id', true);
		if($this->input->post('mode', true) === 'save') {
			$result = $this->db->query("UPDATE
				`objects_groups`
				SET
				`objects_groups`.refzoom  = ?,
				`objects_groups`.refcoord = ?,
				`objects_groups`.icon     = ?,
				`objects_groups`.name     = ?,
				`objects_groups`.active   = ?
				WHERE
				`objects_groups`.id = ?", array(
				$this->input->post('map_zoom', true),
				$this->input->post('map_center' , true),
				$this->input->post('icon', true),
				$this->input->post('name'  , true),
				$this->input->post('active', true),
				$this->input->post('id', true),
			));
			$text = "Администратором ".$this->session->userdata("user_name")." обновлены параметры группы #".$this->input->post('id', true)." - ".$this->input->post('name', true);
			$this->usefulmodel->insert_audit($text);
		}
		if($this->input->post('mode', true) === 'add') {
			$result = $this->db->query("INSERT INTO
			`objects_groups`(
			`objects_groups`.refzoom,
			`objects_groups`.refcoord,
			`objects_groups`.icon,
			`objects_groups`.name,
			`objects_groups`.active)
			VALUES( ?, ?, ?, ?, ?)", array(
				$this->input->post('map_zoom', true),
				$this->input->post('map_center' , true),
				$this->input->post('icon', true),
				$this->input->post('name'  , true),
				$this->input->post('active', true)
			));
			$id = $this->db->insert_id();
			$text = "Администратором ".$this->session->userdata("user_name")." создана группа #".$id." - ".$this->input->post('name', true);
			$this->usefulmodel->insert_audit($text);
		}
		return $id;
	}
#
######################### END groupmanager section ############################
#
#
######################### end of comments section ############################
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
######################### end of map content section ############################
#
######################### start of menu content section ############################
	public function translations($mode = "groups"){
		//$this->output->enable_profiler(TRUE);
		$output = array();

		if ($mode === "groups"){
			$result = $this->db->query("SELECT
			objects_groups.id,
			objects_groups.name
			FROM
			objects_groups
			ORDER BY objects_groups.name");
		}
		if ($mode === "categories"){
			$result = $this->db->query("SELECT 
			`locations_types`.name,
			`locations_types`.id
			FROM
			`locations_types`
			WHERE `locations_types`.pl_num > 0");
		}
		if ($mode === "properties"){
			$result = $this->db->query("SELECT 
			`properties_list`.selfname AS name,
			`properties_list`.id
			FROM
			`properties_list`
			WHERE LENGTH(`properties_list`.`selfname`)");
		}
		if ($mode === "labels"){
			$result = $this->db->query("SELECT DISTINCT
			`properties_list`.label AS id,
			`properties_list`.label AS name
			FROM
			`properties_list`
			ORDER BY `properties_list`.label");
		}
		if ($mode === "articles"){
			$result = $result=$this->db->query("SELECT 
			`sheets`.`id`,
			`sheets`.`header` AS name
			FROM
			`sheets`
			ORDER BY name ASC");
		}
		if ($mode === "maps"){
			$result = $this->db->query("SELECT
			`map_content`.name,
			`map_content`.id
			FROM
			`map_content`
			ORDER BY `map_content`.name ASC");
		}
		$groups = $this->config->item($mode);
		$table  = $this->get_translation_table($result, $groups, $mode);
		$output['table'] = implode($table, "\n");
		$output['mode']  = $mode;
		return $this->load->view('admin/translations', $output, true);
	}

	private function get_translation_table($result, $groups, $mode){
		$table  = array();
		$string = array();
		foreach($this->config->item("lang") as $key=>$val) {
			$cell = '<th><img src="'.$this->config->item("api").'/images/flag_'.$key.'.png" alt="">'.$val.'</th>';
			array_push($string, $cell);
		}
		array_push($table, "<tr>".implode($string, "\n")."</tr>");
		if($result->num_rows()){
			foreach($result->result() as $row){
				$string = array();
				$id = sizeof($table);
				foreach($this->config->item("lang") as $key=>$val) {
					$readonly = "";
					if($key === $this->config->item("native_lang")){
						$value = $row->name;
						$readonly = ' readonly="readonly"';
					} else {
						$value = (isset($groups[$row->id][$key])) ? $groups[$row->id][$key] : '';
					}
					if($mode === 'labels'){
						$cell = "\t".'<td><input type="text" name="'.$mode.'['.$id.']['.$key.']" class="translation" ref="'.$row->id.'" lang="'.$key.'" value="'.$value.'" placeholder="Нет перевода"'.$readonly.'></td>';
					}
					else{
						$cell = "\t".'<td><input type="text" name="'.$mode.'['.$row->id.']['.$key.']" class="translation" ref="'.$row->id.'" lang="'.$key.'" value="'.$value.'" placeholder="Нет перевода"'.$readonly.'></td>';
					}
					array_push($string, $cell);

				}
				if($mode === 'labels'){
					$cell = "\t".'<td class="hide"><input type="hidden" name="'.$mode.'['.$id.'][original]" class="translation" ref="'.$row->id.'" lang="'.$key.'" value="'.$row->name.'" placeholder="Нет перевода"></td>';
					array_push($string, $cell);
				}

				array_push($table, "<tr>\n".implode($string, "\n")."\n</tr>");
			}
		}
		return $table;
	}

	public function trans_save(){
		//$this->output->enable_profiler(TRUE);
		$output = array();
		if($this->input->post("type") === 'labels'){
			$filename = 'application/config/translations_l.php';
			if(sizeof($this->input->post($this->input->post("type")))){

				foreach($this->input->post($this->input->post("type")) as $key=>$val) {
					$orig = $val['original'];
					$input = array();
					foreach($val as $lang=>$word){
						$string = "'".addslashes($lang)."' => '".addslashes($word)."'";
						array_push($input, $string);
					}
					$string = "\t'".$orig."' => array( ".implode($input, ",")." )";
					array_push($output, $string);
				}
			}
		} else {
			if($this->input->post("type") === 'groups'){
				$filename = 'application/config/translations_g.php';
			}
			if($this->input->post("type") === 'categories'){
				$filename = 'application/config/translations_c.php';
			}
			if($this->input->post("type") === 'properties'){
				$filename = 'application/config/translations_p.php';
			}
			if($this->input->post("type") === 'articles'){
				$filename = 'application/config/translations_a.php';
			}
			if($this->input->post("type") === 'maps'){
				$filename = 'application/config/translations_m.php';
			}
			if(sizeof($this->input->post($this->input->post("type")))){
				foreach($this->input->post($this->input->post("type")) as $key=>$val) {
					$input = array();
					foreach($val as $lang=>$word){
						$string = "'".addslashes(trim($lang))."' => '".addslashes(trim($word))."'";
						array_push($input, $string);
					}
					$string = "\t".$key." => array( ".implode($input, ",")." )";
					array_push($output, $string);
				}
			}
		}

		$config = $this->load->view('admin/translations_template', array('group' => $this->input->post("type"), 'content' => implode($output, ",\n")), true);
		$this->load->helper('file');
		write_file($filename, "<".$config, "w");
	}
}
/* End of file adminmodel.php */
/* Location: ./application/controllers/adminmodel.php */