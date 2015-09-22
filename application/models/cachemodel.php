<?php
class Cachemodel extends CI_Model{
	function __construct(){
		parent::__construct();
		// работа с переводами будет принципиально везде
		$this->load->helper('file');
	}
	
	// кэширование объекта
	public function cache_location($location_id = 0, $with_output = 0){ //она же fetch unified information;
		//выбираем назначенные параметры объекта
		$location = array();
		$act      = array();
		$output   = array();
		// наполняем $act данными объекта из основного хранилища
		$result=$this->db->query("SELECT
		`locations`.`address`,
		`locations`.`contact_info` as contact,
		`locations`.`coord_y`,
		`locations`.`parent`,
		IF(`locations_types`.`pl_num` = 0, '', `locations_types`.`name`) AS `name`,
		`locations`.`location_name`,
		`locations_types`.`object_group`,
		`locations_types`.pr_type
		FROM
		`locations`
		INNER JOIN `locations_types` ON (`locations`.`type` = `locations_types`.`id`)
		WHERE locations.id = ?", array($location_id));
		if($result->num_rows()){
			$act = $result->row_array();
		}
		if(isset($act['pr_type'])){
			switch($act['pr_type']){
				case 1 :
					$act['statmap'] = "http://static-maps.yandex.ru/1.x/?z=13&l=map&size=128,128&pt=".$act['coord_y'].",vkbkm";
				break;
				case 2 :
					$act['statmap'] = "http://static-maps.yandex.ru/1.x/?l=map&size=128,128&pl=".$act['coord_y'];
				break;
				case 3 :
					$act['statmap'] = "http://static-maps.yandex.ru/1.x/?l=map&size=128,128&pl=c:ec473fFF,f:FF660020,w:3,".$act['coord_y'];
				break;
				case 4 :
					$act['statmap'] = "";
				break;
				case 5 :
					$act['statmap'] = "";
				break;
			}
		}
		
		$preoutput = array();
		$output    = array();
		$icons     = array();
		$icons['business'] = '<img src="'.$this->config->item('api').'/images/icons/briefcase.png" width="16" height="16" border="0" alt="">&nbsp;&nbsp;&nbsp;';
		$icons['health']   = '<img src="'.$this->config->item('api').'/images/icons/health.png" width="16" height="16" border="0" alt="">&nbsp;&nbsp;&nbsp;';
		$icons['services'] = '<img src="'.$this->config->item('api').'/images/icons/service-bell.png" width="16" height="16" border="0" alt="">&nbsp;&nbsp;&nbsp;';
		$icons['other']    = '<img src="'.$this->config->item('api').'/images/icons/information.png" width="16" height="16" border="0" alt="">&nbsp;&nbsp;&nbsp;';
		$icons['sport']    = '<img src="'.$this->config->item('api').'/images/icons/sports.png" width="16" height="16" border="0" alt="">&nbsp;&nbsp;&nbsp;';
		$icons['sights']   = '<img src="'.$this->config->item('api').'/images/icons/photo.png" width="16" height="16" border="0" alt="">&nbsp;&nbsp;&nbsp;';
		
		$this->db->query("SET group_concat_max_len = 8192");
		$result=$this->db->query("SELECT
		properties_list.label,
		properties_list.id,
		GROUP_CONCAT(
			CONCAT_WS(
				'|', 
				IF(NOT LENGTH(properties_list.selfname), 0, properties_list.selfname),
				properties_list.cat,
				CASE
				WHEN properties_list.fieldtype = 'checkbox' THEN properties_list.selfname
				WHEN properties_list.fieldtype = 'textarea' THEN properties_assigned.value
				WHEN properties_list.fieldtype = 'select' THEN properties_list.selfname
				WHEN properties_list.fieldtype = 'text' THEN IF(properties_list.multiplier <> 1 AND properties_list.divider <> 1, properties_assigned.value % properties_list.divider / properties_list.multiplier, properties_assigned.value) END
			)
		ORDER BY properties_list.cat SEPARATOR '^') AS content
		FROM
		properties_assigned
		INNER JOIN properties_list ON (properties_assigned.property_id = properties_list.id)
		INNER JOIN locations ON (properties_assigned.location_id = locations.id)
		INNER JOIN `locations_types` ON (locations.`type` = `locations_types`.id)
		WHERE
		(properties_list.active) AND
		(`properties_assigned`.`property_id` <> `locations_types`.`pl_num`) AND
		(`locations`.id = ?)
		GROUP BY properties_list.label
		ORDER BY
		`properties_list`.`label` ASC,
		`properties_list`.`selfname` ASC", array($location_id));
		if($result->num_rows()){
			foreach($result->result() as $row){
				$preoutput[$row->label] = array();
				$data = explode("^",$row->content);
				foreach($data as $val){
					$data2 = explode('|',$val);
					if(isset($data2[1])){
						if($data2[1] == 'searange'){
							$preoutput['Расстояние до моря'][0] = $data2[2];
							break;
						}
						if($data2[1] == 'searange_units'){
							$preoutput['Расстояние до моря'][1] = $data2[2];
							break;
						}
						if($data2[1] == 'place'){
							array_push($output['встреча/проводы'], $data2[2]);
							break;
						}
						$icon = (isset($icons[$data2[1]])) ? $icons[$data2[1]] : "";
						$string = $icon.$data2[2];
						array_push($preoutput[$row->label], "<p>".str_replace("\n", "</p><p>", $string)."</p>");
					}
				}
			}
		}
		//print_r($output);
		foreach($preoutput as $key=>$val){
			array_push($output, "<h4>".$key."</h4>").
			array_push($output, implode($val, "\n"));
		}
		$act['content']= implode($output, "\n<br>");
		$cache = $this->load->view('frontend/std_view', $act, true);
		write_file('application/views/cache/locations/location_'.$location_id.".src", $cache, "w");
		if($with_output){
			return $cache;
		}
	}

	private function cache_gis_part($gisroot = 0){
		$root   = ($gisroot) ? $gisroot : $this->config->item('mod_gis');
		$langs  = $this->config->item('lang');

		$result = $this->db->query("SELECT
		locations_types.id AS type_id,
		properties_list.selfname AS itemname,
		`objects_groups`.name AS groupname,
		`objects_groups`.id AS group_id
		FROM
		properties_list
		LEFT OUTER JOIN locations_types  ON (properties_list.id = locations_types.pl_num)
		LEFT OUTER JOIN `objects_groups` ON (locations_types.object_group = `objects_groups`.id)
		WHERE
		LENGTH(properties_list.selfname)
		ORDER BY groupname, itemname");
		if ($result->num_rows()) {
			$this->config->load('translations_g');
			$this->config->load('translations_c');
			$this->config->load('translations_p');
			$this->config->load('translations_l');
			$groups     = $this->config->item("groups");
			$categories = $this->config->item("categories");
			// включаем переводчик
			foreach ($langs as $key => $val) {
				$gis_tree   = array();
				foreach ($result->result() as $row) {
					$groupname = (strlen($groups[$row->group_id][$key]))    ? $groups[$row->group_id][$key]    : $row->groupname;
					$itemname  = (strlen($categories[$row->type_id][$key])) ? $categories[$row->type_id][$key] : $row->itemname;
					$grouplink = '<a href="#"><i class="icon-tags"></i>&nbsp;&nbsp;'.$groupname."</a>";
					$itemlink  = '<a href="/map/type/'.$row->type_id.'"><i class="icon-tag"></i>&nbsp;&nbsp;'.$itemname."</a>";
					if (!isset($gis_tree[$grouplink])) {
						$gis_tree[$grouplink] = array();
					}
					array_push($gis_tree[$grouplink], $itemlink);
				}
				//print_r($gis_tree);
				write_file('application/views/cache/menus/src/menu_'.$key.'.php', ul($gis_tree, array('class' => 'dropdown-menu')));
			}
		}
		//return ul($gis_tree, array('class' => 'dropdown-menu'), 2);
	}

	public function cache_docs($root = 1, $mode = 'file'){
		$this->config->load('translations_a', FALSE);
		$langs    = $this->config->item('lang');
		$articles = $this->config->item('articles');
		$result = $this->db->query("SELECT 
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
					$groupname = (strlen($articles[$row->parent][$lang]))    ? $articles[$row->parent][$lang]    : $row->topheader;
					$itemname  = (strlen($articles[$row->id][$lang])) ? $articles[$row->id][$lang] : $row->header;
					$grouplink = '<a href="#"><i class="icon-tags"></i>&nbsp;&nbsp;'.$groupname."</a>";
					$itemlink  = '<a href="/maps/simple/'.$row->id.'"><i class="icon-tag"></i>&nbsp;&nbsp;'.$itemname."</a>";
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
		foreach ($this->config->item('lang') as $lang => $val) {
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
		OR properties_list.object_group IN (
			Select locations_types.object_group FROM locations_types WHERE locations_types.id IN('.$types.')
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
		$categories = $this->config->item('categories');
		$labels     = $this->config->item('labels');
		foreach ($this->config->item('lang') as $lang => $val){
			$table = array();
			foreach($src as $rowmarker => $elements){
				$incrementer = 0;
				foreach($elements as $label => $objects){
					$label = strlen($labels[$label][$lang]) ? $labels[$label][$lang] : $label;
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
						$element['name'] = strlen($categories[$object_id][$lang]) ? $categories[$object_id][$lang] : $element['name'];
						/* генерация */
						switch ($element['fieldtype']){
							case 'text':
								$string = '<li class="itemcontainer" obj="'.$object_id.'"><input type="text">'.$element['name']."</li>";
								array_push($htmlcontrol, $string);
							break;
							case 'select':
								$string = '<option value="'.object_id.'">'.$element['name'].'</option>';
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
						$element['name'] = strlen($properties[$object_id][$lang]) ? $properties[$object_id][$lang] : $element['name'];
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
			if(strlen($val[0]) && $val[0] != 0){
				$output = $this->cache_selector_layers($val[0]);
				//print "run layers";
			}
			// затем типы
			if(strlen($val[1]) && $val[1] != 0){
				$output = array_merge($output, $this->cache_selector_types($val[1]));
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