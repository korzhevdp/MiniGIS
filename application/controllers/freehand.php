<?php
class Freehand extends CI_Controller {
	function __construct(){
		parent::__construct();
		//$this->output->enable_profiler(TRUE);
		//$this->output->cache(10);
		$this->load->model('usefulmodel');
		if(!$this->session->userdata('common_user')){
			$this->session->set_userdata('common_user', md5(rand(0,9999).'zy'.$this->input->ip_address()));
		}
		if(!$this->session->userdata('noted')){
			$this->session->set_userdata('noted', array());
		}
		if(!$this->session->userdata('objects')){
			$this->session->set_userdata('objects', array());
		}
		if(!$this->session->userdata('map')){
			$this->map_init();
		}
		if(!$this->session->userdata('gcounter')){
			$this->session->set_userdata('gcounter', 1);
		}
	}

	public function index($hash = ""){
		$this->map($hash);
	}

	function map($hash = ""){
		$data = $this->session->userdata('map');
		//$this->session->set_userdata('map',array('id' => 'new'));
		//$act['yandex_key']=$this->config->item('yandex_key');
		//if(!strlen($hash) && $data['id']=='void'){
		//	print 1;
		//}
		$act = array(
			'maps_center'	=> (is_array($data['center'])) ? implode($data['center'], ",") : '',
			'maptype'		=> $data['maptype'],
			'zoom'			=> $data['zoom'],
			'keywords'		=> $this->config->item('maps_keywords'),
			'title'			=> $this->config->item('site_title_start')." Интерактивная карта 0.3b",
			'gcounter'		=> $this->session->userdata('gcounter'),
			'userid'		=> $this->session->userdata('common_user'),
			'menu'			=> $this->load->view('cache/menus/menu', array(), TRUE),
			'navigator'		=> $this->load->view('freehand/navigator', array(), TRUE),
			'header'		=> $this->load->view('frontend/page_header', array(), TRUE),
			'footer'		=> $this->load->view('frontend/page_footer', array(), TRUE),
			'map_header'	=> 'Свободная карта',
			'maphash'		=> str_replace(' ','',substr($hash, 0, 16)),
			'notepad'		=> '',
			'links_heap'	=> ''
		);
		$this->load->view('freehand/freehand_map', $act);
	}

	public function logindata(){
		if(!$this->input->post('token')){
			$this->load->helper('url');
			redirect("freehand");
		}
		$link = "http://loginza.ru/api/authinfo?token=".$this->input->post('token')."&id=70969&sig=".md5($this->input->post('token').'b8c8b99c759d5ad3edc5882559ba359c');
		$data = json_decode(file_get_contents($link));
		if(isset($data->identity)){
			$file  = "/var/www/html/luft/shadow";
			$passwd = file($file);
			$name  = "";
			$found = 0;
			$name .= (isset($data->name->first_name)) ? $data->name->first_name : "";
			$name .= (isset($data->name->last_name))  ? " ".$data->name->last_name : "";
			$fname = (isset($data->name->full_name))  ? (isset($data->name->full_name)) : "Временный поверенный";
			$this->session->set_userdata('photo', ((isset($data->photo)) ? '<img src="'.$data->photo.'" style="width:16px;height:16px;border:none" alt="">' : ""));
			$name  = (!strlen($name)) ? $fname : $name;
			$this->session->set_userdata('supx', 0);
			# Session is not set. Checking rights
			if(!$this->session->userdata('uid1')){
				$this->session->set_userdata('uid1', md5(strrev($data->identity)));
				$this->session->set_userdata('uidx', substr(strrev($this->session->userdata('uid1')), 0, 10));
				$this->session->set_userdata('suid', md5($name));
				$this->session->set_userdata('name', $name);

				foreach($passwd as $user){
					$data = explode(",", $user);
					if($data[0] == $this->session->userdata('uid1')){
						$this->session->set_userdata('supx', $data[1]);
						$found++;
					}
				}
			}else{
				$this->session->set_userdata('uid1', md5(strrev($data->identity)));
				$this->session->set_userdata('suid', md5($name));
				$this->session->set_userdata('name', $name);
				foreach($passwd as $user){
					$data = explode(",", $user);
					if($data[0] == $this->session->userdata('uid1')){
						$found++;
					}
					if($this->session->userdata('uidx') == $data[3]){
						$this->session->set_userdata('supx', $data[1]);
					}
				}
			}
			// активирован - пишем в файл
			if(!$found){
				$string = array($this->session->userdata('uid1'), $this->session->userdata('supx'), $this->session->userdata('name'), $this->session->userdata('uidx'));
				$open   = fopen($file, "a");
				fputs($open, implode($string, ",")."\n");
				fclose($open);
			}
			$this->load->helper('url');
			redirect("freehand");
		}else{
			print 'Логин не удался. Вернитесь по ссылке и попробуйте ещё раз<br><br><a href="http://maps.korzhevdp.com/freehand">Вернуться на http://maps.korzhevdp.com/freehand</a>';
			//header("Location: http://luft.korzhevdp.com")
		}
	}

