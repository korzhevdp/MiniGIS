<?php
class Usermodulemodel extends CI_Model{
	function __construct(){
		parent::__construct();
	}

	function get_index($obj_group = 1, $loc_type = 0){
		$output = array('<ul class="thumbnails">');
		if(!$loc_type){
			$result=$this->db->query("SELECT 
			`objects_groups`.name,
			`objects_groups`.`id`
			FROM
			`objects_groups`
			WHERE
			`objects_groups`.`id` = ?", array($obj_group));
			if($result->num_rows()){
				$row = $result->row();
				array_unshift($output,'<h2><a href="/usermodules/library/'.$obj_group.'">'.$row->name.'</a></h2><hr>');
			}
			$result = $this->db->query("SELECT 
			locations_types.id,
			locations_types.object_group as obj_group,
			locations_types.name AS `title`,
			IF(LENGTH(locations_types.name) > 40, CONCAT(LEFT(locations_types.name, 37), '...'), locations_types.name) AS name
			FROM
			locations_types
			WHERE
			`locations_types`.`pl_num` AND
			`locations_types`.`object_group` = ?
			ORDER BY title", array($obj_group));

			if($result->num_rows()){
				foreach ($result->result_array() as $row){
					$row['img']  = '<img src="'.$this->config->item("api").'/images/folder.png" alt="">';
					$row['link'] = '/usermodules/library/'.$obj_group.'/'.$row['id'];
					array_push($output, $this->load->view("admin/libraryitem", $row, true));
				}
			}
		}else{
			$result=$this->db->query("SELECT
			locations_types.object_group as obj_group,
			`locations_types`.`id` AS `tid`,
			`locations_types`.name AS `tp_name`,
			`objects_groups`.`id` AS `oid`,
			`objects_groups`.name AS `ob_name`
			FROM
			`locations_types`
			INNER JOIN `objects_groups` ON (`locations_types`.object_group = `objects_groups`.id)
			WHERE `locations_types`.`id` = ?", array($loc_type));
			if($result->num_rows()){
				$row = $result->row();
				array_unshift($output,'<h2><a href="/usermodules/library/'.$row->oid.'">'.$row->ob_name.'</a> / <a href="/usermodules/library/'.$row->oid.'/'.$row->tid.'">'.$row->tp_name.'</a></h2><hr>');
			}
			$result=$this->db->query("SELECT 
			IF(LENGTH(`locations`.location_name) > 40, CONCAT(LEFT(`locations`.location_name, 37), '...'), `locations`.location_name) AS name,
			`locations`.location_name AS title,
			`locations`.id
			FROM
			`locations`
			WHERE `locations`.`owner` = ? AND
			`locations`.`type` = ?
			ORDER BY title", array($this->session->userdata('user_id'), $loc_type));
			if($result->num_rows()){
				foreach ($result->result_array() as $row){
					$row['img']  = '<img src="'.$this->config->item("api").'/images/location_pin.png" alt="">';
					$row['link'] = '/editor/forms/'.$row['id'];
					array_push($output, $this->load->view("admin/libraryitem", $row, true));
				}
			}
			$row = array(
				'img'   => '<img src="'.$this->config->item("api").'/images/location_pin.png" alt="">',
				'name'  => 'Добавить объект',
				'link'  => '/editor/forms/0/'.$loc_type,
				'title' => "Добавить новый объект этого класса"
			);
			array_push($output, $this->load->view("admin/libraryitem", $row, true));
		}
		array_push($output,"</ul>");
		return implode($output,"\n");
	}

	function _user_edit($userid){
		$result=$this->db->query("SELECT
			users_admins.nick,
			users_admins.registration_date,
			users_admins.name_f,
			users_admins.name_i,
			users_admins.name_o,
			users_admins.info
			FROM
			users_admins
			WHERE
			users_admins.uid = '".$userid."'");
		if($result->num_rows() ){
			$user=$result->row_array();
		}else{
			$user['warning']="Не найдено учётных данных по пользователю";
		}
		return $this->load->view('admin/user',$user,true);
	}

	function _user_save($userid){
		$this->db->query("UPDATE users_admins SET
		users_admins.name_f = ?,
		users_admins.name_i = ?,
		users_admins.name_o = ?,
		users_admins.info = ?
		WHERE
		users_admins.uid = ?", array(
			$this->input->post('user_name_f', TRUE),
			$this->input->post('user_name_i', TRUE),
			$this->input->post('user_name_o', TRUE),
			$this->input->post('user_info', TRUE),
			$userid
		));
	}

	function _photoeditor_order_save(){
		$list=explode(",",$this->input->post('frm_img_order'));
		foreach($list as $key=>$val){
			$this->db->query("UPDATE `images` SET `images`.`order` = ? WHERE `images`.`filename` = ?", array(($key+1),$val.".jpg"));
		}
	}

	function _photoeditor_list($location_id){
		$pics = array();
		$result=$this->db->query("SELECT 
		`images`.`filename`,
		`images`.`id`,
		`images`.`orig_filename`,
		`images`.`full`,
		`images`.`mid`,
		`images`.`small`,
		`images`.`location_id`,
		`images`.`order`,
		`images`.`comment`,
		`images`.`active`
		FROM
		images
		WHERE
		(`images`.`owner_id` = ?) 
		OR (`images`.`location_id` = ? 
		AND `images`.`owner_id` = 'frontend_user')
		ORDER BY
		`images`.`location_id`,
		`images`.`order`", array( $this->session->userdata('user_id'), $location_id ));
		if($result->num_rows() ){
			foreach ($result->result() as $row){
				array_push($pics, $row->id.": {
				lid   : ".$row->location_id.",
				d800  : '".$row->full."',
				d128  : '".$row->mid."',
				d32   : '".$row->small."',
				file  : '".$row->filename."',
				cm    : '".$row->comment."',
				ofile : '".$row->orig_filename."',
				act   : ".$row->active." }");
			}
		}
		return "var imgs = {\n".implode($pics,",\n")."\n}";
	}

	function _photoeditor_locations(){
		$user = $this->session->userdata('user_id');
		$locs = array("- выберите размещение -");
		$result=$this->db->query("SELECT 
		locations.id,
		CONCAT_WS(' ',locations_types.name,locations.location_name,locations.address,' (',COUNT(images.id),'фото)') AS string
		FROM
		locations
		LEFT OUTER JOIN locations_types ON (locations.`type` = locations_types.id)
		LEFT OUTER JOIN images ON (locations.id = images.location_id)
		WHERE
		(locations.owner = ?)
		GROUP BY
		locations.id
		ORDER BY
		locations_types.name,
		locations.parent", array($user));
		if($result->num_rows()){
			foreach ($result->result() as $row){
				$locs[$row->id] = $row->string;
			}
		}
		return $locs;
	}
/*
#
######################### end of photoeditor section ############################
#
#*/
######################### start of help section ############################
#
	function _get_help_page($page=0){
		$output=Array();
		$topics=Array();
		$result=$this->db->query("SELECT 
		`sheets`.`header`,
		`sheets`.`id`
		FROM
		`sheets`
		where
		`sheets`.`root` = 2
		ORDER BY `sheets`.`pageorder`");
		if($result->num_rows()){
			foreach ($result->result() as $row){
				if($row->id == $page || (!$page && !sizeof($output))){
					$result2=$this->db->query("SELECT `sheets`.`text` FROM `sheets` WHERE `sheets`.`id` = ?", array($row->id));
					$row2=$result2->row();
					array_push($output,'<div class="headerrow">'.$row->header.'</div><div class="help_text">'.$row2->text.'</div>');
				}
				array_push($topics,'<div class="help_topic"><a href="/admin/help/'.$row->id.'">'.(sizeof($topics)+1).'. '.$row->header.'</a></div>');

			}
		}
		return '<h3>Разделы справки</h3><div class="help_topics">'.implode($topics,"\n").'</div>'.implode($output,"\n");
	}

#
######################### end of help section ############################
#
######################### start of comments section ############################
#
	function _comments_show($user_id=0){
		if(!$user_id){
			return "";
		}
		$comments=Array();
		$result=$this->db->query("SELECT 
		comments.auth_name,
		comments.contact_info,
		comments.`text`,
		DATE_FORMAT(comments.`date`, '%d.%c.%Y %H:%i:%s') AS `date`,
		INET_NTOA(comments.ip) AS ip,
		comments.uid,
		comments.hash as `id`,
		comments.status,
		CONCAT_WS(' ',locations_types.name,locations.location_name) AS location_name
		FROM
		comments
		INNER JOIN locations ON (comments.location_id = locations.id)
		LEFT OUTER JOIN locations_types ON (locations.`type` = locations_types.id)
		WHERE
		(locations.owner = ?) AND
		comments.status <> 'D'
		order by
		location_name ASC,
		date DESC",array($user_id));
		
		if($result->num_rows()){
			foreach($result->result_array() as $row){
				$row['control']=$this->load->view('fragments/comment_control',$row,true);
				if($row['status']=="A"){
					array_push($comments,$this->load->view('admin/comment_layout',$row,true));
				}
				if($row['status']=="N"){
					array_push($comments,$this->load->view('admin/comment_layout',$row,true));
				}
			}
		}
		$act['comments'] = implode($comments,"<BR>\n");
		return $this->load->view('admin/comments',$act,true);
	}
}

/* End of file adminmodel.php */
/* Location: ./system/application/controllers/adminmodel.php */
