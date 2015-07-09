<?php
class Cachemodel extends CI_Model{
	function __construct(){
		parent::__construct();
	}

	function _make_cache($location_id = 15, $with_output = 0){ //она же fetch unified information;
		//выбираем назначенные параметры объекта
		$location = array();
		$act = array();
		$output = array();
		// наполняем $act данными объекта из основного хранилища
		$result=$this->db->query("SELECT
		`laz_locations`.`address`,
		`laz_locations`.`contact_info` as contact,
		`laz_locations`.`coord_y`,
		`laz_locations`.`parent`,
		IF(`laz_locations_types`.`pl_num` = 0, '', `laz_locations_types`.`name`) AS `name`,
		`laz_locations`.`location_name`,
		`laz_locations_types`.`object_group`
		FROM
		`laz_locations`
		INNER JOIN `laz_locations_types` ON (`laz_locations`.`type` = `laz_locations_types`.`id`)
		WHERE laz_locations.id = ?", Array($location_id));
		if($result->num_rows()){
			$act = $result->row_array();
		}
		
		$act['yandex_key']=$this->config->item('yandex_key');
		$act['rooms'] = (($act['object_group'] == $this->config->item('mod_housing')) && !$act['parent']) ? $this->cachemodel->_fetch_room_information($location_id,"Комнаты / номерной фонд:") : '';
		$act['graphs'] = $this->cachemodel->_collect_graphs($location_id);
		$act['all_images']=$this->cachemodel->_location_images_collect($location_id); // перенести сюда
		// наполняем $act['content'] данными из хранилища параметров

		$act['content']="";
		$preoutput=array();
		$output=array();
		$icons = array();
		$icons['business']= '<img src="'.$this->config->item('api').'/images/icons/briefcase.png" width="16" height="16" border="0" alt="">&nbsp;&nbsp;&nbsp;';
		$icons['health']= '<img src="'.$this->config->item('api').'/images/icons/health.png" width="16" height="16" border="0" alt="">&nbsp;&nbsp;&nbsp;';
		$icons['services']= '<img src="'.$this->config->item('api').'/images/icons/service-bell.png" width="16" height="16" border="0" alt="">&nbsp;&nbsp;&nbsp;';
		$icons['other']= '<img src="'.$this->config->item('api').'/images/icons/information.png" width="16" height="16" border="0" alt="">&nbsp;&nbsp;&nbsp;';
		$icons['sport']= '<img src="'.$this->config->item('api').'/images/icons/sports.png" width="16" height="16" border="0" alt="">&nbsp;&nbsp;&nbsp;';
		$icons['sights']= '<img src="'.$this->config->item('api').'/images/icons/photo.png" width="16" height="16" border="0" alt="">&nbsp;&nbsp;&nbsp;';
		
		$this->db->query("SET group_concat_max_len = 8192");
		$result=$this->db->query("SELECT
		laz_properties_list.label,
		laz_properties_list.id,
		GROUP_CONCAT(
		CONCAT_WS('|', 
		IF(LENGTH(laz_properties_list.selfname) = 0, 0, laz_properties_list.selfname),
		laz_properties_list.cat,
		CASE
		WHEN laz_properties_list.fieldtype = 'checkbox' THEN laz_properties_list.selfname
		WHEN laz_properties_list.fieldtype = 'textarea' THEN laz_properties_assigned.value
		WHEN laz_properties_list.fieldtype = 'select' THEN laz_properties_list.selfname
		WHEN laz_properties_list.fieldtype = 'text' THEN (laz_properties_assigned.value % laz_properties_list.coef) END
		) ORDER BY laz_properties_list.cat SEPARATOR '^') AS content
		FROM
		laz_properties_assigned
		INNER JOIN laz_properties_list ON (laz_properties_assigned.property_id = laz_properties_list.id)
		INNER JOIN laz_locations ON (laz_properties_assigned.location_id = laz_locations.id)
		INNER JOIN `laz_locations_types` ON (laz_locations.`type` = `laz_locations_types`.id)
		WHERE
		(laz_properties_list.active = 1) AND
		(`laz_properties_assigned`.`property_id` <> `laz_locations_types`.`pl_num`) AND
		(`laz_locations`.id = ?)
		GROUP BY laz_properties_list.label
		ORDER BY
		`laz_properties_list`.`label` ASC,
		`laz_properties_list`.`selfname` ASC", array($location_id));
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
							array_push($output['встреча/проводы'],$data2[2]);
							break;
						}
						$icon = (isset($icons[$data2[1]])) ? $icons[$data2[1]] : "";
						$string = $icon.$data2[2];
						array_push($preoutput[$row->label],$string);
					}
				}
			}
		}
		//print_r($output);

		foreach($preoutput as $key=>$val){
			array_push($output,"<h3>".$key."</h3>").
			array_push($output,implode($val,"\n"));
		}
		$act['content']= implode($output,"\n<br>");
		$this->db->query("UPDATE 
		`laz_locations`
		SET
		`laz_locations`.`cache` = ?,
		`laz_locations`.`cache_date` = NOW()
		WHERE `laz_locations`.id = ?",array($this->load->view('frontend/std_view',$act, true),$location_id));
		$this->load->helper('file');
		if(!write_file('application/views/cache/locations/location_'.$location_id.".src", $this->load->view('frontend/std_view',$act, true),"w")){
			//print("всё фигня и лажа!");
		}
		if ($with_output){
			return $this->load->view('frontend/std_view',$act, true);
		}
	}

	function _collect_graphs($location_id){
		$graphs=Array();
		$currs=Array();
		$result=$this->db->query("SELECT 
		(SELECT laz_timers.price FROM laz_timers WHERE (NOW() BETWEEN laz_timers.start_point AND laz_timers.end_point) AND (laz_timers.`type` = 'price') AND `laz_timers`.`location_id` = laz_locations.id) as curpr,
		laz_locations.id,
		GROUP_CONCAT(CONCAT('[\'',DATE_FORMAT(laz_timers.start_point, '%e.%m'), '\', ', laz_timers.price,']\n' ) ORDER BY `laz_timers`.`start_point`) AS timestream
		FROM
		laz_timers
		INNER JOIN laz_locations ON (laz_timers.location_id = laz_locations.id)
		WHERE
		(laz_locations.parent = ?) AND 
		(laz_locations.active = 1) AND 
		(laz_timers.price > 0) AND 
		(laz_timers.start_point BETWEEN '".date("Y")."-01-01' AND '".(date("Y")+1)."-01-01')
		GROUP BY `laz_timers`.`location_id`
		ORDER BY
		laz_timers.location_id,
		`laz_timers`.`start_point`", array($location_id));
		if($result->num_rows()){
			foreach($result->result() as $row){
				$string = "mySrc[".$row->id."] = []; mySrc[".$row->id."] = new Array(['дата','цена'],".$row->timestream.");";
				array_push($graphs,$string);
				$string2="currs[".$row->id."] = '".$row->curpr." рублей';";
				array_push($currs,$string2);
			}
		}
		return "var currs = [];\n".implode($currs,"\n")."\nvar mySrc = [];\n".implode($graphs,"\n");
	}

	function _location_images_collect($location_id){
		$images=Array();
		$result=$this->db->query("SELECT
		laz_images.filename,
		laz_images.s128,
		laz_images.comment
		FROM laz_images
		WHERE
		laz_images.active = 1 AND
		laz_images.location_id = ?
		ORDER BY laz_images.`order`",$location_id);
		if($result->num_rows()){
			foreach($result->result() as $row){
				$photocomment = (strlen($row->comment)) ? $row->comment : "";
				$measures=explode(",",$row->s128);
				array_push($images,'<img class="imgstripe" data-toggle="modal" href="#modal_pics" src="/userimages/128/'.$row->filename.'" width='.$measures[0].' height='.$measures[1].' alt="'.$photocomment.'"><a href="/userimages/800/'.$row->filename.'" target="_blank">'.$photocomment.'</a>');
			}
		}else{
			array_push($images,'<a href="/userimages/800/nophoto.jpg" target="_blank"><img class="imgstripe" data-toggle="modal" href="#modal_pics" src="/userimages/128/nophoto.jpg" width=128 height=128 alt="фото"></a>');
		}
		return implode($images,"\n");
	}

	function _fetch_room_information($location_id=0,$header="&nbsp;"){
		if(!$location_id){
			return "";
		}else{
			$room_types=Array();
			####################################################### типы комнат
			$result=$this->db->query("SELECT 
			laz_properties_list.selfname,
			laz_properties_assigned.location_id
			FROM
			laz_properties_assigned
			INNER JOIN laz_properties_list ON (laz_properties_assigned.property_id = laz_properties_list.id)
			INNER JOIN laz_locations ON (laz_properties_assigned.location_id = laz_locations.id)
			WHERE
			(laz_locations.parent = ?) AND 
			(laz_properties_list.property_group = 'type')",$location_id);
			if($result->num_rows()){
				foreach($result->result() as $row){
					$room_types[$row->location_id]=$row->selfname;
				}
			}
			####################################################### цены
			####################################################### собираем табличку
			$out=array();
			$result=$this->db->query("SELECT 
			laz_locations.id,
			laz_locations.location_name,
			laz_locations_types.name,
			laz_locations_types.pl_num,
				(SELECT laz_images.filename FROM laz_images
				WHERE (laz_images.`order` <= 1) AND
				(laz_images.location_id = laz_locations.id) 
				LIMIT 1) AS img
			FROM
			laz_locations
			LEFT OUTER JOIN laz_locations_types ON (laz_locations.`type` = laz_locations_types.id)
			WHERE
			(laz_locations.parent = ?)", array($location_id));
			if($result->num_rows()){
				foreach($result->result() as $row){
					$image = (strlen($row->img)) ? $row->img : "nophoto.jpg";
					$type_name = ($row->pl_num) ? "(".$row->name.")" : "";
					$room_type = (isset($room_types[$row->id])) ? $room_types[$row->id] : "Класс номера не указан";
					$string = '<tr>
					<td class="first"><a class="roomlink" href="/page/show/'.$row->id.'"><img src="/userimages/128/'.$image.'" border="0" alt=""><br>'.$row->location_name.'<br>'.$room_type.'</a></td>
					<td class="second"><div class="chartfield" id="chart_div'.$row->id.'"></div></td>
					</tr>';
					array_push($out,$string);
				}
				$table=implode($out,"\n");
			}else{
				$result=$this->db->query("SELECT 
				laz_timers.price
				FROM
				laz_timers
				WHERE
				(NOW() BETWEEN laz_timers.start_point AND laz_timers.end_point) AND 
				(laz_timers.location_id = ?)", array($location_id));
				if($result->num_rows()){
					$row=$result->row(0);
					$table = '<tr><td class="prop_table_cell" colspan=2>стоимость размещения в данный момент составляет: '.$row->price.' рублей в сутки</td></tr>';
				}else{
					$table='<tr><td class="prop_table_cell" colspan=2>описание номерного фонда ещё не составлено или цена на размещение пока не указана</td></tr>';
				}
			}
			return '<table class="prices_table">
			<tr>
				<th colspan=2>'.$header.'</th>
			</tr>
			'.$table.'
			</table>';
		}
	}

	function _site_tree_build(){
		$links=Array();
		$result=$this->db->query("SELECT laz_sheets.header,
		CONCAT('/page/docs/',laz_sheets.id) as link,
		laz_sheets.`comment` 
		FROM 
		laz_sheets 
		WHERE 
		(laz_sheets.active = 1)");
		if($result->num_rows()){
			foreach($result->result() as $row){
				array_push($links, '<a title="'.htmlspecialchars($row->header).', '.htmlspecialchars($row->comment).'" href="'.$row->link.'/2">'.htmlspecialchars($row->header).'</a>');
			}
		}
		$result=$this->db->query("SELECT 
		laz_locations.location_name,
		laz_locations.address,
		laz_locations_types.name,
		CONCAT('/page/',`laz_objects_groups`.`function`,'/',laz_locations.id,'.html') as link
		FROM
		laz_locations
		INNER JOIN laz_locations_types ON (laz_locations.`type` = laz_locations_types.id)
		INNER JOIN `laz_objects_groups` ON (laz_locations_types.object_group = `laz_objects_groups`.id)
		WHERE
		(laz_locations.active = 1) AND 
		(LENGTH(laz_locations.location_name) > 0)");

		if($result->num_rows()){
			foreach($result->result() as $row){
				array_push($links, '<a title="'.htmlspecialchars($row->name).' '.htmlspecialchars($row->location_name).', '.htmlspecialchars($row->address).'" href="'.$row->link.'">'.$row->name.' '.htmlspecialchars($row->location_name).'</a>');
			}
		}
		$this->load->helper('file');
		write_file('application/views/cache/links/links_heap.php', implode($links,"<br>\n"));
	}

	function _menu_build($root=1,$housing_parent=0,$gis_obj_group=0){
		$this->load->helper('html');
		########################## GIS part ###########################
		$gis_tree = array();
		$result=$this->db->query("SELECT 
		CONCAT('<a href=\"#\"><i class=\"icon-tags\"></i>&nbsp;&nbsp;', laz_properties_list.property_group, '</a>') as property_group,
		CONCAT('<a href=\"/maps/type/', laz_locations_types.id, '\"><i class=\"icon-tag\"></i> ', laz_properties_list.selfname, '</a>') AS link
		FROM
		laz_properties_list
		LEFT OUTER JOIN laz_locations_types ON (laz_properties_list.id = laz_locations_types.pl_num)
		WHERE
		(laz_locations_types.object_group = ?) AND 
		(LENGTH(laz_properties_list.selfname) > 0)
		ORDER BY
		laz_properties_list.property_group",array($gis_obj_group));
		if($result->num_rows()){
			foreach($result->result() as $row){
				(!isset($gis_tree[$row->property_group])) ? $gis_tree[$row->property_group] = array() : "";
				array_push($gis_tree[$row->property_group],$row->link);
			}
		}
		$ans['gis']=ul($gis_tree,array('class' => 'dropdown-menu'));
		###############################################################
		########################## DOC part ###########################
		if($housing_parent){
			$housing = array();
			$result = $this->db->query("SELECT 
			laz_sheets.id,
			laz_sheets.header,
			IF(LENGTH(laz_sheets.redirect), laz_sheets.redirect, CONCAT('/page/docs/', CAST(laz_sheets.id as BINARY))) AS link,
			laz_sheets.comment,
			laz_sheets.parent
			FROM
			laz_sheets
			WHERE
			(laz_sheets.active = 1) and
			`laz_sheets`.`root` = 1
			ORDER by
			`laz_sheets`.`parent` DESC,
			`laz_sheets`.`pageorder`");
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
				$menu=implode($rr[0],"\n");
				foreach($rr as $key => $val){
					$menu = str_replace('<!--'.$key.'-->', implode($val,"\n"), $menu);
				}
			}
			$ans['housing'] = ul($housing,array('class' => 'dropdown-menu'));
		}else{
			$ans['housing'] = "";
		}
		$ans['rest'] = $menu;
		$ans['notepad'] = '';
		$this->load->helper('file');
		write_file('application/views/cache/menus/menu.php', $this->load->view('frontend/menu',$ans, true));
	}

	function _cache_selector_content($mapset=1){
		$this->load->helper('form');
		$result=$this->db->query("SELECT 
		laz_map_content.a_layers,
		laz_map_content.a_types
		FROM
		laz_map_content
		WHERE `laz_map_content`.`id` = ?",array($mapset));
		if($result->num_rows()){
			$map_content=$result->row();
		}

		$output=Array();
		$table=Array();

		if($map_content->a_layers){
			$query=$this->db->query("SELECT 
			laz_locations_types.name AS selfname,
			'checkbox' AS fieldtype,
			laz_locations_types.pl_num AS id,
			`laz_properties_list`.label
			FROM
			`laz_properties_list`
			INNER JOIN laz_locations_types ON (`laz_properties_list`.id = laz_locations_types.pl_num)
			WHERE
			(laz_locations_types.object_group = ?) AND 
			(laz_locations_types.pl_num > 0)
			ORDER BY
			laz_properties_list.selfname",array($map_content->a_layers));
			if($query->num_rows()){
				foreach ($query->result() as $row){
					if(!isset($output[111])){$output[111] = Array();}
					if(!isset($output[111][$row->id])){$output[111][$row->id]=Array();}
					$output[111][$row->id]['label']=$row->label;
					$output[111][$row->id]['name']=$row->selfname;
					$output[111][$row->id]['fieldtype']=$row->fieldtype;
					$output[111][$row->id]['group']="u"; //кандидат на исключение
				}
			}
		}

		if($map_content->a_layers){
			$where = "(laz_properties_list.object_group = ?) AND";
			$searchplace = $map_content->a_layers;
		}else{
			$where = "(laz_properties_list.id = (SELECT laz_locations_types.pl_num FROM laz_locations_types WHERE (laz_locations_types.id = ?))) AND";
			$searchplace = $map_content->a_types;
		}

		$query=$this->db->query('SELECT
			CONCAT(laz_properties_list.page, laz_properties_list.`row`, laz_properties_list.element) AS marker,
			laz_properties_list.label,
			laz_properties_list.selfname,
			laz_properties_list.algoritm as alg,
			laz_properties_list.fieldtype,
			laz_properties_list.id
			FROM
			laz_properties_list
			WHERE
			'.$where.'
			laz_properties_list.searchable = 1 AND
			laz_properties_list.active = 1
			ORDER BY
			laz_properties_list.label,
			laz_properties_list.selfname',array($searchplace));
		if($query->num_rows()){
			foreach ($query->result() as $row){
				if(!isset($output[$row->marker])){$output[$row->marker] = Array();}
				if(!isset($output[$row->marker][$row->id])){$output[$row->marker][$row->id]=Array();}
				if(!isset($output[$row->marker]['label'])){
					$output[$row->marker][$row->id]['label']=$row->label;
				}
				$output[$row->marker][$row->id]['name']=$row->selfname;
				$output[$row->marker][$row->id]['fieldtype']=$row->fieldtype;
				$output[$row->marker][$row->id]['group']=$row->alg;
				//['group'] исключать нельзя, ввиду неоднозначности трактовки элемента checkbox: как объединительный (И), так и объединительно-исключающий (ИЛИ) контекст поиска
			}
		}
		$query->free_result();
		$sws=array();
		foreach($output as $key => $val){
			$backcounter = sizeof($val);
			$ea = array();
			$values = array();
			foreach($val as $obj => $val3){
				#
				# Для селектора генерируются только поля типа: text, select и checkbox. 
				# Поле Textarea предназаначено исключительно для ввода больних текстов в админской консоли
				#
				if($obj == 'label'){
					continue;
				}
				$options = array();
				$element = array();
				if(!isset($val['label'])){ $val['label'] = $val3['label']; } // проброс параметра "наружу"
				switch ($val3['fieldtype']){
					case 'text' :
						array_push($element, '<li class="itemcontainer" obj="'.$obj.'"><input type="text">'.$val3['name']."</li>");
						array_push($sws, 'switches['.$obj.'] = { label: "'.$val['label'].'", value: "", fieldtype: "text", group: "'.$val3['group'].'", text: "'.form_prep($val3['name']).'" }');
					break;
					case 'select' :
						array_push($values, '<option value="'.$obj.'">'.$val3['name'].'</option>');
						--$backcounter;
						if(!$backcounter){
							array_push($element, '<li class="itemcontainer" obj="'.$obj.'"><select><option value="0">Выберите вариант</option>'."\n".implode($values,"\n").'</select></li>');
						}
						array_push($sws,'switches['.$obj.'] = { label: "'.$val['label'].'", value: 0, fieldtype: "select", group: "'.$val3['group'].'" , text: "'.form_prep($val3['name']).'" }');
					break;
					case 'checkbox' :
						$tabstr='<li class="itemcontainer" obj="'.$obj.'"><img src="'.$this->config->item('api').'/images/clean_grey.png" alt=" ">'.$val3['name'].'</li>';
						array_push($ea, $tabstr);
						array_push($sws, 'switches['.$obj.']= { label: "'.$val['label'].'", value: 0, fieldtype: "checkbox", group: "'.$val3['group'].'", text: "'.form_prep($val3['name']).'" }');
						--$backcounter;
						if (!$backcounter){
							array_push($element, "<ul>\n".implode($ea,"\n")."\n</ul>");
						}
					break;
				}
			}
			if(!strlen($val['label'])){$val['label']="&nbsp;";}
			array_push($table,'<div class="grouplabel" id="gl_'.$key.'">'."\n".$val['label']."\n</div>");
			array_push($table,'<div class="groupcontainer" id="gc_'.$key.'">'."\n".implode($element,"\n")."\n</div>"); # штатная генерация элементов
			######## конец переключателей
		}

		# пишем в файл (следите за путями)
		$this->load->helper('file');
		write_file('application/views/cache/menus/selector_'.$mapset.'.php', implode($table,"\n")."\n<script type=\"text/javascript\">\nvar switches = [];\n".implode($sws,";\n")."\n</script>");
	}







	function _build_object_lists(){
		$result=$this->db->query("SELECT 
		GROUP_CONCAT(CONCAT('<option value=\"',laz_locations_types.id,'\">',laz_locations_types.name,'</option>') SEPARATOR '') as `list`,
		`laz_locations_types`.`object_group`
		FROM
		laz_locations_types
		WHERE
		(laz_locations_types.pl_num <> 0)
		GROUP BY `laz_locations_types`.`object_group`");
		if($result->num_rows()){
			foreach ($result->result() as $row){
				write_file('application/views/cache/menus/typeslist_'.$row->object_group.'.php',$row->list);
			}
		}
	}
}
#
/* End of file cachemodel.php */
/* Location: ./application/models/cachemodel.php */