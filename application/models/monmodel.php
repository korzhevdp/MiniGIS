<?php
class Monmodel extends CI_Model{
	function __construct(){
		parent::__construct();
	}

	public function current_get($group){
		$out = array();
		$result=$this->db->query("SELECT
		IF(LENGTH(locations.address), locations.address, 'Нет адреса') AS address,
		IF(LENGTH(`locations`.`style_override`) > 1, `locations`.`style_override`, IF(LENGTH(locations_types.attributes), locations_types.attributes, 'default#houseIcon')) AS attr,
		CONCAT_WS(' ', IF(locations_types.id IN(10, 12, 13, 15), 'объект', locations_types.name),locations.location_name) AS location_name,
		TRIM(locations.coord_y) AS coord_y,
		locations.active,
		locations_types.pr_type,
		DATE_FORMAT(locations.date, '%d.%m.%Y %H ч. %i м. %S с.') AS date,
		locations.id,
		CONCAT('/monitoring/object/', locations.id) AS link,
		IF(LENGTH(locations.contact_info), locations.contact_info, 'контактная информация отсутствует') AS contact_info
		FROM
		locations_types
		INNER JOIN locations ON (locations_types.id = locations.`type`)
		WHERE
		(locations_types.object_group = ?) AND
		(locations.active OR DATEDIFF(NOW(), locations.date) < 7 ) AND
		LENGTH(locations.coord_y)
		ORDER BY locations.parent ASC", array($group));
		if($result->num_rows()){
			//header("Content-type: text/html; charset=windows-1251");
			foreach($result->result() as $row){
				$string="mon[".$row->id."] = { description: '".$row->address."', active: ".$row->active.", date: '".$row->date."', name: '".$row->location_name."', attr: '".$row->attr."', pr: ".$row->pr_type.", coord: '".$row->coord_y."', contact: '".$row->contact_info."', link: '".$row->link."' };";
				array_push($out,$string);
			}
			array_unshift($out, "mon=[]");
			print implode($out, "\n");
		}else{
			print "alert('Nothing Found!')";
		}
	}

	function object_get($location_id=0){
		$result=$this->db->query("SELECT 
		locations.location_name,
		locations_types.name,
		locations.contact_info as contact,
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
			$row['images']="Нету картинок";
			$row['location']=$location_id;
			return $this->load->view('frontend/location_gis_view',$row,true);
		}else{
			return "Данные по запрошенному объекту не найдены";
		}
	}

}

/* End of file adminmodel.php */
/* Location: ./system/application/controllers/adminmodel.php */