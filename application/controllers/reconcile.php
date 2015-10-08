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
			$lowest_id = $data['lowest_id'];
			unset($data['lowest_id']);
			foreach($data as $val) {
				array_push($output, "(".$lowest_id.", ".$val.", 1)");
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
	/*
	генерирует "длинный" список стилей объектов из форматного файла style_src.txt
	универсальный для leaflet и ymaps
	и пишет его в файл JS-стиля
	*/

	private function generate_by_type($val, $sizes){
		//print $sizes[0]."<br>";
		$data = explode("\t", $val);
		if(sizeof($data) === 4) {
			$imgsize = $sizes[1];
			$offsets = $sizes[2];
			$catname = $sizes[3]."#".str_replace(chr(10), "", str_replace(chr(13), "", $data[3]));
			return "userstyles['".$catname."'] = {\n\ticonUrl         : '/gisicons/".$sizes[0]."/".$data[0]."',\n\ticonImageHref   : '/gisicons/".$sizes[0]."/".$data[0]."',\n\ticonSize        : ".$imgsize.",\n\ticonImageSize   : ".$imgsize.",\n\ticonImageOffset : ".$offsets.",\n\ttitle           : '".$data[2]."',\n\ttype            : 1\n};";
		}
		if(sizeof($data) === 5) {
			$imgsize = $data[1];
			$offsets = $data[4];
			$catname = str_replace("'", "", $data[2]);
			$data[0] = str_replace("'", "", trim($data[0]));
			$data[0] = str_replace("[api_domain + /images/", "", trim($data[0]));
			return "userstyles['".$catname."'] = {\n\ticonUrl         : '/gisicons/".trim($data[0])."',\n\ticonImageHref   : '/gisicons/".trim($data[0])."',\n\ticonSize        : ".trim($imgsize).",\n\ticonImageSize   : ".trim($imgsize).",\n\ticonImageOffset : ".trim($offsets).",\n\ttitle           : '".str_replace("'", "", trim($data[3]))."',\n\ttype            : 1\n};";
		}
	}

	public function generate_js_styles(){
		$this->load->helper("file");
		if(!$file = read_file("scripts/styles_src.txt")) {
			print "no file exists";
			return false;
		}
		$sizes = array(
			32 => array( 32, '[26, 32]', "[-13, -32]", "free" ),
			48 => array( 48, '[39, 48]', "[-19, -48]", "paid" )
		);
		$output = array();
		$file = explode("\n", $file);
		foreach ($file as $val) {
			array_push($output, $this->generate_by_type($val, $sizes[32]));
			array_push($output, $this->generate_by_type($val, $sizes[48]));
		}
		array_push($output, "function styleAddToStorage(src) {
			var a;
			for (a in src) {
				if (src.hasOwnProperty(a)) {
					ymaps.option.presetStorage.add(a, src[a]);
				}
			}
		}");
		write_file('scripts/styles2.js', "var userstyles = {};\n".implode($output, "\n"));
	}

}
/* End of file reconcile.php */
/* Location: ./system/application/controllers/reconcile.php */