	public function getuserdata(){
		if($this->session->userdata('uid1')){
			$title = ($this->session->userdata('supx')) ? "Ваши загруженные фотографии публикуются сразу" : "Ваши загруженные фотографии просмотрит модератор";
			print "['".$this->session->userdata('name')."', '".$this->session->userdata('photo')."', '".$title."']";
		}else{
			print "['Гость', '', 'После авторизации Вы можете загружать фото']";
		}
	}

	public function getmaps(){
		$result = $this->db->query("SELECT 
		`usermaps`.hash_a,
		`usermaps`.hash_e,
		`usermaps`.public,
		`usermaps`.name
		FROM
		`usermaps`
		WHERE `usermaps`.`author` = ?
		ORDER BY usermaps.id DESC", array($this->session->userdata('uidx')));
		if($result->num_rows()){
			$output = array();
			foreach($result->result() as $row){
				$public = ($row->public) ? ' checked="checked"' : "";
				$string = '<tr>
					<td><input type="text" class="userMapName" ref="'.$row->hash_a.'" name="'.$row->hash_a.'[]" value="'.$row->name.'"></td>
					<td>
						<img src="http://api.korzhevdp.com/images/map.png" width="16" height="16" border="0" alt="">
						<a  href="http://maps.korzhevdp.com/freehand/map/'.$row->hash_a.'" title="Нередактируемая карта">'.$row->hash_a.'</a><br>
						<img src="http://api.korzhevdp.com/images/map_edit.png" width="16" height="16" border="0" alt="">
						<a  href="http://maps.korzhevdp.com/freehand/map/'.$row->hash_e.'" style="color:red" title="Редактируемая карта">'.$row->hash_e.'</a></td>
					<td>
						<center><input type="checkbox" class="userMapPublic" ref="'.$row->hash_a.'" name="'.$row->hash_a.'[]"'.$public.'></center>
					</td>
					<td>
						<button class="userMapNameSaver btn" ref="'.$row->hash_a.'"><i class="icon-tag"></i></button>
					</td>
				</tr>';
				array_push($output, $string);
			}
			print implode($output, "\n");
		}else{
			print "<tr><td colspan=3>Созданных вами карт не найдено</td></tr>";
		}
	}

	public function savemaps(){
		//$this->output->enable_profiler(TRUE);
		$data = $this->input->post();
		//print_r($data);
		foreach($data as $hash_a => $val){
			$name   = (isset($val[0])) ? $val[0] : "";
			$public = (isset($val[1])) ? 1 : 0;
			$result = $this->db->query("UPDATE
			usermaps
			SET
			usermaps.name   = IF(usermaps.author = ?, ?, usermaps.name),
			usermaps.public = IF(usermaps.author = ?, ?, usermaps.public)
			WHERE
			(usermaps.`hash_a` = ?)", array(
				$this->session->userdata('uidx'),
				$name,
				$this->session->userdata('uidx'),
				$public,
				$hash_a
			));
		}
		$this->load->helper("url");
		redirect("freehand");
	}

