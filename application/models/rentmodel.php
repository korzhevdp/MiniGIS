<?php
class Rentmodel extends CI_Model{
	function __construct(){
		parent::__construct();
		//$this->output->enable_profiler(TRUE);
	}
	function _list_build(){
		$output = Array();
		if (strlen($this->session->userdata('user_id')) !== 40){
			return "Некорректный логин";
		}
		$result = $this->db->query("SELECT 
		CONCAT(locations_types.name,' ',locations.location_name,' ',locations.address) as name,
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
		(locations_types.object_group = 1)",array($this->session->userdata('user_id')));
		if($result->num_rows()){
			foreach($result->result() as $row){
				$class = ($row->active) ? "" : "btn-inverse";
				$func = ($row->active) ? "loc_deactivate" : "loc_activate";
				$act_word = ($row->active) ? "Деактивировать" : "Активировать";
				$string='<div class="btn-group span3">
				<button class="btn dropdown-toggle '.$class.'" id="loc'.$row->hash.'" data-toggle="dropdown"><i class="icon-home"></i>&nbsp;'.$row->name.'&nbsp;&nbsp;<span class="caret"></span></button>
				<ul class="dropdown-menu">
					<li onclick="location_edit(\''.$row->hash.'\')"><a href="#"><i class="icon-pencil"></i>&nbsp;Править описание предложения</a></li>
					<li><a href="#"><i class="icon-signal"></i>&nbsp;Править цены</a></li>
					<li id="locact'.$row->hash.'" onclick="loc_activate(\''.$row->hash.'\')"><a href="#"><i class="icon-ban-circle"></i>&nbsp;'.$act_word.' предложение</a></li>
					<li class="divider"></li>
					<li><a href="#"><i class="i"></i>&nbsp;Фигня какая-то</a></li>
				</ul>
				</div>';
				array_push($output,$string);
			}
		}
		return implode($output,"\n");
	}
}
#
/* End of file rentmodel.php */
/* Location: ./system/application/models/rentmodel.php */