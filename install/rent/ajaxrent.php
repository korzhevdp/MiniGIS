<?php
class Ajaxrent extends CI_Controller{
	function __construct(){
		parent::__construct();
	}

	function list_build(){
		//header('Content-type: text/html; charset=windows-1251');
		$output = Array();
		//print strlen($this->session->userdata('user_id'));
		if (strlen($this->session->userdata('user_id')) !== 40){
			print "Некорректный логин";
		}
		$result = $this->db->query("SELECT 
		CONCAT(locations_types.name,' ',locations.location_name,'<br>',locations.address) as name,
		locations.active,
		locations.loc_hash AS `hash`,
		IF(NOW() BETWEEN `timers`.`start_point` AND `timers`.`end_point` AND `timers`.`type` = 'price', 1, 0) as priced,
		IF(NOW() BETWEEN `timers`.`start_point` AND `timers`.`end_point` AND `timers`.`type` = 'check', 1, 0) as checkterm,
		IF(NOW() BETWEEN `timers`.`start_point` AND `timers`.`end_point` AND `timers`.`type` = 'order', 1, 0) as orderterm,
		IF(NOW() BETWEEN `timers`.`start_point` AND `timers`.`end_point` AND `timers`.`type` = 'rent', 1, 0) as rentterm
		FROM
		locations
		INNER JOIN locations_types ON (locations.`type` = locations_types.id)
		LEFT OUTER JOIN `timers` ON (locations.id = `timers`.location_id)
		WHERE
		(locations.owner = ?) AND 
		(locations_types.object_group = 6)",array($this->session->userdata('user_id')));
		if($result->num_rows()){
			foreach($result->result() as $row){
				$class = ($row->active) ? "" : "btn-inverse";
				$func = ($row->active) ? "loc_deactivate" : "loc_activate";
				$act_word = ($row->active) ? "Деактивировать" : "Активировать";
				$string='<div class="btn-group">
				<button class="btn dropdown-toggle'.$class.' span3" id="loc'.$row->hash.'" data-toggle="dropdown" style="margin-left:-5px;">
					<i class="icon-home"></i>&nbsp;'.$row->name.'&nbsp;&nbsp;<span class="caret"></span>
				</button>
				<ul class="dropdown-menu">
					<li onclick="location_edit(\''.$row->hash.'\')"><a href="#"><i class="icon-pencil"></i>&nbsp;Править описание предложения</a></li>
					<li onclick="price_edit(\''.$row->hash.'\')"><a href="#"><i class="icon-signal"></i>&nbsp;Править цены</a></li>
					<li id="locact'.$row->hash.'" onclick="loc_activate(\''.$row->hash.'\')"><a href="#"><i class="icon-ban-circle"></i>&nbsp;'.$act_word.' предложение</a></li>
					<li class="divider"></li>
					<li onclick="rent_edit(\''.$row->hash.'\')"><a href="#"><i class="icon-briefcase"></i>&nbsp;Оформление договора</a></li>
				</ul>
				</div>';
				array_push($output,$string);
			}
		}
		print implode($output,"\n");
	}
	
	function save_user_map_center($coord){
		$this->db-query("UPDATE
		`users_admins`
		SET
		`users_admins`.map_center = ?
		WHERE
		(`users_admins`.uid = ?)", array($coord,$this->session->userdata('user_id')));
		$result = ($this->db->affected_rows()) ? 1 : 0;
		print $result;
	}

	function location_active_sw($location_id){
		$this->db->query("UPDATE
		locations
		SET
		locations.active = IF(locations.active = 1, 0, 1)
		WHERE
		(locations.owner = ?) AND
		(locations.loc_hash = ?)", array($this->session->userdata('user_id'),$location_id));
		$result = $this->db->query("SELECT 
		locations.active 
		FROM locations 
		WHERE 
		(locations.owner = ?) AND
		(locations.loc_hash = ?)", array($this->session->userdata('user_id'),$location_id));
		if($result->num_rows()){
			$row = $result->row();
			$out = $row->active;
		}
		print $out;
	}

	function setusercenter(){
		$coords = implode($this->input->post("coords"), ",");
		$this->db->query("UPDATE
			users_admins
			SET
			users_admins.map_center = ?
			WHERE
			(users_admins.uid = ?)", array($coords, $this->session->userdata('user_id')));
		$result = ($this->db->affected_rows()) ? 1 : 0;
		print $result;
	}

	function getusercenter(){
		$out = $this->config->item('maps_center');
		$result = $this->db->query("SELECT
			users_admins.map_center
			FROM
			users_admins
			WHERE
			(users_admins.uid = ?)
			LIMIT 1", array($this->session->userdata('user_id')));
		if($result->num_rows()){
			$row = $result->row();
			$out = (strlen($row->map_center)) ? $row->map_center : $out;
		}
		print $out;
	}

	function getlocdata($hash,$seed=0){
		$out = "чапаевская пустота";
		$result = $this->db->query("SELECT 
		locations.id,
		locations.address,
		locations.coord_y,
		locations.active,
		locations.type
		FROM
		locations
		WHERE
		`locations`.`loc_hash` = ? AND
		`locations`.`owner` = ?
		LIMIT 1", array($hash, $this->session->userdata('user_id')));
		if($result->num_rows()){
			$row = $result->row();
			$out = '{"address": "'.$row->address.'",
			"coord": "'.$row->coord_y.'",
			"type": "'.$row->type.'",
			"active": "'.$row->active.'"}';
		}
		//header('Content-type: text/html; charset=windows-1251');
		print $out;
	}

	function savelocation(){
		//$addr = iconv("UTF-8","windows-1251",$addr);
		$this->db->query("UPDATE
		locations
		SET
		locations.`address` = ?,
		locations.`type` = ?,
		locations.`coord_y` = ?
		WHERE
		locations.`loc_hash` = ? AND
		locations.owner = ?", array(
			$this->input->post('addr'),
			$this->input->post('type'),
			$this->input->post('coord'),
			$this->input->post('hash'),
			$this->session->userdata('user_id')
		));
		$this->db->query("UPDATE 
		properties_assigned 
		SET 
		properties_assigned.property_id = (SELECT locations_types.pl_num FROM locations_types WHERE `locations_types`.`id` = ?)
		WHERE
		properties_assigned.location_id = (SELECT `locations`.id FROM `locations` WHERE `locations`.loc_hash = ?) AND
		properties_assigned.property_id IN (SELECT locations_types.pl_num FROM locations_types WHERE locations_types.object_group = (SELECT locations_types.object_group FROM locations_types WHERE locations_types.id = ?))", array(
			$this->input->post('type'),
			$this->input->post('hash'),
			$this->input->post('type')
		));
		//header('Content-type: text/html; charset=windows-1251');
		print $this->db->affected_rows();
	}

	function savelpr($hash,$str){
		$this->db->query("DELETE FROM properties_assigned 
		WHERE properties_assigned.location_id = (
			SELECT 
			locations.id
			FROM
			locations
			WHERE
			(locations.loc_hash = ?)
		) AND
		properties_assigned.property_id IN(
			SELECT 
			properties_list.id
			FROM
			`locations_types`
			INNER JOIN properties_list ON (`locations_types`.object_group = properties_list.object_group)
			INNER JOIN `locations` ON (`locations_types`.id = `locations`.`type`)
			WHERE
			`locations`.`loc_hash` = ?
		)",array($hash,$hash));
		//print $this->db->last_query();
		$src=explode("-",$str);
		$out = array();
		foreach($src as $val){
			array_push($out,"(".$row->id.",".(integer)$val.",'Y')");
		}
		$this->db->query("INSERT INTO properties_assigned (
			properties_assigned.location_id,
			properties_assigned.property_id,
			properties_assigned.value) VALUES ".implode($out,","));

		//header('Content-type: text/html; charset=windows-1251');
		//print $this->db->affected_rows();
	}

	function savelprn($hash, $type, $coord, $address){
		$result=$this->db->query("UPDATE
		locations
		SET 
		locations.type = ?,
		locations.coord_y = ?,
		locations.address = ?
		WHERE 
		locations.loc_hash = ?",array($type, $coord, iconv("UTF-8","windows-1251",$address), $hash));
		if($this->db->affected_rows()){
			print $this->db->last_query();
		}
	}

	function newlocation($type,$lat,$lon,$addr){
		$addr = iconv("UTF-8","windows-1251",$addr);
		$this->db->query("INSERT INTO
		locations (
			locations.`address`,
			locations.`type`,
			locations.`coord_y`,
			locations.`location_name`,
			locations.`loc_hash`,
			locations.`owner`,
			locations.`date`
		)VALUES(?,?,?,?,?,?,NOW())",array($addr, $type, $lat.','.$lon, '', 11, $this->session->userdata('user_id')));

		$id = $this->db->insert_id();
		$this->db->query("UPDATE `locations` SET locations.`loc_hash` = ? WHERE `locations`.id = ?", array(md5($this->config->item('user_id').$id),$id));
		$this->db->query("INSERT INTO properties_assigned (
			properties_assigned.location_id,
			properties_assigned.property_id,
			properties_assigned.value
		)VALUES(?,(SELECT locations_types.pl_num FROM locations_types WHERE locations_types.id = ?),'Y')",array($id,$type));
		//header('Content-type: text/html; charset=windows-1251');
		print $this->db->affected_rows();
	}

//выбрать свойства локации :)

	function getproperties($hash="AAAA"){
		$out = array();
		$result = $this->db->query("SELECT 
		properties_list.label,
		properties_list.selfname,
		IF(locations.loc_hash = ?, 1, 0) AS `checked`,
		properties_list.id
		FROM
		properties_assigned
		RIGHT OUTER JOIN properties_list ON (properties_assigned.property_id = properties_list.id)
		LEFT OUTER JOIN locations ON (properties_assigned.location_id = locations.id)
		WHERE
		properties_list.active = 1 AND
		(properties_list.object_group = 6)",array($hash));
		if($result->num_rows()){
			foreach($result->result() as $row){
				$quantor = sizeof($out)%2;
				$prefix = '<div class="row-fluid">';
				$suffix = '</div>';
				($quantor) ? $prefix = '' : $suffix = '';
				$checked = ($row->checked) ? 'checked="checked"' : "";
				$string = $prefix.'<div class="span6"><label class="checkbox" for="chf1'.$row->id.'"><input type="checkbox" id="chf1'.$row->id.'" prp='.$row->id.' '.$checked.'>'.$row->selfname.'</label></div>'.$suffix;
				array_push($out,$string);
			}
		}
		//header('Content-type: text/html; charset=windows-1251');
		print implode($out,"\n");
	}

	function getprofiledata(){
		$out=array();
		$result = $this->db->query("SELECT 
		users_admins.name_f,
		users_admins.name_i,
		users_admins.name_o,
		users_admins.nick
		FROM
		users_admins
		WHERE
		`users_admins`.`uid` = ?
		LIMIT 1",array($this->session->userdata('user_id')));
		if($result->num_rows()) {
			$row = $result->row();
			array_push($out,'"a1": "'.$row->name_f.'"');
			array_push($out,'"a2": "'.$row->name_i.'"');
			array_push($out,'"a3": "'.$row->name_o.'"');
			array_push($out,'"b1": "'.$row->nick.'"');
		}

		$result = $this->db->query("SELECT 
		`pd`.prop,
		`pd`.value
		FROM
		`pd`
		WHERE
		`pd`.`hash` = ?",array(md5($this->session->userdata('user_salt'))));
		if($result->num_rows()) {
			foreach($result->result() as $row){
				array_push($out,'"a'.$row->prop.'": "'.$row->value.'"');
			}
		}
		//header('Content-type: text/html; charset=windows-1251');
		print "{".implode($out,",")."}";
	}

	function setprofiledata(){
		$name_f = array_shift($array);
		$name_i = array_shift($array);
		$name_o = array_shift($array);
		$pd_hash = md5($this->session->userdata('user_salt'));
		$this->db->query("DELETE FROM pd WHERE `pd`.`hash` = ?", array($pd_hash));
		foreach($array as $val){
			$this->db->query("INSERT INTO pd (
				pd.`hash`,
				pd.`prop`,
				pd.`value`
			) VALUES( ?, ?, ? ) ", array( $pd_hash, $str[0], $str[1] ));
		}
	}

	function getprices($hash){
		$out = '{
		"p1": "",
		"p2": "",
		"p4": "",
		"p5": "",
		"pm": ""
		}';
		$result = $this->db->query("SELECT 
		`prices`.day1,
		`prices`.day2,
		`prices`.day34,
		`prices`.day5,
		`prices`.`month`
		FROM
		`prices`
		WHERE 
		prices.loc_hash = ?",array($hash));
		if($result->num_rows()) {
			$row = $result->row();
			$out = '{
			"p1": "'.$row->day1.'",
			"p2": "'.$row->day2.'",
			"p4": "'.$row->day34.'",
			"p5": "'.$row->day5.'",
			"pm": "'.$row->month.'"
			}';
		}
		//header('Content-type: text/html; charset=windows-1251');
		print $out;
	}

	function setprices($d1,$d2,$d4,$d5,$dm,$hash){
		$this->db->query("DELETE FROM `prices` WHERE `prices`.`loc_hash` = ?",array($hash));
		$this->db->query("INSERT INTO `prices`(
			`prices`.day1,
			`prices`.day2,
			`prices`.day34,
			`prices`.day5,
			`prices`.`month`,
			`prices`.`loc_hash`
			)VALUES(?,?,?,?,?,?)",array($d1,$d2,$d4,$d5,$dm,$hash));
		print (!$this->db->affected_rows() < 0) ? $this->db->affected_rows() : $this->db->last_query(); ;
	}

}
/* End of file ajaxrent.php */
/* Location: ./system/application/controllers/ajaxrent.php */