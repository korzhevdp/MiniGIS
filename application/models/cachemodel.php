<?php
class Cachemodel extends CI_Model{
	function __construct(){
		parent::__construct();
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
		$this->load->helper('file');
		$cache = $this->load->view('frontend/std_view', $act, true);
		write_file('application/views/cache/locations/location_'.$location_id.".src", $cache, "w");
		if($with_output){
			return $cache;
		}
	}

	//кэширование меню
	// verified --->
	function menu_build($root=1, $housing_parent=0, $gis_obj_group=0 ){
		$this->load->helper('html');
		########################## GIS part ###########################
		//справочник ГИС
		$gis_tree = array();
		$result=$this->db->query("SELECT 
		CONCAT('<a href=\"#\"><i class=\"icon-tags\"></i>&nbsp;&nbsp;', properties_list.property_group, '</a>') as property_group,
		CONCAT('<a href=\"/map/type/', locations_types.id, '\"><i class=\"icon-tag\"></i> ', properties_list.selfname, '</a>') AS link
		FROM
		properties_list
		LEFT OUTER JOIN locations_types ON (properties_list.id = locations_types.pl_num)
		WHERE
		(locations_types.object_group = ?) AND 
		(LENGTH(properties_list.selfname) > 0)
		ORDER BY
		properties_list.property_group", array($gis_obj_group));
		if($result->num_rows()){
			foreach($result->result() as $row){
				(!isset($gis_tree[$row->property_group])) ? $gis_tree[$row->property_group] = array() : "";
				array_push($gis_tree[$row->property_group], $row->link);
			}
		}

		$ans['gis'] = ul($gis_tree, array('class' => 'dropdown-menu'), 2);
		###############################################################
		########################## DOC part ###########################
		if($housing_parent){
			$housing = array();
			$result = $this->db->query("SELECT 
			sheets.id,
			sheets.header,
			IF(LENGTH(sheets.redirect), sheets.redirect, CONCAT('/page/docs/', CAST(sheets.id as BINARY))) AS link,
			sheets.comment,
			sheets.parent
			FROM
			sheets
			WHERE
			(sheets.active = 1) and
			`sheets`.`root` = 1
			ORDER by
			`sheets`.`parent` DESC,
			`sheets`.`pageorder`");
			if($result->num_rows()){
				$rr = array();
				foreach($result->result() as $row){
					(!isset($rr[$row->parent])) ? $rr[$row->parent] = array() : "";
					if($row->parent == $housing_parent){
						array_push($housing,'<a href="'.$row->link.'"><i class="icon-tag"></i> '.$row->header.'</a>');
					}else{
						(!isset($rr[$row->parent])) ? $rr[$row->parent] = array() : "";
						$string = (isset($rr[$row->id]) && sizeof($rr[$row->id])) 
							? '<ul class="nav">
							<li class="dropdown" title="'.$row->comment.'">
								<a href="#" class="dropdown-toggle" data-toggle="dropdown">'.$row->header.'<b class="caret"></b></a>
								<ul class="dropdown-menu"><!--'.$row->id.'-->
								</ul>
							</li></ul>'
							: '<li><a href="'.$row->link.'" title="'.$row->comment.'"><i class="icon-file"></i>&nbsp;'.$row->header.'</a></li>';
						array_push($rr[$row->parent],$string);
					}
				}
				$menu = implode($rr[0],"\n");
				foreach($rr as $key => $val){
					$menu = str_replace('<!--'.$key.'-->', implode($val, "\n"), $menu);
				}
			}
			$ans['housing'] = ul($housing, array('class' => 'dropdown-menu'));
		}else{
			$ans['housing'] = "";
		}
		$ans['rest'] = $menu;
		$this->load->helper('file');
		write_file('application/views/cache/menus/menu.php', $this->load->view('frontend/menu',$ans, true));
	}

	//кэширование навигатора
	public function cache_selector_content($mapset = 1, $mode = "file"){
		//$this->output->enable_profiler(TRUE);
		$result=$this->db->query("SELECT 
		map_content.a_layers,
		map_content.a_types
		FROM
		map_content
		WHERE `map_content`.`id` = ?", array($mapset) );
		if($result->num_rows()){
			$map_content = $result->row();
		}
		$result->free_result();

		$output = array();
		$table  = array();
		// если есть целые активные слои, сперва выбираем список объектов входящих в эти слои.
		if($map_content->a_layers){
			$result = $this->db->query("SELECT 
			locations_types.name AS selfname,
			'checkbox' AS fieldtype,
			locations_types.pl_num AS id,
			`properties_list`.label
			FROM
			`properties_list`
			INNER JOIN locations_types ON (`properties_list`.id = locations_types.pl_num)
			WHERE
			(locations_types.object_group IN (".$map_content->a_layers.")) AND 
			(locations_types.pl_num > 0)
			ORDER BY
			properties_list.selfname");
			if($result->num_rows()){
				foreach ($result->result() as $row){
					if(!isset($output['z11'])){ $output['z11'] = array(); }
					if(!isset($output['z11'][$row->id])){ 
						$output['z11'][$row->id] = array(
							'label'      => $row->label,
							'name'       => $row->selfname,
							'fieldtype'  => $row->fieldtype,
							'group'      => "u"
						);
					}
				}
			}
		}
		$result->free_result();
		// типы объектов активного слоя выбраны в массив
		// выбираем прочие признаки

		if(!strlen($map_content->a_layers)) {
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
				locations_types.id IN ('.$map_content->a_types.')
			)
			AND properties_list.searchable
			AND properties_list.active
			ORDER BY
			properties_list.label,
			properties_list.selfname');
		}

		if($result->num_rows()){
			foreach ($result->result() as $row){
				if(!isset($output[$row->marker])){ $output[$row->marker] = array(); }
				if(!isset($output[$row->marker][$row->id])){ $output[$row->marker][$row->id] = array(); }
				if(!isset($output[$row->marker]['label'])){
					$output[$row->marker][$row->id]['label'] = $row->label;
				}
				$output[$row->marker][$row->id]['name']      = $row->selfname;
				$output[$row->marker][$row->id]['fieldtype'] = $row->fieldtype;
				$output[$row->marker][$row->id]['alg']       = $row->alg;
				//['group'] исключать нельзя, ввиду неоднозначности трактовки элемента checkbox: как объединительный (И), так и объединительно-исключающий (ИЛИ) контекст поиска
			}
		}
		$result->free_result();
		$sws = array();
		foreach($output as $key => $val){
			$backcounter = sizeof($val);
			$ea          = array();
			$values      = array();
			foreach($val as $obj => $val3){
				#
				# Для селектора генерируются только поля типа: text, select и checkbox. 
				# Поле Textarea предназаначено исключительно для ввода больших текстов в админской консоли
				#
				if($obj == 'label'){
					continue;
				}
				$options = array();
				$element = array();
				if (!isset($val['label'])) {
					$val['label'] = $val3['label']; // проброс параметра "наружу"
				} 
				if (!isset($sws[$val3['label']])) {
					$sws[$val3['label']] = array(); 
				}
				if (!isset($val3['alg'])) {
					$val3['alg'] = "u"; 
				}
				switch ($val3['fieldtype']){
					case 'text':
						array_push($element, '<li class="itemcontainer" obj="'.$obj.'"><input type="text">'.$val3['name']."</li>");
						array_push($sws[$val3['label']], $obj.': { value: "", fieldtype: "text", alg: "'.$val3['alg'].'", text: "'.$val3['name'].'" }');
					break;
					case 'select':
						array_push($values, '<option value="'.$obj.'">'.$val3['name'].'</option>');
						--$backcounter;
						if(!$backcounter){
							array_push($element, '<li class="itemcontainer" obj="'.$obj.'"><select><option value="0">Выберите вариант</option>'."\n".implode($values,"\n").'</select></li>');
						}
						array_push($sws[$val3['label']], $obj.': { value: 0, fieldtype: "select", alg: "'.$val3['alg'].'" , text: "'.$val3['name'].'" }');
					break;
					case 'checkbox':
						$tabstr='<li class="itemcontainer" obj="'.$obj.'"><img src="'.$this->config->item('api').'/images/clean_grey.png" alt=" ">'.$val3['name'].'</li>';
						array_push($ea, $tabstr);
						array_push($sws[$val3['label']], $obj.': { value: 0, fieldtype: "checkbox", alg: "'.$val3['alg'].'", text: "'.$val3['name'].'" }');
						--$backcounter;
						if (!$backcounter){
							array_push($element, "<ul>\n".implode($ea, "\n")."\n</ul>");
						}
					break;
				}
			}
			if(!strlen($val['label'])){ $val['label'] = "&nbsp;"; }
			array_push($table, '<div class="grouplabel" id="gl_'.$key.'">'."\n".$val['label']."\n</div>");
			array_push($table, '<div class="groupcontainer" id="gc_'.$key.'">'."\n".implode($element, "\n")."\n</div>"); # штатная генерация элементов
			######## конец переключателей
		}


		$scr = array();
		foreach($sws as $val){
			array_push($scr, implode($val, ",\n\t"));
		}
		//print_r($sws);
		//print_r($scr);
		$selector = implode($table, "\n");
		$switches = "switches = {\n\t".implode($scr, ",\n")."\n}";
		if ($mode === "file"){
			// print "writing application/views/cache/menus/selector_".$mapset.".php<br>";
			// пишем в файл (следите за путями)
			$this->load->helper('file');
			write_file('application/views/cache/menus/selector_'.$mapset.'.php', $selector);
			write_file('application/views/cache/menus/selector_'.$mapset.'_switches.php', $switches);
		} else {
			print "Активные слои объектов: ".$map_content->a_layers."<br>Активные типы объектов: ".$map_content->a_types;
			print "<hr>";
			print_r($output);
			print "<hr>";
			print '<link href="http://api.korzhevdp.com/css/frontend.css" rel="stylesheet" media="screen" type="text/css">'.$selector."<br><hr><br>".$switches;
		}
	}

	function _build_object_lists(){
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
				write_file('application/views/cache/menus/typeslist_'.$row->object_group.'.php', $row->list);
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