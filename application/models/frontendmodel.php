<?php
class Frontendmodel extends CI_Model{
	function __construct(){
		parent::__construct();
	}

	public function get_properties($location_id){ // CI_C_Gis
		$result=$this->db->query("SELECT 
		CONCAT_WS(' ', locations_types.name, locations.location_name) AS name,
		locations.`parent`,
		locations.`type`,
		locations.`coord_y`,
		locations.`comments`,
		locations_types.`has_child`,
		`objects_groups`.`function`
		FROM
		locations_types
		INNER JOIN locations ON (locations_types.id = locations.`type`)
		INNER JOIN `objects_groups` ON (locations_types.object_group = `objects_groups`.id)
		WHERE
		(locations.id = ?)", array($location_id));
		if($result->num_rows()){
			foreach ($result->result_array() as $row){
				return $row;
			}
		}
	}

	public function get_cached_content($location_id){
		//$this->output->enable_profiler(TRUE);
		$output = "";
		$cachefile = "/var/www/html/minigis/application/views/cache/locations/location_".$location_id.".src";
		if(file_exists($cachefile)){
			$output = file_get_contents($cachefile);
		}else{
			$output = $this->cachemodel->cache_location($location_id, 1);
		}
		return $output;
	}

	private function traverse_sheet_tree($doc_id){
		$traverse_string = array();
		$result          = $this->db->query("SELECT 
		`sheets`.id,
		`sheets`.header,
		`sheets`.parent
		FROM
		`sheets`
		WHERE `sheets`.`id` = ?", array($doc_id));
		if($result->num_rows()){
			$row         = $result->row();
			array_push($traverse_string, '<li><a href="/page/docs/'.$row->id.'">'.$row->header.'</a></li>');
			$itemclass   = ($row->id == $doc_id) ? ' class="active" ' : "";
			if($row->id >= 1){
				$result = $this->db->query("SELECT 
				`sheets`.id,
				`sheets`.header,
				`sheets`.parent
				FROM
				`sheets`
				WHERE `sheets`.`id` = ?", array($row->parent));
				if($result->num_rows()){
					$row=$result->row();
					
					array_push($traverse_string,'<li><a href="/page/docs/'.$row->id.'">'.$row->header.'</a></li>');
				}
			}
		}
		array_push($traverse_string, '<li'.$itemclass.'><a href="/">'.$this->config->item('site_friendly_url').'</a></li>');
		return '<!-- bc --><ul class="breadcrumb">'.implode(array_reverse($traverse_string), '<span class="divider">&nbsp;/&nbsp;</span>').'</ul><!-- bc -->';
	}
	
	public function show_doc($doc_id){
		$output = "";
		$result = $this->db->query("SELECT 
		`sheets`.header,
		`sheets`.text,
		`sheets`.redirect,
		`sheets`.date
		FROM
		`sheets`
		WHERE `sheets`.`id` = ?", array($doc_id));
		if($result->num_rows()){
			$row = $result->row_array();
			if(strlen($row['redirect'])){
				$this->load->helper('url');
				redirect($row['redirect']);
			}
			$row['doc_traverse'] = $this->traverse_sheet_tree($doc_id);
			$output = $this->load->view($this->session->userdata('lang').'/frontend/doc_view', $row, true);
		}else{
			$output = "Запрошенного Вами документа не существует.";
		}
		return $output;
	}

	public function comments_show($location_id = 0){
		if(!$location_id){
			return "";
		}
		$comments = array();
		$result   = $this->db->query("SELECT
		comments.`hash` as id,
		comments.auth_name,
		comments.contact_info,
		comments.text,
		DATE_FORMAT(comments.date,'%d.%m.%Y') as date,
		comments.status,
		comments.uid
		FROM
		comments
		WHERE comments.location_id = ?", array($location_id));
		if($result->num_rows()){
			foreach($result->result_array() as $row){
				($this->session->userdata('common_user') == $row['uid']) ? $row['status'] = "A" : "";
				$row['control'] = "";
				if ($row['status'] === "A") {
					array_push($comments, $this->load->view('fragments/comment_layout', $row, true));
				}
			}
		}
		$act = array(
			'comments'    => (sizeof($comments)) ? implode($comments,"<br>\n") : "<h1><small>Пока здесь тихо</small></h1>",
			'location_id' => $location_id,
			'captcha'     => $this->usefulmodel->captcha_make()
		);
		return $this->load->view('doc/comments', $act, true);
	}
}
/* End of file frontendmodel.php */
/* Location: ./system/application/models/frontendmodel.php */