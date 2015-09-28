<?php
class Reconcile extends CI_Controller{
	function __construct(){
		parent::__construct();
		$this->load->helper('url');
	}
	/*
	удаляет из `properties_assigned` все ссылки на свойства тождественные объектам и заменяет их на новые. 
	Создано на случай, если объекты теряют признак, показывающий на принадлежность их к классу объектов
	*/
	public function reconcile() {
		$result = $this->db->query("DELETE
		FROM `properties_assigned`
		WHERE 
		`properties_assigned`.`property_id` IN (
			SELECT
			`locations_types`.pl_num
			FROM
			`locations_types`
		)");
		$run1 = $this->db->affected_rows();
		$result = $this->db->query("INSERT INTO `properties_assigned` (
		`properties_assigned`.`property_id`,
		`properties_assigned`.`location_id`
		)
		SELECT
		`locations_types`.pl_num,
		`locations`.id
		FROM
		`locations`
		INNER JOIN `locations_types` ON (`locations`.`type` = `locations_types`.id)");
		$run2 = $this->db->affected_rows();
		print "Reconcillation DONE<br>Deleted: ".$run1.",<br>Inserted: ".$run2;
	}
	/*
	Создано для унификации признаков между группами, имеющие одинаковые названия.
	Вставляет в `properties_bindings` минимальные идентификаторы одинаково названных объектов с указанием принадлежности к группе
	*/
	public function restore_property_bindings() {
		$output = $this->collect_properties();
		$this->db->query("INSERT INTO
		`properties_bindings`(
			property_id,
			groups,
			searchable
		) VALUES ".implode($output, ", "));
	}

	private function collect_properties(){
		$output = array();
		$result = $this->db->query("SELECT
		`properties_list`.id,
		`properties_list`.selfname,
		`properties_list`.object_group
		FROM
		`properties_list`
		order by `properties_list`.id");
		$input = array();
		if($result->num_rows()){
			foreach($result->result() as $row){
				if(!isset($input[$row->selfname])) { 
					$input[$row->selfname] = array('lowest_id' => $row->id);
				}
				array_push($input[$row->selfname], $row->object_group);
			}
		}
		
		
		foreach($input as $key => $data) {
			$id = $data['lowest_id'];
			unset($data['lowest_id']);
			foreach($data as $val) {
				array_push($output, "(".$id.", ".$val.", 1)");
			}
			
		}
		return $output;
	}

	public function redux(){
		$input = array();
		$result = $this->db->query("SELECT 
		`properties_list`.selfname,
		`properties_list`.id
		FROM
		`properties_list`
		ORDER BY `properties_list`.`selfname`, `properties_list`.`id`");
		if ($result->num_rows()) {
			foreach ($result->result() as $row) {
				if (!isset($input[$row->selfname])) {
					$input[$row->selfname] = array();
				}
				array_push($input[$row->selfname], $row->id);
			}
		}

		foreach ($input as $val) {
			if (sizeof($val) > 1) {
				$target = $val[0];
				$src    = implode(array_slice($val, 1), ", ");
				$this->db->query("UPDATE
				`properties_assigned`
				SET
				`properties_assigned`.property_id = ?
				Where
				`properties_assigned`.property_id IN (".$src.")", array($target));
				$this->db->query("DELETE FROM
				`properties_list`
				WHERE
				`properties_list`.id IN (".$src.")", array($target));
			}
		}
	}

	// user-calls for caching model
	public function cachemap($mode = "browser"){
		$this->load->model('cachemodel');
		$this->cachemodel->cache_selector_content($mode);
	}

	public function cachemenu(){
		$this->load->model('cachemodel');
		$this->cachemodel->cache_docs(1, 'browser');
	}

	public function cacheloc($loc_id){
		$this->load->model('cachemodel');
		$this->cachemodel->cache_location($loc_id, 0, 'browser');
	}

}
/* End of file reconcile.php */
/* Location: ./system/application/controllers/reconcile.php */