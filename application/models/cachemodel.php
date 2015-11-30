<?php
class Cachemodel extends CI_Model{
	function __construct(){
		parent::__construct();
		$this->load->helper('file');
	}

	private function write_gis_menu($result, $lang, $groups, $categories){
		$gis_tree      = array();
		foreach ($result->result() as $row) {
			$groupname = (isset($groups[$row->group_id]) && strlen($groups[$row->group_id][$lang]))       ? $groups[$row->group_id][$lang]    : $row->groupname;
			$itemname  = (isset($categories[$row->type_id]) && strlen($categories[$row->type_id][$lang])) ? $categories[$row->type_id][$lang] : $row->itemname;
			$grouplink = '<a href="/map/group/'.$row->group_id.'"><i class="icon-tags"></i>&nbsp;&nbsp;'.$groupname."</a>";
			$itemlink  = '<a href="/map/type/'.$row->type_id.'"><i class="icon-tag"></i>&nbsp;&nbsp;'.$itemname."</a>";
			if (!isset($gis_tree[$grouplink])) {
				$gis_tree[$grouplink] = array();
			}
			array_push($gis_tree[$grouplink], $itemlink);
		}
		write_file('application/views/cache/menus/src/menu_'.$lang.'.php', ul($gis_tree, array('class' => 'dropdown-menu')));
	}
	
	private function cache_gis_part($gisroot = 0) {
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
			$groups        = $this->config->item("groups");
			$categories    = $this->config->item("categories");
			$langs  = $this->config->item('lang');
			foreach ($langs as $lang => $lang_name) {
				$this->write_gis_menu($result, $lang, $groups, $categories);
			}
		}
	}

	private function cache_docs($root = 1, $mode = 'file'){
		$this->config->load('translations_a', false);
		$langs    = $this->config->item('lang');
		$articles = $this->config->item('articles');
		$result   = $this->db->query("SELECT
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
				//if($mode === 'file') {
					write_file('application/views/cache/menus/docs_'.$lang.'.php', ul($list, array('class' => 'dropdown-menu')));
				//} else {
				//	print '<link href="http://api.korzhevdp.com/css/frontend.css" rel="stylesheet" media="screen" type="text/css">'.ul($list, array('class' => 'dropdown-menu'))."<hr>";
				//}
			}
		}
	}

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
		`properties_list`.label,
		'u' AS alg
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
		return $this->pack_properties($result);
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
		return $this->pack_properties($result);
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
		properties_bindings
		RIGHT OUTER JOIN properties_list ON (properties_bindings.property_id = properties_list.id)
		WHERE
		properties_bindings.groups IN (".$layers.")
		AND properties_list.id NOT IN (
			SELECT locations_types.pl_num FROM locations_types WHERE locations_types.id IN (
				SELECT locations_types.id FROM locations_types WHERE locations_types.object_group IN (".$layers.")
			)
		)
		AND `properties_bindings`.`searchable`
		AND properties_list.active
		ORDER BY
		marker,
		properties_list.label,
		properties_list.selfname");
		return $this->pack_properties($result);
	}

	private function pack_properties($result){
		$output = array();
		if($result->num_rows()){
			foreach ($result->result() as $row) {
				if (!isset($output[$row->marker])) { $output[$row->marker] = array(); }
				if (!isset($output[$row->marker][$row->label])) { $output[$row->marker][$row->label] = array(); }
				$output[$row->marker][$row->label][$row->id] = array(
					'name'       => $row->selfname,
					'fieldtype'  => $row->fieldtype,
					'alg'        => $row->alg
				);
			}
		}
		return $output;
	}
	
	private function generate_selector($src, $map, $mode){
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
						if (!isset($element['alg'])) { $element['alg'] = "u"; }
						$element['name'] = (isset($properties[$object_id]) && strlen($properties[$object_id][$lang])) ? $properties[$object_id][$lang] : $element['name'];
						switch ($element['fieldtype']){
							case 'text':
								$string = '<li class="itemcontainer"><input type="text" class="itemtext" obj="'.$object_id.'">'.$element['name']."</li>";
								array_push($htmlcontrol, $string);
							break;
							case 'select':
								$string = '<option value="'.$object_id.'">'.$element['name'].'</option>';
								array_push($values, $string);
								--$backcounter;
								if($backcounter === 0) {
									array_unshift($values, '<option value="0" selected="selected"> - - - </option>');
									$string = '<li class="itemcontainer"><select class="itemselect" obj="'.$object_id.'">'."\n".implode($values,"\n").'</select></li>';
									array_push($htmlcontrol, $string);
								}
							break;
							case 'checkbox':
								$string = '<li class="itemcontainer itemcheckbox" obj="'.$object_id.'"><img src="'.$this->config->item('api').'/images/clean_grey.png" alt=" ">'.$element['name'].'</li>';
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
			}
			$content = implode($table, "\n");
			if ($mode === "file") {
				write_file('application/views/cache/selectors/selector_'.$map.'_'.$lang.'.php', $content);
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
						$sws[$object_id] = $object_id.': { value: 0, fieldtype: "'.$element['fieldtype'].'", alg: "'.$element['alg'].'", text: "'.$element['name'].'" }';
					}
				}
			}
			$content = "switches = {\n\t".implode($sws, ",\n")."\n}";
			if ($mode === "file") {
				write_file('application/views/cache/selectors/selector_'.$map.'_switches_'.$lang.'.php', $content);
			}
		}
	}
	//кэширование навигатора

	private function return_refgroups($val) {
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
		return implode($refgroups, ",");
	}

	private function generate_selector_content($val, $map, $mode) {
		$output     = array();
		$refgroups  = $this->return_refgroups($val);
		if (strlen($val[0]) && $val[0] != 0) {
			$output = $this->cache_selector_layers($val[0]);
		}
		if (strlen($val[1]) && $val[1] != 0) {
			$output = array_merge($output, $this->cache_selector_types($val[1]));
		}
		if ($val[0] == "0" && strlen($val[1]) && $val[1] != 0) {
			$output = array_merge($output, $this->cache_selector_properties($refgroups));
		}
		$this->generate_selector($output, $map, $mode);
		$this->generate_switches($output, $map, $mode);
	}

	public function cache_selector_content($mode = "file") {
		$output      = array();
		$table       = array();
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
		foreach($map_content as $map => $val) {
			$this->generate_selector_content($val, $map, $mode);
		}
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