<?php
class Ajaxutils extends CI_Controller{
	function __construct(){
		parent::__construct();
	}

/* UTILS */
	function gpe($type=0, $rand=0){
		$out=array();
		$result=$this->db->query("SELECT 
		`locations`.coord_y as coord,
		IF(LENGTH(`locations`.`style_override`) > 1, `locations`.`style_override`, `locations_types`.attributes) as `attributes`,
		`locations_types`.name,
		`locations`.id
		FROM
		`locations`
		INNER JOIN `locations_types` ON (`locations`.`type` = `locations_types`.id)
		WHERE
		`locations`.owner = ? AND
		`locations_types`.id = ?", array($this->session->userdata('user_id'),$type));
		if($result->num_rows()){
			foreach($result->result() as $row){
				$string = $row->id." : { attr : '".$row->attributes."' , description : '".$row->name."', ttl: ".$row->id.", contact : '".$row->name."' , coord : '".$row->coord."' , pr : 1 }";
				array_push($out, $string);
			}
		}
		//header('Content-type: text/html; charset=windows-1251');
		print "bo = { ".implode($out, ",\n")."\n}";
	}

	function getimagelist($lid=0){
		$lid = $this->input->post("picref");
		$out = array();
		$result=$this->db->query("SELECT 
			`images`.filename as img,
			`images`.full,
			`images`.`comment`
			FROM
			`images`
			WHERE
			`images`.`location_id` = ?", array($lid));
		if($result->num_rows()){
			foreach($result->result() as $row){
				$dim    = explode(",", $row->full);
				$act    = (!sizeof($out)) ? 'active' : '';
				$string = '<div class="item '.$act.'">
				<img src="/uploads/full/'.$row->img.'" width="'.$dim[0].'" height="'.$dim[1].'" alt=""/>
				<div class="carousel_annot">
					<h5>'.$row->comment.'</h5>
				</div>
				</div>';
				array_push($out, $string);
			}
		}else{
			array_push($out,'<div class="item"><img src="/uploads/full/nophoto.jpg" width="128" height="128" alt=""/><div class="carousel_annot"><h5>Изображения отсутствуют</h5></div></div>');
		}
		//array_unshift($out,'<div class="item active"><h2>Фотографии объекта ('.(sizeof($out)).')</h2></div>');
		//header('Content-type: text/html; charset=windows-1251');
		print implode($out, "\n");
	}

	function dependencycalc($lid,$input) { //запрос из map_calc.js / locations_container.php
		$locs = implode(explode("_",$input),",");
		$out=array();
		array_push($out, "var set = [];");
		$result=$this->db->query("SELECT 
		`locations_types`.pr_type,
		`locations`.coord_y,
		`locations`.id
		FROM
		`locations`
		INNER JOIN `locations_types` ON (`locations`.`type` = `locations_types`.id)
		WHERE
		`locations`.`id` = ?
		UNION
		SELECT 
		locations_types.pr_type,
		locations.coord_y,
		properties_list.id
		FROM
		locations
		INNER JOIN locations_types ON (locations.`type` = locations_types.id)
		INNER JOIN `properties_list` ON (locations.id = `properties_list`.linked)
		WHERE
		(`properties_list`.`id` IN (".$locs."))",array($lid));
		if($result->num_rows()){
			foreach($result->result() as $row){
				array_push($out, "set.push([".$row->id.",".$row->pr_type.",'".$row->coord_y."']);");
			}
		}
		print implode($out,"\n");
	}

	public function get_objects_by_type(){
		if(!$this->session->userdata('user_id')){
			print "Время работы в текущей сессии истекло.<br>Завершите работу и введите имя пользователя и пароль заново";
			exit;
		}
		$this->load->model('editormodel');
		$result = $this->editormodel->get_objects_by_type();
		print $result;
	}

	public function get_object_list_by_type(){
		if(!$this->session->userdata('user_id')){
			print "Время работы в текущей сессии истекло.<br>Завершите работу и введите имя пользователя и пароль заново";
			exit;
		}
		$this->load->model('editormodel');
		$result = $this->editormodel->get_object_list_by_type();
		print $result;
	}

	function moraleup(){ //запрос из main_page_content.js 
		$file = "./morale.txt";
		$sum = implode(file($file),'');
		$open = fopen($file,"w");
		fputs($open,++$sum);
		fclose($open);
		print $sum;
	}
}

/* End of file ajax.php */
/* Location: ./system/application/controllers/ajax.php */