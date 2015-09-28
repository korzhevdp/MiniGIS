<?php
class Docmodel extends CI_Model{
	function __construct(){
		parent::__construct();
	}

	#######################################################################
	### редактор страниц VERIFIED
	#######################################################################
	public function find_initial_sheet($root = 1) {
		$out = $root;
		$result = $this->db->query("SELECT 
		sheets.id
		FROM
		sheets
		WHERE
		sheets.`root` = ?
		ORDER BY `id`
		LIMIT 1",array($root));
		if($result->num_rows()){
			$row = $result->row();
			$out = $row->id;
		}
		return $out;
	}
	
	private function sheet_tree($root = 0, $sheet_id = 1) {
		$tree = "";
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
				$tree  = str_replace("--".$row->parent."--", '<a href="/admin/sheets/edit/'.$row->id.'"><div class="menu_item" class="'.implode($style, ";").'">'.$row->id.". ".$row->header.' ('.$row->root.')</div></a><div class="menu_item_container">--'.$row->id.'--</div>--'.$row->parent.'--', $tree);
			}
		}
		$tree = preg_replace("/(\-\-)(\d+)(\-\-)/", "", $tree);
		return $tree;
	}

	public function sheet_edit($sheet_id){
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
			'sheet_tree' => ''
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
			$act['sheet_id']   = $sheet_id;
			$act['sheet_tree'] = $this->sheet_tree(0, $sheet_id);
			$act['is_active']  = ($act['active']) ? 'checked="checked"' : "";
		}
		$act['redirect'] = $this->get_redirects();
		return $this->load->view('fragments/sheets_editor', $act, true);
	}
	
	private function get_redirects() {
		$redirect = array();
		$result   = $this->db->query("SELECT
		`map_content`.id,
		`map_content`.name
		FROM
		`map_content`");
		if($result->num_rows()){
			foreach($result->result() as $row){
				$selected = ($row->id == $act['redirect']) ? ' selected="selected"' : "";
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

	public function sheet_save($sheet_id){
		$pageorder = $this->get_pageorder($sheet_id)
		if ($this->input->post('save_new')) {
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
				$this->input->post('sheet_text',     TRUE),
				$this->input->post('sheet_root',     TRUE),
				date("Y-m-d"),
				$this->input->post('sheet_header',   TRUE),
				$pageorder,
				$this->input->post('is_active')) ? 1 : 0,
				$sheet_id,
				$this->input->post('sheet_redirect', TRUE),
				$this->input->post('sheet_comment',  TRUE)
			));
		}else{
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
				$this->input->post('sheet_text',     TRUE),
				$this->input->post('sheet_header',   TRUE),
				$this->input->post('pageorder',      TRUE),
				$this->input->post('is_active')) ? 1 : 0,
				$this->input->post('sheet_parent',   TRUE),
				$this->input->post('sheet_redirect', TRUE),
				$this->input->post('sheet_comment',  TRUE),
				$this->input->post('sheet_root',     TRUE),
				$sheet_id
			));
		}
		$this->load->model('cachemodel');
		$this->cachemodel->menu_build(1, 0, 'file');
	}

}
/* End of file docmodel.php */
/* Location: ./system/application/controllers/docmodel.php */