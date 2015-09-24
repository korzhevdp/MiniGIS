<?php
class Cachemodel extends CI_Model{
	function __construct(){
		parent::__construct();
		// работа с переводами будет принципиально везде
		$this->load->helper('file');
	}
	private function get_statmap($type = 1, $coords = "0,0"){
		$output = array();
		$maps   = array(
			1 => "http://static-maps.yandex.ru/1.x/?z=13&l=map&size=128,128&pt=".$coords.",vkbkm",
			2 => "http://static-maps.yandex.ru/1.x/?l=map&size=128,128&pl=".$coords,
			3 => "http://static-maps.yandex.ru/1.x/?l=map&size=128,128&pl=c:ec473fFF,f:FF660020,w:3,".$coords,
			4 => '',
			5 => ''
		);
		$output['statmap'] = $maps[$type];
		if ($type == 1) {
			$output['lat'] = $act['coord_y'][1];
			$output['lon'] = $act['coord_y'][0];
		}
		return $output;
	}

	// кэширование объекта
	public function cache_location($location_id = 0, $with_output = 0, $mode = 'file'){
		$act      = array();
		$input  = array();
		$output   = array();
		// наполняем $act данными объекта из основного хранилища
		$result = $this->db->query("SELECT
		`locations`.`address`,
		`locations`.`contact_info` as contact,
		`locations`.`coord_y`,
		`locations`.`coord_obj`,
		`locations`.`coord_array`,
		`locations`.`parent`,
		`locations`.`location_name`,
		IF(`locations_types`.`pl_num` = 0, '', `locations_types`.`name`) AS `name`,
		`locations_types`.`object_group`,
		`locations_types`.pr_type,
		`locations_types`.id as typeid
		FROM
		`locations`
		INNER JOIN `locations_types` ON (`locations`.`type` = `locations_types`.`id`)
		WHERE locations.id = ?", array($location_id));
		if($result->num_rows()){
			$act = $result->row_array();
		}
		$act = array_merge($act, $this->get_statmap($act['pr_type'], $act['coord_y']));
		
		$result = $this->db->query("SELECT 
		properties_assigned.property_id,
		properties_assigned.location_id,
		properties_assigned.value,
		properties_list.selfname,
		properties_list.property_group,
		properties_list.fieldtype,
		properties_list.multiplier,
		properties_list.divider,
		properties_list.coef,
		properties_list.algoritm,
		properties_list.label
		FROM
		properties_assigned
		INNER JOIN properties_list ON (properties_assigned.property_id = properties_list.id)
		WHERE
		(properties_assigned.location_id = ?)", array($location_id));
		if($result->num_rows()){
			foreach($result->result() as $row){
				array_push($output, "<h4>".$row->label.'</h4><span class="line">'.$row->selfname.' - '.$row->value.'</span>');
			}
		}
		$act['content'] = implode($output, "\n<br>");
		
		$cache = $this->load->view('ru/frontend/std_view', $act, true);

		if($mode === 'file') {
			write_file('application/views/cache/locations/location_'.$location_id.".src", $cache, "w");
		}else{
			print $cache."<hr>";
		}
		
		if($with_output){
			return $cache;
		}
	}

	private function cache_gis_part($gisroot = 0) {
		$langs  = $this->config->item('lang');

		$result = $this->db->query("SELECT
		locations_types.id   AS type_id,
		objects_groups.id    AS group_id,
		objects_groups.name  AS groupname,
		locations_types.name AS itemname
		FROM
		locations_types
		LEFT OUTER JOIN objects_groups ON (locations_types.object_group = objects_groups.id)
		WHERE
		objects_groups.active
		ORDER BY
		groupname, itemname");
		if ($result->num_rows()) {
			$this->config->load('translations_g');
			$this->config->load('translations_c');
			$this->config->load('translations_p');
			$this->config->load('translations_l');
			$groups     = $this->config->item("groups");
			$categories = $this->config->item("categories");
			// включаем переводчик
			foreach ($langs as $key => $val) {
				$gis_tree      = array();
				foreach ($result->result() as $row) {
					$groupname = (isset($groups[$row->group_id]) && strlen($groups[$row->group_id][$key]))       ? $groups[$row->group_id][$key]    : $row->groupname;
					$itemname  = (isset($categories[$row->type_id]) && strlen($categories[$row->type_id][$key])) ? $categories[$row->type_id][$key] : $row->itemname;
					$grouplink = '<a href="#"><i class="icon-tags"></i>&nbsp;&nbsp;'.$groupname."</a>";
					$itemlink  = '<a href="/map/type/'.$row->type_id.'"><i class="icon-tag"></i>&nbsp;&nbsp;'.$itemname."</a>";
					if (!isset($gis_tree[$grouplink])) {
						$gis_tree[$grouplink] = array();
					}
					array_push($gis_tree[$grouplink], $itemlink);
				}
				write_file('application/views/cache/menus/src/menu_'.$key.'.php', ul($gis_tree, array('class' => 'dropdown-menu')));
			}
		}
	}

	private function cache_docs($root = 1, $mode = 'file'){
		$this->config->load('translations_a', FALSE);
		$langs    = $this->config->item('lang');
		$articles = $this->config->item('articles');
		$result = $this->db->query("SELECT
		sheets.redirect,
		sheets.parent,
		sheets.id,
		sheets.header,
		sheets1.header as topheader
		FROM
		sheets sheets1
		RIGHT OUTER JOIN sheets ON (sheets1.id = sheets.parent)
		where sheets.active
		ORDER BY
		sheets.parent DESC,
		sheets.pageorder", array($root));
		if($result->num_rows()){
			$this->load->helper('html');
			foreach ($langs as $lang => $langname) {
				$input = array();
				foreach($result->result() as $row) {
					$link = (strlen($row->redirect)) ? $row->redirect : '/page/docs/'.$row->id;
					$groupname = (isset($articles[$row->parent]) && strlen($articles[$row->parent][$lang]))    ? $articles[$row->parent][$lang]    : $row->topheader;
					$itemname  = (isset($articles[$row->id]) && strlen($articles[$row->id][$lang])) ? $articles[$row->id][$lang] : $row->header;
					$grouplink = '<a href="#"><i class="icon-tags"></i>&nbsp;&nbsp;'.$groupname."</a>";
					$itemlink  = '<a href="'.$link.'"><i class="icon-tag"></i>&nbsp;&nbsp;'.$itemname."</a>";
					if (!isset($input[$grouplink])) {
						$input[$grouplink] = array();
					}
					array_push($input[$grouplink], $itemlink);
				}
				foreach($input as $key => $val){
					$list = $input[$key];
					break;
				}
				//print_r($list);
				if($mode === 'file') {
					write_file('application/views/cache/menus/docs_'.$lang.'.php', ul($list, array('class' => 'dropdown-menu')));
				} else {
					print '<link href="http://api.korzhevdp.com/css/frontend.css" rel="stylesheet" media="screen" type="text/css">'.ul($list, array('class' => 'dropdown-menu'))."<hr>";
				}
			}
		}
	}
	//кэширование меню
	// verified --->
	public function menu_build($docroot = 1, $gisroot = 0, $mode = "file"){
		/*
		меню строится из дерева созданных документов, начиная сid документа указанного как $docroot и рекурсивно далее
		кроме того отстраивается дерево объектов GIS. Корневой объект задаётся в конфигурационном файле или явно
		*/

		$this->load->helper('html');
		$this->cache_gis_part($gisroot);
		$this->cache_docs($docroot);
		$langs = $this->config->item('lang');
		foreach ($langs as $lang => $val) {
			$ans = array(
				'gis'     => $this->load->view("cache/menus/src/menu_".$lang, array(), true),
				'housing' => $this->load->view("cache/menus/docs_".$lang, array(), true),
				'rest'    => "" // reserved for future use
			);
			if($mode === 'file'){
				write_file('application/views/cache/menus/menu_'.$lang.'.php', $this->load->view($lang.'/frontend/menu', $ans, true));
			}else{
				print '<link href="http://api.korzhevdp.com/css/frontend.css" rel="stylesheet" media="screen" type="text/css">'.$this->load->view('frontend/menu', $ans, true)."<hr>";
			}
		}
	}

	private function cache_selector_layers($layers){
		$output = array();
		$result = $this->db->query("SELECT
		'111' AS marker,
		locations_types.name AS selfname,
		'checkbox' AS fieldtype,
		locations_types.pl_num AS id,
		`properties_list`.label
		FROM
		`properties_list`
		INNER JOIN locations_types ON (`properties_list`.id = locations_types.pl_num)
		WHERE
		(locations_types.object_group IN (".$layers."))
		AND (locations_types.pl_num > 0)
		ORDER BY
		marker,
		properties_list.label,
		properties_list.selfname");
		if($result->num_rows()){
			foreach ($result->result() as $row){
				if(!isset($output[$row->marker])){ $output[$row->marker] = array(); }
				if(!isset($output[$row->marker][$row->label])){ $output[$row->marker][$row->label] = array(); }
				$output[$row->marker][$row->label][$row->id] = array(
					'name'       => $row->selfname,
					'fieldtype'  => $row->fieldtype,
					'alg'        => "ud"
				);
			}
		}
		return $output;
	}

	private function cache_selector_types($types){
		$output = array();
		$result = $this->db->query('SELECT
		CONCAT(properties_list.page, properties_list.`row`, properties_list.element) AS marker,
		properties_list.label,
		properties_list.selfname,
		properties_list.algoritm AS alg,
		properties_list.fieldtype,
		properties_list.id
		FROM
		properties_list
		WHERE
		properties_list.id IN (
			SELECT locations_types.pl_num
			FROM
			locations_types
			WHERE
			locations_types.id IN ('.$types.')
		)
		AND properties_list.searchable
		AND properties_list.active
		ORDER BY
		marker,
		properties_list.label,
		properties_list.selfname');
		if($result->num_rows()){
			foreach ($result->result() as $row){
				if(!isset($output[$row->marker])){ $output[$row->marker] = array(); }
				if(!isset($output[$row->marker][$row->label])){ $output[$row->marker][$row->label] = array(); }
				$output[$row->marker][$row->label][$row->id] = array(
					'name'       => $row->selfname,
					'fieldtype'  => $row->fieldtype,
					'alg'        => "ud"
				);
			}
		}
		return $output;
	}

	private function cache_selector_properties($layers){
		$output = array();
		$result = $this->db->query("SELECT
		CONCAT(properties_list.page, properties_list.`row`, properties_list.element) AS marker,
		properties_list.label,
		properties_list.selfname,
		properties_list.algoritm AS alg,
		properties_list.fieldtype,
		properties_list.id
		FROM
		properties_list
		WHERE
		properties_list.`object_group` IN (".$layers.")
		AND `properties_list`.`id` NOT IN (
			SELECT `locations_types`.`pl_num` FROM `locations_types` WHERE `locations_types`.`id` IN (
				SELECT `locations_types`.`id` FROM `locations_types` WHERE `locations_types`.`object_group` = ".$layers."
			)
		)
		AND properties_list.searchable
		AND properties_list.active
		ORDER BY
		marker,
		properties_list.label,
		properties_list.selfname");
		if($result->num_rows()){
			foreach ($result->result() as $row){
				if(!isset($output[$row->marker])){ $output[$row->marker] = array(); }
				if(!isset($output[$row->marker][$row->label])){ $output[$row->marker][$row->label] = array(); }
				$output[$row->marker][$row->label][$row->id] = array(
					'name'       => $row->selfname,
					'fieldtype'  => $row->fieldtype,
					'alg'        => "ud"
				);
			}
		}
		return $output;
	}
	/*
	# Для селектора генерируются только поля типа: text, select и checkbox. 
	# Поле Textarea предназаначено исключительно для ввода больших текстов в админской консоли
	*/
	
	private function generate_selector($src, $map, $mode){
		//print $map."<br>";
		//print $mode."<br>";
		//print_r($src)."<hr>";
		//exit;
		$properties = $this->config->item('properties');
		$labels     = $this->config->item('labels');
		foreach ($this->config->item('lang') as $lang => $val){
			$table = array();
			foreach($src as $rowmarker => $elements){
				$incrementer = 0;
				foreach($elements as $label => $objects){
					$label = (isset($labels[$label]) && strlen($labels[$label][$lang])) ? $labels[$label][$lang] : $label;
					array_push($table, '<div class="grouplabel" id="gl_'.$rowmarker.$incrementer.'">'."\n".$label."\n</div>");
					$backcounter = sizeof($objects);
					$checkboxes  = array();
					$values      = array();
					$htmlcontrol = array();
					foreach($objects as $object_id => $element) {
						$options = array('<option value="0">Выберите вариант</option>');
						if (!isset($element['alg'])) {
							$element['alg'] = "u"; 
						}
						$element['name'] = (isset($properties[$object_id]) && strlen($properties[$object_id][$lang])) ? $properties[$object_id][$lang] : $element['name'];
						/* генерация */
						switch ($element['fieldtype']){
							case 'text':
								$string = '<li class="itemcontainer" obj="'.$object_id.'"><input type="text">'.$element['name']."</li>";
								array_push($htmlcontrol, $string);
							break;
							case 'select':
								$string = '<option value="'.$object_id.'">'.$element['name'].'</option>';
								array_push($values, $string);
								--$backcounter;
								if($backcounter === 0) {
									$string = '<li class="itemcontainer" obj="'.$object_id.'"><select>'."\n".implode($values,"\n").'</select></li>';
									array_push($htmlcontrol, $string);
								}
							break;
							case 'checkbox':
								$string = '<li class="itemcontainer" obj="'.$object_id.'"><img src="'.$this->config->item('api').'/images/clean_grey.png" alt=" ">'.$element['name'].'</li>';
								array_push($checkboxes, $string);
								--$backcounter;
								if ($backcounter === 0){
									array_push($htmlcontrol, "<ul>\n".implode($checkboxes, "\n")."\n</ul>");
								}
							break;
						}
					}
					array_push($table, '<div class="groupcontainer" id="gc_'.$rowmarker.$incrementer++.'">'."\n".implode($htmlcontrol, "\n")."\n</div>");
				}
				//print implode($htmlcontrol, "\n");
				//array_push($table, '<div class="grouplabel" id="gl_'.$rowmarker.'">'."\n".$label."\n</div>");
				//; # штатная генерация элементов
				######## конец переключателей
			}
			$content = implode($table, "\n");
			if ($mode === "file") {
				write_file('application/views/cache/selectors/selector_'.$map.'_'.$lang.'.php', $content);
			} else {
				print '<link href="http://api.korzhevdp.com/css/frontend.css" rel="stylesheet" media="screen" type="text/css">'.$content;
				print '<br>########################################## end of map item ##########################################';
			}
		}
	}

	private function generate_switches($src, $map, $mode){
		//print_r($src);
		$sws = array();
		$properties = $this->config->item('properties');
		foreach ($this->config->item('lang') as $lang => $val) {
			foreach ($src as $rowmarker => $elements){
				foreach ($elements as $label => $objects){
					foreach ($objects as $object_id => $element) {
						if (!isset($element['alg'])) {
							$element['alg'] = "u"; 
						}
						$element['name'] = (isset($properties[$object_id]) && strlen($properties[$object_id][$lang])) ? $properties[$object_id][$lang] : $element['name'];
						/*
						switch ($element['fieldtype']){
							case 'text':
								$sws[$object_id] = $object_id.': { value: "", fieldtype: "text",    alg: "'.$element['alg'].'", text: "'.$element['name'].'" }';
							break;
							case 'select':
								$sws[$object_id] = $object_id.': { value: 0, fieldtype: "select",   alg: "'.$element['alg'].'" , text: "'.$element['name'].'" }';
							break;
							case 'checkbox':
								$sws[$object_id] = $object_id.': { value: 0, fieldtype: "checkbox", alg: "'.$element['alg'].'", text: "'.$element['name'].'" }';
							break;
						}
						*/
						$sws[$object_id] = $object_id.': { value: 0, fieldtype: "'.$element['fieldtype'].'", alg: "'.$element['alg'].'", text: "'.$element['name'].'" }';
					}
				}
			}
			$content = "switches = {\n\t".implode($sws, ",\n")."\n}";
			if ($mode === "file") {
				write_file('application/views/cache/selectors/selector_'.$map.'_switches_'.$lang.'.php', $content);
			} else {
				print "<hr>";
				print '<link href="http://api.korzhevdp.com/css/frontend.cs-s" rel="stylesheet" media="screen" type="text/css">'.$content;
				print '<br>########################################## end of map item ##########################################';
			}
		}
	}

	//кэширование навигатора
	public function cache_selector_content($mode = "file") {
		/*
		уже переписано под множественные слои и типы объектов
		*/
		//$this->output->enable_profiler(TRUE);
		$output = array();
		$table  = array();
		$map_content = array();

		$result = $this->db->query("SELECT
		map_content.id,
		map_content.a_layers,
		map_content.a_types
		FROM
		map_content");
		if ($result->num_rows()) {
			foreach ($result->result() as $row){
				$map_content[$row->id] = array($row->a_layers, $row->a_types);
			}
		}

		foreach($map_content as $map => $val){
			$output     = array();
			$refgroups  = array();
			if($val[0] == "0" && strlen($val[1]) && $val[1] != 0){
				$result = $this->db->query("SELECT DISTINCT
				`locations_types`.object_group
				FROM
				`locations_types`
				WHERE `locations_types`.`id` IN (".$val[1].")");
				if($result->num_rows()){
					foreach($result->result() as $row){
						array_push($refgroups, $row->object_group);
					}
				}
			}
			if(strlen($val[0]) && $val[0] != 0){
				$output = $this->cache_selector_layers($val[0]);
				//print "run layers";
			}
			// затем типы
			if(strlen($val[1]) && $val[1] != 0){
				$output = array_merge($output, $this->cache_selector_types($val[1]));
				//print "run types";
			}
			// затем свойства
			if($val[0] == "0" && strlen($val[1]) && $val[1] != 0){
				$output = array_merge($output, $this->cache_selector_properties(implode($refgroups, ",")));
				//print "run types";
			}

			//print_r($output);
			// генерация
			$this->generate_selector($output, $map, $mode);
			$this->generate_switches($output, $map, $mode);
		}

		#######################################################################
	}

	public function build_object_lists(){
		$result=$this->db->query("SELECT 
		GROUP_CONCAT(CONCAT('<option value=\"',locations_types.id,'\">',locations_types.name,'</option>') SEPARATOR '') as `list`,
		`locations_types`.`object_group`
		FROM
		locations_types
		WHERE
		(locations_types.pl_num <> 0)
		GROUP BY `locations_types`.`object_group`");
		if($result->num_rows()){
			foreach ($result->result() as $row){
				write_file('application/views/cache/typelists/typeslist_'.$row->object_group.'.php', $row->list);
			}
		}
	}

	public function cache_all(){
		$links = array();
		$result = $this->db->query("SELECT 
		locations.id,
		locations.location_name
		FROM
		locations");
		if($result->num_rows()){
			foreach($result->result() as $row){
				$this->cache_location($row->id);
				array_push($links, '<a href="http://maps.korzhevdp.com/page/gis/'.$row->id.'">'.$row->location_name.'</a>');
			}
			print "Sucessfully re-cached ".$result->num_rows()." objects!";
		}
		$this->load->helper("file");
		write_file('../base/extralinks.html', '<!doctype html><html lang="en"><head><meta charset="UTF-8"></head><body>'.implode($links,"<br>\n")).'</body></html>';
	}
}
#
/* End of file cachemodel.php */
/* Location: ./application/models/cachemodel.php */