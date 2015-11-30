<?php
class Docmodel extends CI_Model{
	function __construct(){
		parent::__construct();
	}

	private function doc_tree($root = 1, $sheet_id = 1) {
		$tree   = "";
		$result = $this->db->query("SELECT
		`sheets`.`id`,
		`sheets`.`parent`,
		`sheets`.`pageorder`,
		`sheets`.`header`,
		`sheets`.`active`,
		IF(`sheets`.`root` = 1, 'Ст', 'П') AS root
		FROM
		`sheets`
		".(($root) ? "" : " WHERE `sheets`.`root` = ? ")."
		ORDER BY `sheets`.`parent`,
		`sheets`.`pageorder`", array($root));

		if($result->num_rows() ){
			foreach($result->result() as $row){
				$style = array();
				if (!$row->active) {
					array_push($style, 'muted');
				}
				if ($row->id == $sheet_id) {
					array_push($style, "active");
				}
				if (!strlen($tree)) {
					$tree .=  "\..--".$row->parent."--";
				}
				$tree  = str_replace("--".$row->parent."--", '<a href="/admin/docs/'.$row->id.'"><div class="menu_item '.implode($style, " ").'">'.$row->id.". ".$row->header.' ('.$row->root.')</div></a><div class="menu_item_container">--'.$row->id.'--</div>--'.$row->parent.'--', $tree);
			}
		}
		$tree = preg_replace("/(\-\-)(\d+)(\-\-)/", "", $tree);
		return $tree;
	}

	private function get_redirects($redirect_id) {
		$redirect = array();
		$result   = $this->db->query("SELECT
		`map_content`.id,
		`map_content`.name
		FROM
		`map_content`");
		if($result->num_rows()){
			foreach($result->result() as $row){
				$selected = ('/map/simple/'.$row->id == $redirect_id) ? ' selected="selected"' : "";
				$string   = '<option value="/map/simple/'.$row->id.'"'.$selected.'>'.$row->name.'</option>';
				array_push($redirect, $string);
			}
		}
		return implode($redirect, "\n");
	}

	private function get_pageorder($sheet_id) {
		$pageorder = 10;
		$result = $this->db->query("SELECT 
		MAX(`sheets`.pageorder) + 10 AS pageorder
		FROM `sheets` 
		WHERE `sheets`.`parent` = ?", array($sheet_id));
		if($result->num_rows()){
			$row = $result->row();
			$pageorder = $row->pageorder;
		}
		return $pageorder;
	}

	public function doc_edit($sheet_id, $root = 1) {
		$redirect = array('<option value="">Не перенаправляется</option>');
		$act = array(
			'id'         => 1,
			'sheet_id'   => 1,
			'sheet_text' => 'Текст',
			'root'       => 0,
			'owner'      => 0,
			'header'     => 'Заголовок',
			'redirect'   => '',
			'date'       => '00.00.0000',
			'ts'         => 0,
			'active'     => 1,
			'is_active'  => "",
			'parent'     => 0,
			'pageorder'  => 0,
			'comment'    => 0,
			'doc_tree' => ''
		);
		$result = $this->db->query("SELECT 
		`sheets`.`id`,
		`sheets`.`text` as sheet_text,
		`sheets`.`root`,
		`sheets`.`owner`,
		`sheets`.`header`,
		`sheets`.`redirect`,
		`sheets`.`date`,
		`sheets`.`ts`,
		`sheets`.`active`,
		`sheets`.`parent`,
		`sheets`.`pageorder`,
		`sheets`.`comment`
		FROM
		`sheets`
		WHERE `sheets`.`id` = ?
		LIMIT 1", array($sheet_id));
		if($result->num_rows()){
			$act = $result->row_array();
			$act['doc_id']   = $sheet_id;
			$act['doc_tree'] = $this->doc_tree($root, $sheet_id);
			$act['is_active']  = ($act['active']) ? 'checked="checked"' : "";
		}
		$act['redirect'] = $this->get_redirects($act['redirect']);
		return $this->load->view('doc/doc_editor', $act, true);
	}
	
	private function insert_doc($doc_id) {
		$pageorder = $this->get_pageorder($doc_id);
		$active    = ($this->input->post('is_active')) ? 1 : 0;
		$this->db->query("INSERT INTO `sheets`(
			`sheets`.`text`,
			`sheets`.`root`,
			`sheets`.`date`,
			`sheets`.`header`,
			`sheets`.`pageorder`,
			`sheets`.active,
			`sheets`.parent,
			`sheets`.redirect,
			`sheets`.comment ) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ? )", array(
			preg_replace("/'/", '&quot;', $this->input->post('sheet_text',     TRUE)),
			$this->input->post('sheet_root',     TRUE),
			date("Y-m-d"),
			preg_replace("/'/", '&quot;', $this->input->post('sheet_header',   TRUE)),
			$pageorder,
			$active,
			$doc_id,
			$this->input->post('sheet_redirect', TRUE),
			preg_replace("/'/", '&quot;', $this->input->post('sheet_comment',  TRUE))
		));
	}

	private function update_doc($doc_id) {
		//$this->output->enable_profiler(TRUE);
		//return false;
		$active = ($this->input->post('is_active')) ? 1 : 0;
		$this->db->query("UPDATE `sheets` SET
			`sheets`.`ts`        = NOW(),
			`sheets`.`text`      = ?,
			`sheets`.`header`    = ?,
			`sheets`.`pageorder` = ?,
			`sheets`.`active`    = ?,
			`sheets`.`parent`    = ?,
			`sheets`.`redirect`  = ?,
			`sheets`.`comment`   = ?,
			`sheets`.`root`      = ?
		WHERE `sheets`.`id`        = ?", array(
			preg_replace("/'/", '&quot;', $this->input->post('sheet_text',     TRUE)),
			preg_replace("/'/", '&quot;', $this->input->post('sheet_header',   TRUE)),
			$this->input->post('pageorder',      TRUE),
			$active,
			$this->input->post('sheet_parent',   TRUE),
			$this->input->post('sheet_redirect', TRUE),
			preg_replace("/'/", '&quot;', $this->input->post('sheet_comment',  TRUE)),
			$this->input->post('sheet_root',     TRUE),
			$doc_id
		));
	}

