<?php
class Transmodel extends CI_Model{
	function __construct(){
		parent::__construct();
	}

	public function translations($mode = "groups"){
		$output = array();
		$queries = array(
			"groups"		=> "SELECT objects_groups.id, objects_groups.name FROM objects_groups ORDER BY objects_groups.name",
			"categories"	=> "SELECT `locations_types`.name, `locations_types`.id FROM `locations_types` WHERE `locations_types`.pl_num > 0",
			"properties"	=> "SELECT `properties_list`.selfname AS name, `properties_list`.id FROM `properties_list` WHERE LENGTH(`properties_list`.`selfname`)",
			"labels"		=> "SELECT DISTINCT `properties_list`.label AS id, `properties_list`.label AS name FROM `properties_list` ORDER BY `properties_list`.label",
			"articles"		=> "SELECT `sheets`.`id`, `sheets`.`header` AS name FROM `sheets` ORDER BY name ASC",
			"maps"			=> "SELECT `map_content`.name, `map_content`.id FROM `map_content` ORDER BY `map_content`.name ASC"
		)
		$result = $this->db->query($queries[$mode]);
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
					$readonly = ($key === $this->config->item("native_lang")) ? ' readonly="readonly"' : '';
					$value = (isset($groups[$row->id][$key])) ? $groups[$row->id][$key] : '';
					if ($mode === 'labels') {
						$cell = "\t".'<td><input type="text" name="'.$mode.'['.$id.']['.$key.']" class="translation" ref="'.$row->id.'" lang="'.$key.'" value="'.$value.'" placeholder="Нет перевода"'.$readonly.'></td>';
					} else {
						$cell = "\t".'<td><input type="text" name="'.$mode.'['.$row->id.']['.$key.']" class="translation" ref="'.$row->id.'" lang="'.$key.'" value="'.$value.'" placeholder="Нет перевода"'.$readonly.'></td>';
					}
					array_push($string, $cell);
				}
				if($mode === 'labels'){
					$cell = "\t".'<td class="hide"><input type="hidden" name="'.$mode.'['.$id.'][original]" class="translation" ref="'.$row->id.'" lang="'.$key.'" value="'.$row->name.'" placeholder="Нет перевода"></td>';
					array_push($string, $cell);
				}
				array_push($table, "<tr>\n".implode($string, "\n")."\n</tr>");
			}
		}
		return $table;
	}

	public function trans_save(){
		$output = array();
		$files  = array(
			'groups'		=> 'application/config/translations_g.php',
			'categories'	=> 'application/config/translations_c.php',
			'properties'	=> 'application/config/translations_p.php',
			'articles'		=> 'application/config/translations_a.php',
			'maps'			=> 'application/config/translations_m.php',
			'labels'		=> 'application/config/translations_l.php'
		);
		$filename = $files[$this->input->post("type")];
		if($this->input->post("type") === 'labels'){
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