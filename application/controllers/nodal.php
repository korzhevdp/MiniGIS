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

	function getimagelist($lid = 0) {
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

	public function get_trapping(){
		//$this->output->enable_profiler(TRUE);
		$output = array();
		$points = array();
		foreach($this->input->post('geometry') as $order=>$val){
			$tolerance    = $this->input->post("tolerance");
			$key          = substr($val[0], 0, (strpos($val[0], ".") + $tolerance + 1)).",".substr($val[1], 0, (strpos($val[1], ".") + $tolerance + 1));
			$coord        = substr($val[0], 0, (strpos($val[0], ".") + $tolerance + 1))."%,".substr($val[1], 0, (strpos($val[1], ".") + $tolerance + 1))."%";
			$output[$key] = "`locations`.`coord_y` LIKE '".$coord."'";
			$points[$key] = $order;
		}
		$result = $this->db->query("SELECT
		locations.id,
		locations.location_name,
		`locations_types`.name,
		locations.coord_y,
		SUBSTR(locations.coord_y, 1, LOCATE('.', locations.coord_y) + ?) AS lon,
		SUBSTR(locations.coord_y,
			LOCATE(',', locations.coord_y) + 1,
			LOCATE('.', locations.coord_y, LOCATE(',', locations.coord_y)) - LOCATE(',', locations.coord_y) + ?
		) AS lat
		FROM
		`locations_types`
		RIGHT OUTER JOIN locations ON (`locations_types`.id = locations.`type`)
		WHERE
		`locations_types`.id = ? AND (
		".implode($output, "\n OR ").")
		ORDER BY `locations`.id", array($tolerance, $tolerance, $this->input->post("types")));
		if ($result->num_rows()) {
			$out = array();
			foreach($result->result() as $row) {
				$vertex = (isset($points[$row->lon.",".$row->lat])) ? $points[$row->lon.",".$row->lat] : "";
				array_push($out, $row->id.": { real: [".$row->coord_y."], matched: '".$row->lon.",".$row->lat."', vertex: ".$vertex.", name: '".preg_replace("/'/","&quot;", $row->location_name)."', tn: '".preg_replace("/'/", "&quot;", $row->name)."'}");
			}
			print "data = {\n".implode($out, ",\n")."}";
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

/* End of file nodal.php */
/* Location: ./system/application/controllers/nodal.php */