	public function doc_save($doc_id) {
		if ($this->input->post('save_new')) {
			$this->insert_doc($doc_id);
		} else {
			$this->update_doc($doc_id);
		}
		$this->load->model('cachemodel');
		$this->cachemodel->menu_build(1, 0, 'file');
	}

	// comments
	function comments_show($user_id = 0) {
		$data  = array();
		$where = array();
		if (!$this->session->userdata("admin") || !$this->config->item("admin_can_edit_user_locations")) {
			array_push($where, "AND (locations.owner = ?)");
			array_push($data, $this->session->userdata("user_id"));
		}
		$comments = array();
		$result   = $this->db->query("SELECT
		comments.auth_name,
		comments.contact_info,
		comments.`text`,
		DATE_FORMAT(comments.`date`, '%d.%c.%Y %H:%i:%s') AS `date`,
		INET_NTOA(comments.ip) AS ip,
		comments.uid,
		comments.hash AS `id`,
		comments.status,
		CONCAT_WS(' ', locations_types.name, locations.location_name) AS location_name
		FROM
		comments
		INNER JOIN locations ON (comments.location_id = locations.id)
		LEFT OUTER JOIN locations_types ON (locations.`type` = locations_types.id)
		WHERE
		comments.status <> 'D'
		".implode($where, "\n")."
		ORDER BY
		location_name ASC, date DESC", $data);
		if ($result->num_rows()) {
			foreach($result->result_array() as $row) {
				$row['current'] = ($row['status'] === "A") ? "icon-eye-open" : "icon-eye-close";
				$row['title']   = ($row['status'] === "A") ? "Скрыть комментарий" : "Опубликовать комментарий";
				$row['control'] = $this->load->view('doc/comment_control', $row, true);
				array_push($comments, $this->load->view('doc/comment_layout', $row, true));
			}
		}
		$act = array(
			'comments' => implode($comments, "\n")
		);
		return $this->load->view('admin/comments', $act, true);
	}

	function addcomment() {
		$location_id = $this->input->post('location_id', true);
		$this->load->helper('url');
		if ( (string) $this->session->userdata('cpt') !== (string) md5(strtolower($this->input->post('cpt')))){
			redirect("/page/gis/".$location_id);
		}
		$name    = substr(strip_tags($this->input->post('name',     true)), 0, 250);
		$about   = substr(strip_tags($this->input->post('about',    true)), 0, 250);
		$text    = substr(strip_tags($this->input->post('send_text',true)), 0, 1000);
		$counter = 0;
		if(!strlen($name)){
			$name = "Неизвестный";
			$counter++;
		}
		if(!strlen($about)){
			$about = $this->input->ip_address();
			$counter++;
		}
		if(!strlen($text)){
			$text = "От переполняющих душу чувств восторженно молчит.";
			$counter++;
		}
		if($counter === 3){
			redirect("/page/show/".$location_id);
		}
		$this->db->query("INSERT INTO comments (
			comments.auth_name,
			comments.contact_info,
			comments.text,
			comments.ip,
			comments.date,
			comments.status,
			comments.uid,
			comments.location_id,
			comments.`hash`
		) VALUES ( ?, ?, ?, INET_ATON(?), NOW(), 'N', ?, ?, ?)", array(
			$name,
			$about,
			$text,
			$this->input->ip_address(),
			substr($this->input->post('random'), 0, 32),
			$location_id,
			md5(date("U").rand(0,500))
		));
		redirect("/page/gis/".$location_id);
	}

}
/* End of file docmodel.php */
/* Location: ./system/application/models/docmodel.php */