	public function savemapname(){
		//$this->output->enable_profiler(TRUE);
		//return false;
		$result = $this->db->query("UPDATE
		`usermaps`
		SET
		`usermaps`.name = ?,
		`usermaps`.public = ?
		WHERE `usermaps`.`hash_a` = ?", array(
			$this->input->post('name'),
			$this->input->post('pub'),
			$this->input->post('uhash')
		));
		print implode(array($this->input->post('name'), $this->input->post('pub'), $this->input->post('uhash')), ", ");
	}

	public function logout(){
		$this->session->unset_userdata('uid1');
		$this->session->unset_userdata('uidx');
		$this->session->unset_userdata('supx');
		$this->session->unset_userdata('photo');
		$this->load->helper("url");
		redirect("freehand");
	}

	###### AJAX-СЕКЦИЯ
	function save(){
		$counter = $this->session->userdata('gcounter');
		$this->session->set_userdata('gcounter', ++$counter);
		$data = $this->session->userdata('objects');
		//$attr = str_replace("-","#",$attr);
		$geometry = $this->input->post('geometry');
		if($this->input->post('type') == 1){
			$geometry = implode($geometry, ",");
		}
		if($this->input->post('type') == 4){
			$geometry = implode($geometry[0],",").",".$geometry[1];
		}
		$data[$this->input->post('id')] = array(
			"geometry"	=> $geometry,
			"type"		=> $this->input->post('type'),
			"attr"		=> $this->input->post('attr'),
			"desc"		=> $this->input->post('desc'),
			"link"		=> $this->input->post('link'),
			"address"	=> $this->input->post('address'),
			"name"		=> $this->input->post('name')
		);
		$this->session->set_userdata("objects", $data);
		// тесты обработки
		//print implode(array($id,$type,$geometry,$attr,$desc,$address,$name),"\n");
		//print "Создан объект ".$id." применён класс ".$attr." в координатах: ".implode(explode(",", $geometry),"<br>"); 
		print_r($this->session->userdata("objects"));
		//print sizeof($this->session->userdata("objects"));
	}
	
	function map_init(){
		$hasha = substr(base64_encode(md5("ehЫАgварыgd".date("U").rand(0,99))), 0, 16);
		$hashe = substr(base64_encode(md5("ЯПzОz7dTS<.g".date("U").rand(0,99))), 0, 16);
		while($this->db->query("SELECT usermaps.id FROM usermaps WHERE usermaps.hash_a = ? OR usermaps.hash_e = ?", array($hasha, $hashe))->num_rows()){
			$hasha = substr(base64_encode(md5("ehЫАgварыgd".date("U").rand(0,99))), 0, 16);
			$hashe = substr(base64_encode(md5("ЯПzОz7dTS<.g".date("U").rand(0,99))), 0, 16);
		}
		$data = array(
			'id'		=> $hasha,
			'eid'		=> $hashe,
			'maptype'	=> 'yandex#satellite',
			'center'	=> $this->config->item('map_center'),
			'zoom'		=> 15,
			'indb'		=> 0,
			'author'	=> 0
		);
		$this->session->set_userdata('map', $data);
		$this->session->set_userdata('objects', array());
		//print_r($this->session->userdata('map'));
	}

	function savemap(){
		$data = $this->session->userdata('map');
		$data['maptype'] = $this->input->post('maptype');
		$data['center']  = $this->input->post('center');
		$data['zoom']    = $this->input->post('zoom');
		$this->session->set_userdata("map", $data);

		//print "Создан объект ".$id." применён класс ".$attr." в координатах: ".implode(explode(",",$geometry),"<br>"); 
		//print "Данные карты: заполнено ".sizeof($data)." полей";
		//print_r($this->session->userdata("map"));
	}

	function session_reset(){
		$this->map_init();
		$this->session->set_userdata('objects',array());
		$data = $this->session->userdata("map");
		print "usermap = []; mp = { ehash:'".$data['eid']."', uhash: '".$data['id']."', indb: 0 }";
	}

	function savedb($list = ""){
		$map = $this->session->userdata('map');
		if($map['id'] == 'void'){
			return false;
		}
		//$map_center = explode(",", $map['center']);
		$map_center = $map['center'];
		$map_lat    = $map_center[0];
		$map_lon    = $map_center[1];
		$hasha      = $map['id'];
		$hashe      = $map['eid'];

		if(!$map['indb']){
			if($this->db->query("INSERT INTO usermaps (
				usermaps.center_lat,
				usermaps.center_lon,
				usermaps.maptype,
				usermaps.zoom,
				usermaps.hash_a,
				usermaps.hash_e,
				usermaps.author
			) VALUES (?, ?, ?, ?, ?, ?, ?)", array(
				$map_lat,
				$map_lon,
				$map['maptype'],
				$map['zoom'],
				$map['id'],
				$map['eid'],
				$this->session->userdata('uidx')
			))){
				$map['indb'] = 1;
			}
		}else{
			if(!$map['author'] || $map['author'] == $this->session->userdata("uidx")){
				$this->db->query("UPDATE usermaps 
				SET
					usermaps.center_lat = ?,
					usermaps.center_lon = ?,
					usermaps.maptype = ?,
					usermaps.zoom = ?,
					usermaps.author = ?
				WHERE usermaps.hash_a = ?
				OR usermaps.hash_e = ?", array(
					$map_lat,
					$map_lon,
					$map['maptype'],
					$map['zoom'],
					$this->session->userdata("uidx"),
					$map['id'],
					$map['id']
				));
			}else{
				$this->db->query("UPDATE usermaps 
				SET
					usermaps.center_lat = ?,
					usermaps.center_lon = ?,
					usermaps.maptype = ?,
					usermaps.zoom = ?
				WHERE usermaps.hash_a = ?
				OR usermaps.hash_e = ?", array(
					$map_lat,
					$map_lon,
					$map['maptype'],
					$map['zoom'],
					$map['id'],
					$map['id']
				));
			}
		}
		$this->session->set_userdata('map', $map);

		$this->load->library('user_agent');
		$this->db->query("DELETE FROM userobjects WHERE userobjects.map_id = ?", array($map['id']));
		
		$insert_query_list = array();
		$data = $this->session->userdata('objects');
		foreach ($data as $key=>$val) {
			$superhash = $map['id']."_".substr(md5(date("U").rand(0, 9999).rand(0, 9999)), 0, 8);
			$string = "(
				'".$this->db->escape_str($val['geometry'])."',
				'".$this->db->escape_str($val['attr'])."',
				'".$this->db->escape_str($val['desc'])."',
				'".$this->db->escape_str($val['address'])."',
				'".$this->db->escape_str($val['name'])."',
				'".$this->db->escape_str($val['type'])."',
				'".$this->db->escape_str($val['link'])."',
				'".$this->db->escape_str($map['id'])."',
				'".$superhash."',
				INET_ATON('".((isset($_SERVER["HTTP_X_REAL_IP"]) ? $_SERVER["HTTP_X_REAL_IP"] : 0))."'),
				'".$this->agent->agent_string()."'
			)";
			array_push($insert_query_list, $string);
		}
		if(sizeof($insert_query_list)){
			$this->db->query("INSERT INTO userobjects (
				userobjects.coord,
				userobjects.attributes,
				userobjects.description,
				userobjects.address,
				userobjects.name,
				userobjects.type,
				userobjects.link,
				userobjects.map_id,
				userobjects.hash,
				userobjects.ip,
				userobjects.uagent
			) VALUES ". implode($insert_query_list, ",\n"));
			/*
			$this->db->query("INSERT INTO userobjects_heap (
				userobjects_heap.coord,
				userobjects_heap.attributes,
				userobjects_heap.description,
				userobjects_heap.address,
				userobjects_heap.name,
				userobjects_heap.type,
				userobjects_heap.map_id,
				userobjects_heap.hash,
				userobjects_heap.ip,
				userobjects_heap.uagent
			) VALUES ". implode($insert_query_list, ",\n"));
			*/
		}
		//print implode($insert_query_list, ",\n");
		$this->createframe($map['id']);
		//print $hasha;
		$output = $this->getumap($map['id']);
		print "usermap = { ".implode($output,",\n")." }; mp = { ehash: '".$map['eid']."', uhash: '".$map['id']."' }";
		//print $this->db->last_query();
		//print "Список объектов ".implode(explode("-",$list),","); 
		//print_r($map);
		//print_r($data);
		//print implode($output, "\n");
		//print_r($this->session->userdata("objects"));
		//print_r($superhash);
	}

	function getumap($hash = "NmIzZjczYWRlOTg5"){
		$result = $this->db->query("SELECT 
		userobjects.name,
		userobjects.description,
		userobjects.coord,
		userobjects.attributes,
		userobjects.address,
		userobjects.`type`,
		userobjects.hash,
		userobjects.link,
		usermaps.hash_a,
		usermaps.hash_e
		FROM
		userobjects
		INNER JOIN `usermaps` ON (userobjects.map_id = `usermaps`.hash_a)
		WHERE
		`usermaps`.hash_a = ? OR
		`usermaps`.hash_e = ?", array($hash, $hash));
		$output = array();
		if($result->num_rows()){
			$newobjects = array();
			foreach ($result->result() as $row){
				$newobjects[$row->hash] = array(
					"geometry" => $row->coord,
					"type"     => $row->type,
					"attr"     => $row->attributes,
					"link"     => $row->link,
					"desc"     => $row->description,
					"address"  => $row->address,
					"name"     => $row->name
				);
				$string = $row->hash.": { d: '".$row->description."', n: '".$row->name."', a: '".$row->attributes."', p: ".$row->type.", c: '".$row->coord."', b: '".$row->address."', l: '".$row->link."' }";
				array_push($output, preg_replace("/\n/", " ", $string));
			}
			$this->session->set_userdata('objects', array());
			$this->session->set_userdata('objects', $newobjects);
		}else{
			$output = array("error: 'Содержимого для карты с таким идентификатором не найдено.'");
		}
		return $output;
	}

	function loadscript($hash = "YzkxNzVjYTI0MGZk"){
		$result = $this->db->query("SELECT 
		`usermaps`.center_lon as `maplon`,
		`usermaps`.center_lat as `maplat`,
		`usermaps`.hash_a,
		`usermaps`.hash_e,
		`usermaps`.zoom as `mapzoom`,
		`usermaps`.maptype,
		`usermaps`.name
		FROM
		`usermaps`
		WHERE
		`usermaps`.`hash_a` = ?",array($hash));
		if($result->num_rows()){
			$objects = $result->row_array();
			$objects['maptype'] = (!in_array($objects['maptype'], array("yandex#satellite", "yandex#map"))) ? "yandex#satellite" : $objects['maptype'];

		}else{
			print 'Карта не была обработана и не может быть выдана в виде HTML<br><br>
			Вернитесь в <a href="/freehand">РЕДАКТОР КАРТ</a>, выберите в меню <strong>Карта</strong> -> <strong>Обработать</strong> и попробуйте ещё раз';
			return false;
		}

		$result = $this->db->query("SELECT 
		userobjects.name,
		userobjects.description,
		userobjects.coord,
		userobjects.attributes,
		userobjects.address,
		userobjects.`type`,
		userobjects.`link`
		FROM
		userobjects
		WHERE
		`userobjects`.`map_id` = ?", array($objects['hash_a']));
		$output = array();
		$mo     = array();
		if($result->num_rows()){
			foreach ($result->result() as $row){
				$addr = str_replace("'", '"', $row->address);
				$desc = str_replace("'", '"', $row->description);
				$name = str_replace("'", '"', $row->name);
				$attr = str_replace("'", '"', $row->attributes);
				$link = str_replace("'", '"', $row->link);
				$constant = "{address: '".$addr."', description: '".$desc."', name: '".$name."', link: '".$link."' }, ymaps.option.presetStorage.get('".$attr."'));ms.add(object);";
				switch($row->type){
					case 1:
						$string='object = new ymaps.Placemark({type: "Point", coordinates: ['.$row->coord.']}, '.$constant;
					break;
					case 2:
						$string='object = new ymaps.Polyline(new ymaps.geometry.LineString.fromEncodedCoordinates("'.$row->coord.'"), '.$constant;
					break;
					case 3:
						$string='object = new ymaps.Polygon(new ymaps.geometry.Polygon.fromEncodedCoordinates("'.$row->coord.'"), '.$constant;
					break;
					case 4:
						$coords = explode(",", $row->coord);
						$string='object = new ymaps.Circle(new ymaps.geometry.Circle(['.$coords[0].', '.$coords[1].'],'.$coords[2].'), '.$constant;
					break;
				}
				array_push($output, $string);
			}
		}

		$file = "./mpct.txt";
		$sum = implode(file($file), '');
		$open = fopen($file, "w");
		fputs($open, ++$sum);
		fclose($open);

		$objects['mapobjects'] = implode($output, "\n");


		$script = $this->load->view('freehand/script', $objects, TRUE);
		$this->load->helper('download');
		force_download("Minigis.NET - ".$objects['hash_a'].".html", $script); 
	}

	function createframe($hash = "YzkxNzVjYTI0MGZk"){
		$objects = array();
		$result  = $this->db->query("SELECT 
		`usermaps`.center_lon as `maplon`,
		`usermaps`.center_lat as `maplat`,
		`usermaps`.hash_a,
		`usermaps`.hash_e,
		`usermaps`.zoom as `mapzoom`,
		`usermaps`.maptype,
		`usermaps`.name
		FROM
		`usermaps`
		WHERE
		`usermaps`.`hash_a` = ? OR 
		`usermaps`.`hash_e` = ? ",array($hash, $hash));
		if($result->num_rows()){
			$objects = $result->row_array();
		}else{
			print 'alert("Карта не была обработана. Нажмите кнопку обработать в выпадающем меню Карта")';
			return false;
		}

		$result = $this->db->query("SELECT 
		userobjects.name,
		userobjects.description,
		userobjects.coord,
		userobjects.attributes,
		userobjects.address,
		userobjects.`type`,
		userobjects.`link`
		FROM
		userobjects
		WHERE
		`userobjects`.`map_id` = ?", array($objects['hash_a']));
		$output = array();
		$mo = array();
		if($result->num_rows()){
			foreach ($result->result() as $row){
				$addr = str_replace("'", "\"", $row->address);
				$desc = str_replace("'", "\"", $row->description);
				$name = str_replace("'", "\"", $row->name);
				$attr = str_replace("'", "\"", $row->attributes);
				$link = str_replace("'", "\"", $row->link);
				$prop = '{address: \''.$addr.'\', description: \''.$desc.'\', name: \''.$name.'\', hasHint: 1, hintContent: \''.$name.' '.$desc.'\', link: \''.$link.'\' }';
				$opts = 'ymaps.option.presetStorage.get(\''.$attr.'\')';
				switch($row->type){
					case 1:
						$string = 'object = new ymaps.Placemark( {type: \'Point\', coordinates: ['.$row->coord.']}, '.$prop.', '.$opts.' )';
					break;
					case 2:
						$string = 'object = new ymaps.Polyline(new ymaps.geometry.LineString.fromEncodedCoordinates(\''.$row->coord.'\'), '.$prop.', '.$opts.')';
					break;
					case 3:
						$string = 'object = new ymaps.Polygon(new ymaps.geometry.Polygon.fromEncodedCoordinates("'.$row->coord.'"), '.$prop.', '.$opts.')';
					break;
					case 4:
						$coords = explode(",", $row->coord);
						$string = 'object = new ymaps.Circle(new ymaps.geometry.Circle(['.$coords[0].', '.$coords[1].'], '.$coords[2].'), '.$prop.', '.$opts.')';
					break;
				}
				array_push($output, $string.";\nms.add(object);");
			}
		}
		$objects['mapobjects'] = implode($output, "\n");
		$script = $this->load->view('freehand/frame', $objects, TRUE);
		$this->load->helper("file");
		write_file('freehandcache/'.$objects['hash_a'], $script, 'w');
		//$this->loadframe($objects['hash_a']);
		//print "OK";
	}
	
	function loadframe($hash = "NWY2MjVlMzAwOWMz"){
		$file = "./mpct.txt";
		$sum = implode(file($file),'');
		$open = fopen($file,"w");
		fputs($open,++$sum);
		fclose($open);
		$this->load->helper("file");
		//$this->load->helper("download");
		//force_download('mapframe_'.$hash.'.html', read_file('freehandcache/'.$hash));
		print read_file('freehandcache/'.$hash);
	}

	function loadmap(){
		$hash = $this->input->post('name');
		$mapparam = "";
		$result = $this->db->query("SELECT 
		CONCAT_WS(',', `usermaps`.center_lon, `usermaps`.center_lat) AS center,
		`usermaps`.hash_a,
		`usermaps`.hash_e,
		`usermaps`.zoom,
		`usermaps`.maptype,
		`usermaps`.name,
		`usermaps`.author
		FROM
		`usermaps`
		WHERE
		`usermaps`.`hash_a` = ? OR
		`usermaps`.`hash_e` = ?", array( $hash, $hash ));
		if($result->num_rows()){
			$newobjects = array();
			$row = $result->row();
			if($row->hash_e == $hash){
				$mapid = $row->hash_a;
				$ehash = $row->hash_e;
				$uhash = $row->hash_a;
			}
			if($row->hash_a == $hash){
				$mapid = "void";
				$ehash = $row->hash_a;
				$uhash = $row->hash_a;
			}
			$data = array(
				"id"		=> $mapid,
				"eid"		=> $ehash,
				"maptype"	=> $row->maptype,
				"center"	=> $row->center,
				"zoom"		=> $row->zoom,
				"indb"		=> 1,
				"author"	=> $row->author
			);
			$this->session->set_userdata('map', $data);
			$mapparam = "mp = { id: '".$mapid."', maptype: '".$row->maptype."', c: [".$row->center."], zoom: ".$row->zoom.", uhash: '".$uhash."', ehash: '".$ehash."', indb: 1 };\n";
			print $mapparam."usermap = { ".implode($this->getumap($uhash), ",\n")."\n}";
		}
		else{
			//$this->session->set_userdata('map',array('id' => 'new'));
			//$this->session->set_userdata('map', $data);
			print "alert('Карты с таким идентификатором не найдено.')";
		}
	}

	function obj_delete(){
		$node = $this->input->post("ttl");
		$objects = $this->session->userdata('objects');
		//print $objects[$node]['desc']."\n";
		unset($objects[$node]);
		$this->session->set_userdata('objects', $objects);
		//print_r($this->session->userdata("objects"));
		//print sizeof($this->session->userdata("objects"));
	}

	function get_session(){
		$data = $this->session->userdata('map');
		if($data['id'] == 'void'){
			$this->map_init();
			//$data = $this->session->userdata('map');
			print "usermap = []";
			return false;
		}
		$objects = $this->session->userdata('objects');
		$output = array();
		foreach($objects as $hash => $val){
			$string = $hash." : { d: '".$val['desc']."', n: '".$val['name']."', a: '".$val['attr']."', p: ".$val['type'].", c: '".$val['geometry']."', b: '".$val['address']."', l: '".$val['link']."' }";
			array_push($output, $string);
		}
		$c = $data['center'];
		print  "mp = { id: '".$data['id']."', maptype: '".$data['maptype']."', c0: ".$c[0].", c1: ".$c[1].", zoom: ".$data['zoom'].", uhash: '".$data['id']."', ehash: '".$data['eid']."', indb: ".$data['indb']." };"."\nusermap = { ".implode($output,",\n")."\n};";
		//print_r($this->session->userdata("objects"));
	}

	public function transfer(){
		$format = ($this->input->post("format")) ? $this->input->post("format") : "plainobject";
		$hash   = $this->input->post("hash");
		$result = $this->db->query("SELECT 
		`usermaps`.center_lon as `maplon`,
		`usermaps`.center_lat as `maplat`,
		`usermaps`.hash_a,
		`usermaps`.hash_e,
		`usermaps`.zoom as `mapzoom`,
		`usermaps`.maptype,
		`usermaps`.name
		FROM
		`usermaps`
		WHERE
		`usermaps`.`hash_a` = ?", array($hash));
		if($result->num_rows()){
			$objects = $result->row_array();
		}else{
			print "Сопоставленная карта не обнаружена";
			return false;
		}

		$result = $this->db->query("SELECT 
		userobjects.name,
		userobjects.description,
		userobjects.coord,
		userobjects.attributes,
		userobjects.address,
		userobjects.`type`,
		userobjects.`link`
		FROM
		userobjects
		WHERE
		`userobjects`.`map_id` = ?
		ORDER BY userobjects.timestamp", array($objects['hash_a']));
		$output = array();
		$mo     = array();
		if($result->num_rows()){
			foreach ($result->result() as $row){
				$p = sizeof($output);
				$addr = str_replace("'", "\"", $row->address);
				$desc = str_replace("'", "\"", $row->description);
				$name = str_replace("'", "\"", $row->name);
				$attr = str_replace("'", "\"", $row->attributes);
				$link = str_replace("'", "\"", $row->link);
				switch($format){
					case 'plainjs':
						$props = '<br>&nbsp;&nbsp;&nbsp;&nbsp;{ b: "'.$addr.'", d: "'.$desc.'", n: "'.$name.'", l: \''.$link.'\' },<br>';
						$opts  = '&nbsp;&nbsp;&nbsp;&nbsp;ymaps.option.presetStorage.get(\''.$attr.'\')<br>';
					break;
					case 'plainobject':
						$props = '{ b: "'.$addr.'", d: "'.$desc.'", n: "'.$name.'", l: \''.$link.'\' },';
						$opts  = '{ attr: "'.$attr.'" }';
					break;
				}
				//$props = '<br>&nbsp;&nbsp;&nbsp;&nbsp;{ b: "'.$addr.'", d: "'.$desc.'", n: "'.$name.'", l: \''.$link.'\' },<br>';
				//$opts  = '&nbsp;&nbsp;&nbsp;&nbsp;ymaps.option.presetStorage.get(\''.$attr.'\')<br>';
				switch($row->type){
					case 1:
						switch($format){
							case 'plainjs':
								$string = $p.': new ymaps.Placemark(<br>&nbsp;&nbsp;&nbsp;&nbsp;{type: "Point", coordinates: ['.$row->coord.']},'.$props.$opts." )";
							break;
							case 'plainobject':
								$string = $p.': [{ type: "Point", coord: ['.$row->coord.'] },'.$props.$opts."]";
							break;
						}
					break;
					case 2:
						switch($format){
							case 'plainjs':
								$string = $p.': new ymaps.Polyline(<br>&nbsp;&nbsp;&nbsp;&nbsp;new ymaps.geometry.LineString.fromEncodedCoordinates("'.$row->coord.'"),'.$props.$opts." )";
							break;
							case 'plainobject':
								$string = $p.': [{ type: "LineString", coord: "'.$row->coord.'" },'.$props.$opts."]";
							break;
						}
					break;
					case 3:
						switch($format){
							case 'plainjs':
								$string = $p.': new ymaps.Polygon(<br>&nbsp;&nbsp;&nbsp;&nbsp;new ymaps.geometry.LineString.fromEncodedCoordinates("'.$row->coord.'"),'.$props.$opts." )";
							break;
							case 'plainobject':
								$string = $p.': [{ type: "Polygon", coord: "'.$row->coord.'" },'.$props.$opts."]";
							break;
						}
					break;
					case 4:
						$coords = explode(",", $row->coord);
						switch($format){
							case 'plainjs':
								$string = $p.': new ymaps.Circle(<br>&nbsp;&nbsp;&nbsp;&nbsp;new ymaps.geometry.Circle(['.$coords[0].', '.$coords[1].'],'.$coords[2].'),'.$props.$opts." )";
							break;
							case 'plainobject':
								$string = $p.': [{ type: "Circle", coord: ['.$coords[0].', '.$coords[1].', '.$coords[2].'] },'.$props.$opts."]";
							break;
						}
					break;
				}
				array_push($output, $string);
			}
		}else{
			print "No Objects";
		}

		switch($format){
			case 'plainjs':
				$delimiter = ",\n<br>";
			break;
			case 'plainobject':
				$delimiter = ",\n<br>";
			break;
		}

		$objects['mapobjects'] = implode($output, $delimiter);
		$script = $this->load->view('freehand/transfer', $objects, TRUE);
		print $script;
	}
}

/* End of file freehand.php */
/* Location: ./system/application/controllers/freehand.php */