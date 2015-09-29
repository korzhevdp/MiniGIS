<?php
class Adminmodel extends CI_Model{
	function __construct(){
		parent::__construct();
	}

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
}
/* End of file adminmodel.php */
/* Location: ./application/models/adminmodel.php */