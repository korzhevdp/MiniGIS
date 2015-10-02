<?php
class Transmodel extends CI_Model{
	function __construct(){
		parent::__construct();
	}

	public function translations($mode = "groups"){
		//$this->output->enable_profiler(TRUE);
		$output = array();

		if ($mode === "groups"){
			$result = $this->db->query("SELECT
			objects_groups.id,
			objects_groups.name
			FROM
			objects_groups
			ORDER BY objects_groups.name");
		}
		if ($mode === "categories"){
			$result = $this->db->query("SELECT 
			`locations_types`.name,
			`locations_types`.id
			FROM
			`locations_types`
			WHERE `locations_types`.pl_num > 0");
		}
		if ($mode === "properties"){
			$result = $this->db->query("SELECT 
			`properties_list`.selfname AS name,
			`properties_list`.id
			FROM
			`properties_list`
			WHERE LENGTH(`properties_list`.`selfname`)");
		}
		if ($mode === "labels"){
			$result = $this->db->query("SELECT DISTINCT
			`properties_list`.label AS id,
			`properties_list`.label AS name
			FROM
			`properties_list`
			ORDER BY `properties_list`.label");
		}
		if ($mode === "articles"){
			$result = $result=$this->db->query("SELECT 
			`sheets`.`id`,
			`sheets`.`header` AS name
			FROM
			`sheets`
			ORDER BY name ASC");
		}
		if ($mode === "maps"){
			$result = $this->db->query("SELECT
			`map_content`.name,
			`map_content`.id
			FROM
			`map_content`
			ORDER BY `map_content`.name ASC");
		}
		$groups = $this->config->item($mode);
		$table  = $this->get_translation_table($result, $groups, $mode);
		$output['table'] = implode($table, "\n");
		$output['mode']  = $mode;
		return $this->load->view('admin/translations', $output, true);
	}

	private function get_translation_table($result, $groups, $mode){
		$table  = array();
		$string = array();
		foreach($this->config->item("lang") as $key=>$val) {
			$cell = '<th><img src="'.$this->config->item("api").'/images/flag_'.$key.'.png" alt="">'.$val.'</th>';
			array_push($string, $cell);
		}
		array_push($table, "<tr>".implode($string, "\n")."</tr>");
		if($result->num_rows()){
			foreach($result->result() as $row){
				$string = array();
				$id = sizeof($table);
				foreach($this->config->item("lang") as $key=>$val) {
					$readonly = "";
					if($key === $this->config->item("native_lang")){
						$value = $row->name;
						$readonly = ' readonly="readonly"';
					} else {
						$value = (isset($groups[$row->id][$key])) ? $groups[$row->id][$key] : '';
					}
					if($mode === 'labels'){
						$cell = "\t".'<td><input type="text" name="'.$mode.'['.$id.']['.$key.']" class="translation" ref="'.$row->id.'" lang="'.$key.'" value="'.$value.'" placeholder="��� ��������"'.$readonly.'></td>';
					}
					else{
						$cell = "\t".'<td><input type="text" name="'.$mode.'['.$row->id.']['.$key.']" class="translation" ref="'.$row->id.'" lang="'.$key.'" value="'.$value.'" placeholder="��� ��������"'.$readonly.'></td>';
					}
					array_push($string, $cell);

				}
				if($mode === 'labels'){
					$cell = "\t".'<td class="hide"><input type="hidden" name="'.$mode.'['.$id.'][original]" class="translation" ref="'.$row->id.'" lang="'.$key.'" value="'.$row->name.'" placeholder="��� ��������"></td>';
					array_push($string, $cell);
				}

				array_push($table, "<tr>\n".implode($string, "\n")."\n</tr>");
			}
		}
		return $table;
	}

	public function trans_save(){
		//$this->output->enable_profiler(TRUE);
		$output = array();
		if($this->input->post("type") === 'labels'){
			$filename = 'application/config/translations_l.php';
			if(sizeof($this->input->post($this->input->post("type")))){

				foreach($this->input->post($this->input->post("type")) as $key=>$val) {
					$orig = $val['original'];
					$input = array();
					foreach($val as $lang=>$word){
						$string = "'".addslashes($lang)."' => '".addslashes($word)."'";
						array_push($input, $string);
					}
					$string = "\t'".$orig."' => array( ".implode($input, ",")." )";
					array_push($output, $string);
				}
			}
		} else {
			if($this->input->post("type") === 'groups'){
				$filename = 'application/config/translations_g.php';
			}
			if($this->input->post("type") === 'categories'){
				$filename = 'application/config/translations_c.php';
			}
			if($this->input->post("type") === 'properties'){
				$filename = 'application/config/translations_p.php';
			}
			if($this->input->post("type") === 'articles'){
				$filename = 'application/config/translations_a.php';
			}
			if($this->input->post("type") === 'maps'){
				$filename = 'application/config/translations_m.php';
			}
			if(sizeof($this->input->post($this->input->post("type")))){
				foreach($this->input->post($this->input->post("type")) as $key=>$val) {
					$input = array();
					foreach($val as $lang=>$word){
						$string = "'".addslashes(trim($lang))."' => '".addslashes(trim($word))."'";
						array_push($input, $string);
					}
					$string = "\t".$key." => array( ".implode($input, ",")." )";
					array_push($output, $string);
				}
			}
		}

		$config = $this->load->view('admin/translations_template', array('group' => $this->input->post("type"), 'content' => implode($output, ",\n")), true);
		$this->load->helper('file');
		write_file($filename, "<".$config, "w");
	}

}
/* End of file adminmodel.php */
/* Location: ./application/models/transmodel.php */