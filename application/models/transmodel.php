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
		);
		$groups = $this->config->item($mode);
		$table  = $this->get_translation_table($this->db->query($queries[$mode]), $groups, $mode);
		$output['table'] = implode($table, "\n");
		$output['mode']  = $mode;
		return $this->load->view('admin/translations', $output, true);
	}

	private function get_translation_table_header(){
		$string = array();
		foreach($this->config->item("lang") as $key=>$val) {
			$cell = '<th><img src="'.$this->config->item("api").'/images/flag_'.$key.'.png" alt="">'.$val.'</th>';
			array_push($string, $cell);
		}
		return array( "<tr>".implode($string, "\n")."</tr>" );
	}

	private function get_translation_table($result, $groups, $mode){
		$table  = $this->get_translation_table_header();
		if ($result->num_rows()) {
			foreach($result->result() as $row) {
				$string = array();
				$table_len = sizeof($table);
				foreach($this->config->item("lang") as $key=>$val) {
					$readonly = ($key === $this->config->item("native_lang")) ? ' readonly="readonly"' : '';
					$value    = ($key === $this->config->item("native_lang")) ? $row->name : ((isset($groups[$row->id][$key])) ? $groups[$row->id][$key] : '');
					$cell     = ($mode === 'labels')
						? "\t".'<td><input type="text" name="'.$mode.'['.$table_len.']['.$key.']" class="translation" ref="'.$row->id.'" lang="'.$key.'" value="'.$value.'" placeholder="Нет перевода"'.$readonly.'></td>'
						: "\t".'<td><input type="text" name="'.$mode.'['.$row->id.']['.$key.']" class="translation" ref="'.$row->id.'" lang="'.$key.'" value="'.$value.'" placeholder="Нет перевода"'.$readonly.'></td>';
					array_push($string, $cell);
				}
				if($mode === 'labels'){
					$cell = "\t".'<td class="hide"><input type="hidden" name="'.$mode.'['.$table_len.'][original]" class="translation" ref="'.$row->id.'" lang="'.$key.'" value="'.$row->name.'" placeholder="Нет перевода"></td>';
					array_push($string, $cell);
				}
				array_push($table, "<tr>\n".implode($string, "\n")."\n</tr>");
			}
		}
		return $table;
	}

	private function make_common_file($type) {
		$output = array();
		if (sizeof($this->input->post($this->input->post("type")))) {
			foreach ($this->input->post($this->input->post("type")) as $key=>$val) {
				$field = ($type === 'labels') ? $val['original'] : $key ;
				array_push($output, $this->return_line_of_translation_file($val, $field));
			}
		}
		return $output;
	}

	private function return_line_of_translation_file($val, $key) {
		$input = array();
		foreach($val as $lang=>$word) {
			array_push($input, "'".addslashes(trim($lang))."' => '".addslashes(trim($word))."'");
		}
		return "\t'".$key."' => array( ".implode($input, ",")." )";
	}

	public function trans_save(){
		$this->output->enable_profiler(TRUE);
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
		$output = $this->make_common_file($this->input->post("type"));
		$config = $this->load->view('admin/translations_template', array('group' => $this->input->post("type"), 'content' => implode($output, ",\n")), true);
		$this->load->helper('file');
		write_file($filename, "<".$config, "w");
	}

}
/* End of file adminmodel.php */
/* Location: ./application/models/transmodel.php */