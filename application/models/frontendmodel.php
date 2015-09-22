<?php
class Frontendmodel extends CI_Model{
	function __construct(){
		parent::__construct();
		$this->load->helper('form');
	}

	public function get_properties($location_id){ // CI_C_Gis
		$result=$this->db->query("SELECT 
		CONCAT_WS(' ', locations_types.name, locations.location_name) AS name,
		locations.`parent`,
		locations.`type`,
		locations.`coord_y`,
		locations.`comments`,
		locations_types.`has_child`,
		`objects_groups`.`function`
		FROM
		locations_types
		INNER JOIN locations ON (locations_types.id = locations.`type`)
		INNER JOIN `objects_groups` ON (locations_types.object_group = `objects_groups`.id)
		WHERE
		(locations.id = ?)", array($location_id));
		if($result->num_rows()){
			foreach ($result->result_array() as $row){
				return $row;
			}
		}
	}

	public function get_cached_content($location_id){ // CI_C_Gis проверить работу - каждый раз готовит кэш заново.
		//$this->output->enable_profiler(TRUE);
		$output = "";
		$cachefile = "/var/www/html/inmypocket/application/views/cache/locations/location_".$location_id.".src";
		if(file_exists($cachefile)){
			$output = file_get_contents($cachefile);
		}else{
			$output = $this->cachemodel->cache_location($location_id, 1);
		}
		return $output;
	}

	function _menu_build($root = 1, $housing_parent = 0, $gis_obj_group = 0){
		$this->load->helper('html');
		########################## GIS part ###########################
		$gis_tree = array();
		$result=$this->db->query("SELECT 
		CONCAT('<a href=\"#\">',properties_list.property_group,'</a>') AS property_group,
		CONCAT('<a href=\"/page/minigis/', locations_types.id, '\">', properties_list.selfname, '</a>') AS link
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
				if (!isset($gis_tree[$row->property_group])) {
					$gis_tree[$row->property_group] = array();
				}
				array_push( $gis_tree[$row->property_group], $row->link );
			}
		}
		$ans['gis'] = ul($gis_tree);
		###############################################################
		########################## DOC part ###########################
		if($housing_parent){
			$housing = array();
			$result=$this->db->query("SELECT 
			sheets.id,
			sheets.header,
			IF(LENGTH(sheets.redirect), sheets.redirect, CONCAT('/page/docs/', CAST(sheets.id as BINARY))) AS link,
			sheets.parent
			FROM
			sheets
			WHERE
			(sheets.active = 1) and
			`sheets`.`root` = 1
			ORDER by
			`sheets`.`parent` ASC,
			`sheets`.`pageorder`");
			if($result->num_rows()){
				$rr = array();
				foreach($result->result() as $row){
					if(!isset($rr[$row->parent])) {
						$rr[$row->parent] = array();
					}
					if($row->parent == $housing_parent){
						//(!isset($housing[$row->parent])) ? $housing[$row->parent]=array() : "";
						array_push($housing,'<a href="'.$row->link.'">'.$row->header.'</a>');
					}else{
						if (!isset($rr[$row->parent])) {
							$rr[$row->parent] = array(); 
						}
						array_push($rr[$row->parent],'<li><a href="'.$row->link.'">'.$row->header.'</a><!--'.$row->id.'--></li>');
					}
				}
				$menu=implode($rr[0],"\n");
				foreach($rr as $key => $val){
					$menu=str_replace('<!--'.$key.'-->','<ul>'.implode($val,"\n").'</ul>',$menu);
				}
			}
			$ans['housing']=ul($housing);
		}else{
			$ans['housing']="";
		}
		$ans['rest']=$menu;
		$ans['notepad']=$this->load->view($this->session->userdata('lang').'/frontend/frontend_notepad_etc',$ans, true);
		return $this->load->view($this->session->userdata('lang').'/frontend/menu',$ans, true);
	}

	function traverse_sheet_tree($doc_id){
		$traverse_string=array();
		$result=$this->db->query("SELECT 
		`sheets`.id,
		`sheets`.header,
		`sheets`.parent
		FROM
		`sheets`
		WHERE `sheets`.`id` = ?", array($doc_id));
		if($result->num_rows()){
			$row = $result->row();
			array_push($traverse_string,'<li><a href="/page/docs/'.$row->id.'">'.$row->header.'</a></li>');
			$itemclass = ($row->id == $doc_id) ? ' class="active" ' : "";
			if($row->id >= 1){
				$result=$this->db->query("SELECT 
				`sheets`.id,
				`sheets`.header,
				`sheets`.parent
				FROM
				`sheets`
				WHERE `sheets`.`id` = ?", array($row->parent));
				if($result->num_rows()){
					$row=$result->row();
					
					array_push($traverse_string,'<li><a href="/page/docs/'.$row->id.'">'.$row->header.'</a></li>');
				}
			}
		}
		array_push($traverse_string,'<li'.$itemclass.'><a href="/">'.$this->config->item('site_friendly_url').'</a></li>');
		return '<!-- bc --><ul class="breadcrumb">'.implode(array_reverse($traverse_string), '<span class="divider">&nbsp;/&nbsp;</span>').'</ul><!-- bc -->';
	}
	
	function show_doc($doc_id){
		$result = $this->db->query("SELECT 
		`sheets`.header,
		`sheets`.text,
		`sheets`.redirect,
		`sheets`.date
		FROM
		`sheets`
		WHERE `sheets`.`id` = ?",array($doc_id));
		if($result->num_rows()){
			$row = $result->row_array();
			if(strlen($row['redirect'])){
				$this->load->helper('url');
				redirect($row['redirect']);
			}
			$row['doc_traverse'] = $this->traverse_sheet_tree($doc_id);
			return $this->load->view($this->session->userdata('lang').'/frontend/doc_view', $row, true);
		}else{
			return "Запрошенного Вами документа не существует.";
		}
	}

	function _show_catalog($doc_id){
		$result=$this->db->query("SELECT `sheets`.id, `sheets`.header FROM `sheets` WHERE `sheets`.`parent` = ?",array($doc_id));
		$cat_entries=Array();
		if($result->num_rows()){
			array_push($cat_entries,'<ul class="checks">');
			foreach($result->result() as $row){
				array_push($cat_entries,'<li><a href="/page/docs/'.$row->id.'">'.$row->header.'</a></li>');
			}
			array_push($cat_entries,"</ul>");
			return implode($cat_entries,"\n");
		}else{
			return "Страницы для каталогизации не найдены.";
		}
	}

	function _stripe_aggregation_make($property,$comment=105){
		$out=array();
		$result = $this->db->query("SELECT 
		CONCAT('/userimages/128/', images.filename, '|', images.s128) AS img,
		CONCAT('page/show/',images.location_id) as location_id,
		CONCAT_WS(' ',locations_types.name,locations.location_name) AS `location_name`
		FROM
		users_admins
		INNER JOIN images ON (users_admins.uid = images.owner_id)
		INNER JOIN locations ON (images.location_id = locations.id)
		INNER JOIN locations_types ON (locations.`type` = locations_types.id)
		WHERE
		(images.`order` <= 1) AND 
		(users_admins.active = 1) AND 
		(locations.active = 1)
		ORDER BY
		RAND()
		LIMIT 10",array($property,$comment));
		if($result->num_rows){
			array_push($out,'<UL class="slideshow" width="450" height="180">');
			foreach($result->result() as $row){
				$imageset=explode("|",$row->img);
				$image = $imageset[0];
				$img_dim_wh = isset($imageset[1]) ? explode(",",$imageset[1]) : array(128,128);
				$string='<li><a href="'.$row->location_id.'"><img class="caption" title="'.$row->location_name.'" src="'.$image.'" width="'.$img_dim_wh[0].'" height="'.$img_dim_wh[1].'" border="0"></a></li>';
				array_push($out,$string);
				
			}
			array_push($out,"</UL>");
		}else{
			array_push($out,"Коллекции почему-то не получилось");
		}
		return implode($out,"\n");
	}

	function _sheet_aggregation_make($sheet){
		$result=$this->db->query("SELECT 
		`sheets`.ts as date,
		`sheets`.`text`,
		`sheets`.header
		FROM
		`sheets`
		where sheets.id = ?",array($sheet));
		if($result->num_rows()){
			$row=$result->row_array();
			$row['doc_traverse']=$this->_traverse_sheet_tree($sheet);
			$page=$this->load->view($this->session->userdata('lang').'/frontend/doc_view',$row, true);
		}else{
			$page="Страница куда-то потерялась.";
		}
		return $page;
	}

	function aggregation_page_build(){
		$act['text1'] = '';			//$this->_sheet_aggregation_make(1);
		$act['special_offer'] = "";	//$this->_stripe_aggregation_make(34,105);
		$act['notes'] = $this->session->userdata('note');
		return $this->load->view($this->session->userdata('lang').'/frontend/main_page_content', $act, true);
	}
	############ GIS ##############
	###
	###############################

	function _description_get_id($location_id=0){
		$result=$this->db->query("SELECT 
		properties_list.id
		FROM
		`locations`
		INNER JOIN `locations_types` ON (`locations`.`type` = `locations_types`.id)
		INNER JOIN properties_list ON (`locations_types`.object_group = properties_list.object_group)
		WHERE
		(`locations`.`id` = ?) AND
		(properties_list.cat = 'description') AND 
		(properties_list.property_group = 'description')", Array($location_id));
		//print $result->num_rows()."<br>";
		if($result->num_rows()){
			$row=$result->row();
			return $row->id;
		}else{
			//print "Для группы объектов указанного объекта ГИС - (".$obj_group.") в базе отсутствует резервированное поле описания";
			return FALSE;
		}
	}

	function _gis_types_get(){
		$result=$this->db->query("SELECT locations_types.attributes, locations_types.id FROM locations_types");
		$gis_types=Array();
		array_push($gis_types,"var gis_types= [];");
		if($result->num_rows()){
			foreach($result->result() as $row){
				array_push($gis_types,"gis_types[".$row->id."]= '".$row->attributes."';");
			}
		}
		return implode($gis_types,"\n");
	}

	function _gis_object_save($location_id){
		$desc_id=$this->_description_get_id($location_id);
		$this->db->query("UPDATE
		`locations`
		SET
		`locations`.`location_name` = ?,
		`locations`.`address` = ?,
		`locations`.`contact_info` = ?,
		`locations`.`coord_y` = ?
		where
		`locations`.id = ?", array(
			$this->input->post('q_gis_locname'),
			$this->input->post('q_gis_address'),
			$this->input->post('q_gis_contact'),
			$this->input->post('yandex_coords'),
			$location_id));
		$this->db->query("DELETE
		FROM
		`properties_assigned`
		WHERE
		(`properties_assigned`.location_id = ?) AND 
		(`properties_assigned`.property_id = ?)", array($location_id,$desc_id));
		$this->db->query("INSERT INTO `properties_assigned` (
			`properties_assigned`.location_id,
			`properties_assigned`.`property_id`,
			`properties_assigned`.`value`
		)VALUES(?,?,?)", array($location_id,$desc_id,$this->input->post('q_gis_editor')));
	}

	function _gis_object_get($location_id=0,$edit=0){
		$desc_id=$this->_description_get_id($location_id);
		$result=$this->db->query("SELECT 
		locations.location_name,
		locations_types.name,
		locations.contact_info,
		locations.address,
		locations.coord_y
		FROM
		locations_types
		INNER JOIN locations ON (locations_types.id = locations.`type`)
		WHERE
		(locations.id = ?)",Array($location_id));
		if($result->num_rows()){
			$row = $result->row_array();
			$row['description']="Описание не найдено. Возможно, его подготовят в ближайшем будущем.";
			$result2=$this->db->query("SELECT 
			`properties_assigned`.value
			FROM
			`properties_assigned`
			WHERE 
			`properties_assigned`.`location_id` = ? AND
			`properties_assigned`.`property_id` = ?",array($location_id,$desc_id));
			if($result2->num_rows()){
				$row2=$result2->row();
				if(strlen($row2->value)){
					$row['description']=$row2->value;
				}
			}
			$result3=$this->db->query("SELECT 
			images.filename,
			images.s128,
			images.`comment`
			FROM
			images
			WHERE
			(images.location_id = ?) AND 
			(images.active = 1)
			ORDER BY
			images.`order`",$location_id);
			$images=Array();
			if($result3->num_rows()){
				foreach($result3->result() as $row3){
					$measures = explode(",",$row3->s128);
					array_push($images,'<a href="/userimages/800/'.$row3->filename.'"><IMG title="'.$row3->comment.'" SRC="/userimages/128/'.$row3->filename.'" WIDTH='.$measures[0].' HEIGHT='.$measures[1].' /></a>');
				}
			}else{
				array_push($images,'<a href="/userimages/800/nophoto.jpg"><IMG title="фотографий нет" class="caption" SRC="/userimages/128/nophoto.jpg" WIDTH="128" HEIGHT="128"></a>');
			}
			$row['images']=implode($images,"\n");
			$row['yandex_key']=$this->config->item('yandex_key');
			$row['location']=$location_id;
			/*if($edit){
				$row['photo']=$this->adminmodel->_show_location_images($location_id,4,"qgis");
				$row['map']=$this->frontendmodel->_show_map($location_id); // взять подходящий из ajax.php или админки
				return $this->load->view('frontend/location_quickgis_view',$row,true);
			}else{*/
			return $this->load->view($this->session->userdata('lang').'/frontend/location_gis_view',$row,true);
			//}
		}else{
			return "Данные по запрошенному объекту не найдены";
		}
	}

	function _show_map($location_id){
		$jsarray=Array();
		array_push($jsarray,"var objects = [];");
		array_push($jsarray,"var object = [];");
		$result=$this->db->query('SELECT 
		CONCAT(locations_types.name, \' "\', locations.location_name, \'"\') AS location_name,
		locations.coord_y,
		locations.parent,
		locations_types.attributes,
		locations_types.id AS type_id,
		locations1.id,
		locations.id AS `location_id`
		FROM
		locations_types
		RIGHT OUTER JOIN locations ON (locations_types.id = locations.`type`)
		LEFT OUTER JOIN `locations` locations1 ON (locations1.`type` = locations_types.id)
		WHERE
		(locations1.id = ?) AND 
		(locations.owner = ?)', array($location_id,$this->session->userdata('user_id')));
		if($result->num_rows()){
			foreach($result->result() as $row){
				if($location_id == $row->location_id){
					$string = "object.push({coord : '".$row->coord_y."', description : '".$row->location_name."', style : 'user#curbuilding'});";
					array_push($jsarray, $string);
				}else{
					if(!$row->parent){
						$string = "objects[".$row->location_id."] = { description : '".$row->location_name."', coord : '".$row->coord_y."', style : '".$row->attributes."' };";
						array_push($jsarray, $string);
					}
				}
			}
		}
		$act['yandex_key']=$this->config->item('yandex_key');
		$act['maps_center']=$this->config->item('maps_center');
		$act['objects']=implode($jsarray,"\n");
		return $this->load->view($this->session->userdata('lang').'/frontend/qgis_locations_map',$act,true);
	}
	########

	function _map_parameters_get($mapset=0,$type=0){
		$header="Полная карта";
		if ($mapset) {
			$result=$this->db->query("SELECT
			CONCAT(
			IF(map_content.a_layers > 0, objects_groups.name, locations_types.name),
			' - ',
			(SELECT
			CONCAT('<small>', `sheets`.`header`, ' ', `sheets`.`comment`,'</small>')
			FROM `sheets`
			WHERE `sheets`.`redirect` = CONCAT('/page/map/',`map_content`.`id`))
			) AS `name`
			FROM
			map_content
			LEFT OUTER JOIN objects_groups ON (map_content.a_layers = objects_groups.id)
			LEFT OUTER JOIN locations_types ON (map_content.a_types = locations_types.id)
			WHERE
			(map_content.id = ?)", array($mapset));
			if($result->num_rows()){
				$row = $result->row(0);
				$header = $row->name;
			}
		}
		if ($type) {
			$result=$this->db->query("SELECT 
			`locations_types`.name
			FROM
			`locations_types`
			WHERE
			`locations_types`.`id` = ?", array($type));
			if($result->num_rows()){
				$row = $result->row(0);
				$header = "<small>Объекты с меткой</small>&nbsp;".$row->name;
			}
		}
		return $header;
	}

	function _gallery_build($obj_group=1,$type=0){
		$yandex_key = $this->config->item('yandex_key');
		$this->load->helper('url');
		$output = array();
		$result=$this->db->query("SELECT 
		GROUP_CONCAT(CONCAT(images.filename,'|',images.s128,'|',images.`comment`) SEPARATOR ';') as imgstr,
		images.location_id,
		locations.contact_info,
		locations.address,
		locations.coord_y,
		CONCAT_WS(' ',IF(`locations_types`.`pl_num` = 0, '',`locations_types`.name),locations.location_name) AS location_name
		FROM
		images
		LEFT OUTER JOIN locations ON (images.location_id = locations.id)
		LEFT OUTER JOIN users_admins ON (locations.owner = users_admins.uid)
		LEFT OUTER JOIN `locations_types` ON (locations.`type` = `locations_types`.id)
		WHERE
		(images.active = 1) AND 
		(locations.active = 1) AND 
		(users_admins.active = 1) and
		locations_types.object_group = ?
		GROUP BY `locations`.`id`
		HAVING LENGTH(imgstr) > 0
		ORDER BY
		users_admins.rating DESC,
		`locations_types`.`name`,
		`locations`.`location_name`",array($obj_group));
		if($result->num_rows()){
			foreach($result->result() as $row){
				$namestring = '<h5><a href="/page/show/'.$row->location_id.'"><img src="/images/icons/house.png" width="16" height="16" border="0" alt="" style="vertical-align:middle;">&nbsp;&nbsp;'.$row->location_name.'</h5></a><ADDRESS>'.$row->address.'</ADDRESS><img src="/images/icons/telephone.png" width="16" height="16" border="0" alt="" style="vertical-align:middle;">&nbsp;<img src="/images/icons/contact_email.png" width="16" height="16" border="0" alt="" style="vertical-align:middle;">&nbsp;&nbsp;'.$row->contact_info."";
				$images = explode(";",$row->imgstr);
				$out_images=array();
				foreach($images as $key => $val){
					$image=explode("|",$val);
					$dimensions = (isset($image[1])) ? explode(',',$image[1]) : explode(',','128,128');
					$class = (sizeof($out_images) > 4) ? ' class="hide"' : '';
					$string='<a href="/userimages/800/'.$image[0].'"><img '.$class.'src="/userimages/128/'.$image[0].'" width="'.$dimensions[0].'" height="'.$dimensions[1].'" /></a>';
					array_push($out_images,$string);
				}
				array_push($output,$namestring.'<div class="gallery">'.implode($out_images,"\n").'</div>');
			}
		}else{
			array_push($output,"Почему-то в эту галерею не нашлось ни одного экспоната.");
		}
		return '<!-- <H3>Предложений в этой галерее: '.sizeof($output).'</H3> -->'.implode($output,"\n<hr>\n");
		//print_r($output);
	}

	#### notepad :)
	function _noted_show(){
		$tabheader = array();
		$tabs = array();
		$locations = $this->session->userdata('noted');
		if(!sizeof($locations)){
			return "Блокнот пока пуст";
		}
		$result=$this->db->query("SELECT 
		locations.id,
		locations.cache
		FROM
		locations
		WHERE
		(locations.id IN (".implode($locations,",")."))");
		if($result->num_rows()){
			$i = 0;
			foreach($result->result() as $row){
				array_push($tabheader,'<li><a href="#tabr'.$row->id.'">'.++$i.'</a></li>');
				array_push($tabs,'<div id="tabr'.$row->id.'" class="tab-content">'.$row->cache.'</div>');
			}
		}
		return '<ul class="tabs left">'.implode($tabheader,"").'</ul>'.implode($tabs,"");
	}

	public function comments_show($location_id = 0){
		if(!$location_id){
			return "";
		}
		$comments=Array();
		$result=$this->db->query("SELECT 
		comments.`hash` as id,
		comments.auth_name,
		comments.contact_info,
		comments.text,
		DATE_FORMAT(comments.date,'%d.%m.%Y') as date,
		comments.status,
		comments.uid
		FROM
		comments
		WHERE comments.location_id = ?",array($location_id));
		if($result->num_rows()){
			foreach($result->result_array() as $row){
				($this->session->userdata('common_user') == $row['uid']) ? $row['status'] = "A" : "";
				$row['control'] = "";
				($row['status'] == "A") ? array_push($comments, $this->load->view('fragments/comment_layout', $row, true)) : "";
			}
		}
		$act['comments'] = (sizeof($comments)) ? implode($comments,"<BR>\n") : "<h1><small>Пока здесь тихо</small></h1>";
		$act['location_id'] = $location_id;
		$act['captcha']=$this->usefulmodel->_captcha_make();
		return $this->load->view($this->session->userdata('lang').'/frontend/comments', $act, true);
	}
}
/* End of file frontendmodel.php */
/* Location: ./system/application/models/frontendmodel.php */