<?php
class Adminmodel extends CI_Model{
	function __construct(){
		parent::__construct();
	}

	function get_full_index($obj_group = 0, $loc_type = 0){
		$output = array();
		$out    = array(
			'loc_type'	=> $loc_type,
			'obj_group'	=> $obj_group
		);

		$result=$this->db->query("SELECT 
		`locations_types`.name AS `type_name`
		FROM
		`locations_types`,
		`objects_groups`
		WHERE `locations_types`.`id` = ?", array($loc_type));
		if($result->num_rows()){
			$row = $result->row();
			$out['type_name'] = $row->type_name;
		}

		$result=$this->db->query("SELECT 
		`objects_groups`.name
		FROM
		`objects_groups`
		WHERE
		`objects_groups`.id = ?", array($obj_group));
		if($result->num_rows()){
			$row = $result->row();
			$out['name'] = $row->name;
		}
		
		if(!$obj_group) {
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
					$row['link']      = '/admin/library/'.$row['id'];
					array_push($output, $this->load->view("admin/libraryitem", $row, true));
				}
			}
		} else {
			if(!$loc_type){
				$result=$this->db->query("SELECT 
				locations_types.id,
				locations_types.name AS `title`,
				IF(LENGTH(locations_types.name) > 49, CONCAT(LEFT(locations_types.name, 46) ,'...'), locations_types.name) AS name
				FROM
				locations_types
				WHERE
				`locations_types`.`pl_num` AND
				`locations_types`.`object_group` = ?
				ORDER BY title", array($obj_group));
				if($result->num_rows()){
					foreach ($result->result_array() as $row){
						$row['obj_group'] = $obj_group;
						$row['img']  = '<img src="'.$this->config->item("api").'/images/folder.png" alt="">';
						$row['link'] = '/admin/library/'.$obj_group.'/'.$row['id'];
						array_push($output, $this->load->view("admin/libraryitem", $row, true));
					}
				}
			} else {
				$result = $this->db->query("SELECT 
				IF(LENGTH(`locations`.location_name) > 49, CONCAT(LEFT(`locations`.location_name, 46), '...'),`locations`.location_name) AS name,
				`locations`.location_name AS title,
				`locations`.id
				FROM
				`locations`
				WHERE
				`locations`.`type`    = ?
				AND `locations`.owner = ?
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
			}
		}
		$out['library'] = implode($output, "\n");
		return $this->load->view("admin/library", $out, true);
	}

	#######################################################################
	### управление списками параметров VERIFIED
	#######################################################################
	function show_semantics($object_group){
		$table = array();
		$result=$this->db->query("SELECT 
		IF(properties_list.page = 1, 0, 1) AS editable,
		properties_list.id,
		properties_list.`label`,
		properties_list.selfname,
		properties_list.property_group,
		properties_list.cat,
		properties_list.linked,
		properties_list.searchable,
		properties_list.active
		FROM
		properties_list
		WHERE
		(properties_list.object_group = ?)
		ORDER BY
		properties_list.page,
		properties_list.`row`,
		properties_list.element", array($object_group));
		if($result->num_rows()){
			//array_push($table,'<ul class="span12 row-fluid" style="list-style-type: none; margin-left:0px;">');
			foreach ($result->result_array() as $row){
				//$ed_btn = ($row->editable) ? '<a href="/admin/semantics/'.$object_group.'/'.$row->id.'" class="btn btn-primary btn-mini">Редактировать</a>' : "";
				$row['object_group'] = $object_group;
				$row['infoclass']	 = ($row['editable'])	? ""					: ' class="warning"';
				$row['pic1']		 = ($row['searchable'])	? 'find.png'			: 'lightbulb_off.png';
				$row['title1']		 = ($row['searchable'])	? 'Доступно для поиска' : 'Поиск по параметру не производится';
				$row['pic2']		 = ($row['active'])		? 'lightbulb.png'		: 'lightbulb_off.png';
				$row['title2']		 = ($row['active'])		? 'Параметр активен'	: 'Параметр отключен';
				array_push($table, $this->load->view("admin/parameterline", $row, true));
			}
			
		}
		$out = (sizeof($table)) ? implode($table,"\n") : "<tr><td colspan=6>Nothing Found!</td></tr>";
		return $out;
	}

	function show_semantics_values($object_group = 1, $obj = 0){
		//$this->output->enable_profiler(TRUE);
		$output= array(
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
			'og_name'			=> '',
			'linked'			=> ''
		);

		$result=$this->db->query("SELECT 
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
		properties_list.active,
		`objects_groups`.name AS `og_name`
		FROM
		`objects_groups`
		INNER JOIN properties_list ON (`objects_groups`.id = properties_list.object_group)
		WHERE
		(properties_list.id = ?)", array($obj));
		if($result->num_rows() ){
			$output=$result->row_array();
		}
		$result->free_result();

		$output['searchable']			= (($output['searchable']) ? 'checked="checked"' : '');
		$output['active']				= (($output['active']) ? 'checked="checked"' : '');
		$output['property_group_name']	= $output['property_group'];
		$output['cat_name']				= $output['cat'];
		//$output['og_name']	= $output['og_name'];

		function pack_select($result){
			$array = array();
			if($result->num_rows()){
				foreach($result->result() as $row){
					$selected = ($row->act) ? ' selected="selected"' : '';
					$string   = '<option value="'.$row->vals.'"'.$selected.'>'.$row->vals.'</option>';
					array_push($array, $string);
				}
			}
			$result->free_result();
			return implode($array, "\n");
		}

		function pack_datalist($result){
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

		$result = $this->db->query("SELECT DISTINCT properties_list.fieldtype AS vals, IF(properties_list.fieldtype = ?, 1, 0) AS act FROM properties_list ORDER BY vals", array($output['fieldtype']));
		$output['fieldtype'] = pack_select($result);

		$result=$this->db->query("SELECT DISTINCT properties_list.property_group AS vals FROM properties_list ORDER BY vals");
		$output['property_group'] = pack_datalist($result);

		$result=$this->db->query("SELECT DISTINCT properties_list.cat AS vals FROM properties_list ORDER BY vals");
		$output['cat'] = pack_datalist($result);

		$result=$this->db->query("SELECT DISTINCT properties_list.algoritm AS vals, IF(properties_list.algoritm = ?, 1, 0) AS act FROM properties_list ORDER BY vals", array($output['algoritm']));
		$output['algoritm'] = pack_select($result);

		$result=$this->db->query("SELECT 
		locations.id,
		CONCAT_WS(' ', locations_types.name, locations.location_name) AS name,
		IF(locations.id = ?, 1, 0) AS act
		FROM
		locations
		INNER JOIN locations_types ON (locations.`type` = locations_types.id)
		WHERE
		(locations_types.pr_type = 3)
		ORDER BY name", array($output['linked']));
		$array = array('<option value=0>Не установлена</option>');
		if($result->num_rows()){
			foreach($result->result() as $row){
				$string = '<option value="'.$row->id.'"'.(($row->act) ? ' selected="selected"' : '').'>'.$row->name.'</option>';
				array_push($array, $string);
			}
		}
		$result->free_result();
		$output['linked'] = implode($array, "\n");

		$output['object_group'] = $object_group;
		$output['obj'] = $obj;
		return $output;
	}

	function save_semantics(){
		//$this->output->enable_profiler(TRUE);
		$mode  =  $this->input->post('mode');
		$group = ($this->input->post('object_group'))? $this->input->post('object_group') : 1;
		$sb    = ($this->input->post('searchable')) ? 1 : 0;
		$ac    = ($this->input->post('active')) ? 1 : 0;

		if($mode == "save"){
			$this->db->query("UPDATE
			`properties_list`
			SET
			`properties_list`.`row` = ?,
			`properties_list`.element = ?,
			`properties_list`.label = ?,
			`properties_list`.selfname = ?,
			`properties_list`.page = ?,
			`properties_list`.parameters = ?,
			`properties_list`.property_group = ?,
			`properties_list`.searchable = ?,
			`properties_list`.fieldtype = ?,
			`properties_list`.cat = ?,
			`properties_list`.active = ?,
			`properties_list`.algoritm = ?,
			`properties_list`.`linked` = ?
			WHERE
			`properties_list`.`id` = ?", array(
				$this->input->post('row'),
				$this->input->post('element'),
				$this->input->post('label'),
				$this->input->post('selfname'),
				$this->input->post('page'),
				$this->input->post('parameters'),
				$this->input->post('property_group'),
				$sb,
				$this->input->post('fieldtype'),
				$this->input->post('cat'),
				$ac,
				$this->input->post('algoritm'),
				$this->input->post('linked'),
				$this->input->post('obj')
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
			`properties_list`.`searchable`,
			`properties_list`.`fieldtype`,
			`properties_list`.`cat`,
			`properties_list`.`active`,
			`properties_list`.`object_group`,
			`properties_list`.`algoritm`,
			`properties_list`.`linked`
			) VALUES ( ?,?,?,?,?,?,?,?,?,?,?,?,?,? )",
			array(
				$this->input->post('row'),
				$this->input->post('element'),
				$this->input->post('label'),
				$this->input->post('selfname'),
				$this->input->post('page'),
				$this->input->post('parameters'),
				$this->input->post('property_group'),
				$sb,
				$this->input->post('fieldtype'),
				$this->input->post('cat'),
				$ac,
				$group,
				$this->input->post('algoritm'),
				$this->input->post('linked')
			));
		}
		/*
		кэшируем набор поисковых параметров
		*/
		$output = array();
		$input  = array();
		$result = $this->db->query("SELECT
		`properties_list`.id,
		`properties_list`.algoritm,
		`properties_list`.fieldtype,
		`properties_list`.label,
		`properties_list`.selfname
		FROM
		`properties_list`
		WHERE
		`properties_list`.`object_group` = ?", array($group));
		if($result->num_rows()){
			foreach($result->result() as $row){
				if(!isset($input[$row->label])){
					$input[$row->label] = array();
				}
				$string = $row->id." = { al: '".$row->algoritm."', ft: '".$row->fieldtype."', sn: '".$row->selfname."'}";
				array_push($input[$row->label], $string);
			}
		}
		// сделать распаковку массива и запись в файл

		if($this->input->post('linked')){
			$this->db->query("DELETE 
			FROM 
			`properties_assigned`
			WHERE 
			`properties_assigned`.location_id = ? AND
			`properties_assigned`.property_id = ?", array(
				$this->input->post('linked'),
				$this->input->post('obj')
			));
			$this->db->query("INSERT INTO 
			`properties_assigned` (
				`properties_assigned`.location_id,
				`properties_assigned`.property_id,
				`properties_assigned`.value
			) VALUES (?, ?, 1)", array(
				$this->input->post('linked'),
				$this->input->post('obj')
			));
		}
		redirect('admin/semantics/'.$group);
	}


#######################################################################
### объекты ГИС VERIFIED
#######################################################################
	function gis_objects_show($obj = 0){
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
			$result=$this->db->query("SELECT 
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
		$result=$this->db->query("SELECT `objects_groups`.id,`objects_groups`.name FROM `objects_groups` WHERE `objects_groups`.`active`");
		if($result->num_rows()){
			foreach($result->result() as $row){
				array_push($object['obj_group'],'<option value="'.$row->id.'" '.(($row->id == $object['object_group']) ? 'selected="selected"' : '').'>'.$row->name.'</option>');
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
			$out = $this->load->view('admin/object_types_control_table', $object, true);
		}else{
			$out = "<tr><td colspan=5>Справочник объектов пуст.</td></tr>";
		}
		return $out;
	}

	function gis_save(){
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
			)VALUES( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", array(
				1,
				1,
				1,
				'u',
				$this->input->post('name', TRUE),
				1,
				"",
				"checkbox",
				"",
				$this->input->post('obj_group', TRUE),
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
		}
	}

#######################################################################
### редактор страниц VERIFIED
#######################################################################
	function find_initial_sheet($root = 1){
		$result=$this->db->query("SELECT 
		sheets.id
		FROM
		sheets
		WHERE
		sheets.`root` = ?
		ORDER BY `id`
		LIMIT 1",array($root));
		if($result->num_rows()){
			$row = $result->row();
			$out = $row->id;
		}else{
			$out = $root;
		}
		return $out;
	}
	
	function _sheet_tree($root = 0, $sheet_id = 1){
		$tree="";
		if(!$root){
			$result=$this->db->query("SELECT 
			`sheets`.`id`,
			`sheets`.`parent`,
			`sheets`.`pageorder`,
			`sheets`.`header`,
			`sheets`.`active`,
			`sheets`.`root`
			FROM
			`sheets`
			ORDER BY `sheets`.`parent`,
			`sheets`.`pageorder`",array($root));
		}else{
			$result=$this->db->query("SELECT 
			`sheets`.`id`,
			`sheets`.`parent`,
			`sheets`.`pageorder`,
			`sheets`.`header`,
			`sheets`.`active`,
			`sheets`.`root`
			FROM
			`sheets`
			WHERE
			`sheets`.`root` = ?
			ORDER BY `sheets`.`parent`,
			`sheets`.`pageorder`",array($root));
		}

		if($result->num_rows() ){
			foreach($result->result() as $row){
				$style=array();
				(!$row->active) ? array_push($style,'muted') : '';
				($row->id == $sheet_id) ? array_push($style,"active") : '';
				switch ($row->root){
					case 2 :
						$type="П";
					break;
					case 1 :
						$type="Ст";
					break;
				}
				$tree.=(!strlen($tree)) ? "\..--".$row->parent."--" : '';

				$tree=str_replace("--".$row->parent."--",'<a href="/admin/sheets/edit/'.$row->id.'"><div class="menu_item" class="'.implode($style,";").'">'.$row->id.". ".$row->header.' ('.$type.')</div></a><div class="menu_item_container">--'.$row->id.'--</div>
				--'.$row->parent.'--',$tree);
			}
		}
		$tree=preg_replace("/(\-\-)(\d+)(\-\-)/","",$tree);
		return $tree;
	}

	function sheet_edit($sheet_id){
		$redirect = array('<option value="">Не перенаправляется</option>');
		$act = array();
		$result=$this->db->query("SELECT 
		`sheets`.`id`,
		`sheets`.`text` as sheet_text,
		`sheets`.`root`,
		`sheets`.`owner`,
		`sheets`.`header`,
		`sheets`.`redirect`,
		`sheets`.`date`,
		`sheets`.`ts`,
		`sheets`.`active`,
		`sheets`.`parent`,
		`sheets`.`pageorder`,
		`sheets`.`comment`
		FROM
		`sheets`
		WHERE `sheets`.`id` = ?
		LIMIT 1",array($sheet_id));
		if($result->num_rows()){
			$act = $result->row_array();
			$act['sheet_id'] = $sheet_id;
			$act['sheet_tree'] = $this->adminmodel->_sheet_tree(0,$sheet_id);
			$act['is_active'] = ($act['active']) ? 'checked="checked"' : "";
			$result=$this->db->query("SELECT 
			CONCAT('/map/simple/',`map_content`.id) as id,
			`map_content`.name
			FROM
			`map_content`");
			if($result->num_rows()){
				foreach($result->result() as $row){
					$selected = ($row->id == $act['redirect']) ? 'selected="selected"' : "";
					$string = '<option value="'.$row->id.'" '.$selected.'>'.$row->name.'</option>';
					array_push($redirect, $string);
				}
			}

		}
		$act['redirect'] = implode($redirect, "\n");
		$out = $this->load->view('fragments/sheets_editor',$act,true);
		return $out;
	}

	function sheet_save($sheet_id){
		//$this->output->enable_profiler(TRUE);
		$is_active = ($this->input->post('is_active')) ? 1 : 0;
		if($this->input->post('save_new')){
			$result=$this->db->query("SELECT 
			MAX(`sheets`.pageorder) + 10 AS pageorder
			FROM
			`sheets`
			WHERE `sheets`.`parent` = ?",array($sheet_id));
			if($result->num_rows()){
				$row = $result->row();
				$pageorder = $row->pageorder;
			}else{
				$pageorder = 10;
			}
			$result=$this->db->query("INSERT INTO `sheets`(
			`sheets`.`text`,
			`sheets`.`root`,
			`sheets`.`date`,
			`sheets`.`header`,
			`sheets`.`pageorder`,
			`sheets`.active,
			`sheets`.parent,
			`sheets`.redirect,
			`sheets`.comment
			) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ? )", array(
				$this->input->post('sheet_text',     TRUE),
				$this->input->post('sheet_root',     TRUE),
				date("Y-m-d"),
				$this->input->post('sheet_header',   TRUE),
				$pageorder,
				$is_active,
				$sheet_id,
				$this->input->post('sheet_redirect', TRUE),
				$this->input->post('sheet_comment',  TRUE)
			));
		}else{
			$result=$this->db->query("UPDATE `sheets` SET
			`sheets`.`ts`        = NOW(),
			`sheets`.`text`      = ?,
			`sheets`.`header`    = ?,
			`sheets`.`pageorder` = ?,
			`sheets`.`active`    = ?,
			`sheets`.`parent`    = ?,
			`sheets`.`redirect`  = ?,
			`sheets`.`comment`   = ?,
			`sheets`.`root`      = ?
			WHERE
			`sheets`.`id`        = ?", array(
				$this->input->post('sheet_text',     TRUE),
				$this->input->post('sheet_header',   TRUE),
				$this->input->post('pageorder',      TRUE),
				$is_active,
				$this->input->post('sheet_parent',   TRUE),
				$this->input->post('sheet_redirect', TRUE),
				$this->input->post('sheet_comment',  TRUE),
				$this->input->post('sheet_root',     TRUE),
				$sheet_id
			));
		}
		//return "sheet_save";
	}
#
######################### BEGIN usermanager section ############################
#
	function users_show(){
		$result=$this->db->query("SELECT 
		`users_admins`.id,
		`users_admins`.class_id,
		`users_admins`.nick,
		DATE_FORMAT(`users_admins`.registration_date, '%d.%m.%Y') AS registration_date,
		CONCAT_WS(' ',`users_admins`.name_f,`users_admins`.name_i,`users_admins`.name_o) AS `fio`,
		SUBSTRING(`users_admins`.`info`,1,400) as info,
		`users_admins`.active,
		`users_admins`.rating,
		`users_admins`.valid
		FROM
		`users_admins`
		ORDER BY `users_admins`.`class_id` ASC, fio ASC");
		$output = array();
		$users = array();
		if($result->num_rows()){
			foreach($result->result() as $row){
				$fio = (strlen($row->fio)) ? $row->fio : '<em class="muted">ФИО не указано</em>';
				$string = '<tr>
					<td>'.$row->nick.'</td>
					<td><small>'.$fio.'<br>'.$row->info.'</small></td>
					<td><input type="text" class="short" id="rating" value="'.$row->rating.'"></td>
					<td><input type="checkbox" id="active"'.(($row->active) ? ' checked="checked"' : '').'></td>
					<td><input type="checkbox" id="valid"'.(($row->valid) ? ' checked="checked"' : '').'</td>
					<td><span class="btn btn-primary btn-mini">Сохранить</span></td>
				</tr>';
				array_push($users, $string);
			}
		}
		$output = array(
			'table' => implode($users, "\n")
		);
		return $this->load->view("admin/usermanager", $output, true);
	}

	function users_save($id){
		$result=$this->db->query("UPDATE users_admins 
		SET
		users_admins.active = ?,
		users_admins.valid  = ?,
		users_admins.rating = ?,
		users_admins.lang   = ?,
		users_admins.access = ?
		WHERE
		users_admins.uid = ?", array(
			$this->input->post('active', true),
			$this->input->post('valid' , true),
			$this->input->post('rating', true),
			$this->input->post('lang'  , true),
			$this->input->post('access', true),
			$id
		));
	}
#
######################### END usermanager section ############################
#
#
######################### BEGIN usermanager section ############################
#
	function groups_show($group = 0){
		$result=$this->db->query("SELECT 
		`objects_groups`.id,
		`objects_groups`.name,
		`objects_groups`.active,
		`objects_groups`.icon,
		`objects_groups`.refcoord,
		`objects_groups`.refzoom
		FROM
		`objects_groups`");
		$output = array(
			'coord'  => $this->session->userdata("map_center"),
			'zoom'   => $this->session->userdata("map_zoom"),
			'icon'   => '',
			'name'   => '',
			'id'     => 0,
			'active' => '<input type="checkbox" value="1" name="active">'
		);
		$groups = array();
		if($result->num_rows()){
			foreach($result->result() as $row){
				$checked  = ($row->active) ? 'checked="checked"' : '' ;
				$checkbox = ($row->active) ? "Да" : "Нет" ;
				$string = '<tr>
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

	function group_save($id){
		$result=$this->db->query("UPDATE users_admins 
		SET
		users_admins.active = ?,
		users_admins.valid  = ?,
		users_admins.rating = ?,
		users_admins.lang   = ?,
		users_admins.access = ?
		WHERE
		users_admins.uid = ?", array(
			$this->input->post('active', true),
			$this->input->post('valid' , true),
			$this->input->post('rating', true),
			$this->input->post('lang'  , true),
			$this->input->post('access', true),
			$id
		));
	}
#
######################### END usermanager section ############################
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
		`objects_groups`");
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
		`locations_types`.`pl_num`");
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
		return $this->db->insert_id();
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
	}
######################### end of map content section ############################
#
######################### start of menu content section ############################
}
/* End of file adminmodel.php */
/* Location: ./application/controllers/adminmodel.php */