<?php
class Nodal extends CI_Controller{
	function __construct(){
		parent::__construct();
	}

/* UTILS */
	function dependencycalc() { //запрос из map_calc.js / locations_container.php
		$ids    = implode($this->input->post("ids"), ", ");
		//print $ids;
		$out    = array();
		$result = $this->db->query("SELECT DISTINCT 
		properties_list.id,
		locations.coord_y,
		locations.coord_array,
		locations.location_name
		FROM
		properties_list
		RIGHT OUTER JOIN properties_assigned ON (properties_list.id = properties_assigned.property_id)
		RIGHT OUTER JOIN locations ON (properties_assigned.location_id = locations.id)
		LEFT OUTER JOIN locations_types ON (locations.`type` = locations_types.id)
		WHERE
		(locations_types.pr_type = 3) AND 
		(properties_list.linked) AND 
		(properties_list.id IN (".$ids."))");
		if ($result->num_rows()) {
			foreach($result->result() as $row) {
				array_push($out, $row->id." : { ym : '".$row->coord_y."', um : ".(strlen($row->coord_array) ? $row->coord_array : "''" )." }");
			}
		}
		print "data = {\n".implode($out, ",\n")."\n}";
	}

	function gpe($type = 0, $rand = 0){
		$out    = array();
		$result = $this->db->query("SELECT
		`locations`.coord_y as coord,
		IF(LENGTH(`locations`.`style_override`) > 1, `locations`.`style_override`, `locations_types`.attributes) as `attributes`,
		`locations_types`.name,
		`locations`.id
		FROM
		`locations`
		INNER JOIN `locations_types` ON (`locations`.`type` = `locations_types`.id)
		WHERE
		`locations`.owner = ?
		AND `locations_types`.id = ?", array(
			$this->session->userdata('user_id'),
			$type
		));
		if($result->num_rows()) {
			foreach($result->result() as $row){
				$string = $row->id." : { attr : '".$row->attributes."' , description : '".$row->name."', ttl: ".$row->id.", contact : '".$row->name."' , coord : '".$row->coord."' , pr : 1 }";
				array_push($out, $string);
			}
		}
		//header('Content-type: text/html; charset=windows-1251');
		print "bo = { ".implode($out, ",\n")."\n}";
	}

	function getimagelist() {
		$lid = $this->input->post("loc");
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
				<img src="/uploads/full/'.$lid."/".$row->img.'" width="'.$dim[0].'" height="'.$dim[1].'" alt=""/>
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

	public function get_objects_by_type(){
		if(!$this->session->userdata('user_id')){
			print "Время работы в текущей сессии истекло.<br>Завершите работу и введите имя пользователя и пароль заново";
		} else {
			$this->load->model('editormodel');
			$result = $this->editormodel->get_objects_by_type();
			print $result;
		}
	}

	public function get_object_list_by_type(){
		if(!$this->session->userdata('user_id')){
			print "Время работы в текущей сессии истекло.<br>Завершите работу и введите имя пользователя и пароль заново";
		} else {
			$this->load->model('editormodel');
			$result = $this->editormodel->get_object_list_by_type();
			print $result;
		}
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