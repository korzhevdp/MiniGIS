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
		$tree="";
		if(!$root){
			$result=$this->db->query("SELECT 
			`sheets`.`id`,
			`sheets`.`parent`,
			`sheets`.`pageorder`,
			`sheets`.`header`,
			`sheets`.`active`,
			`sheets`.`root`
			FROM
			`sheets`
			ORDER BY `sheets`.`parent`,
			`sheets`.`pageorder`",array($root));
		}else{
			$result=$this->db->query("SELECT 
			`sheets`.`id`,
			`sheets`.`parent`,
			`sheets`.`pageorder`,
			`sheets`.`header`,
			`sheets`.`active`,
			`sheets`.`root`
			FROM
			`sheets`
			WHERE
			`sheets`.`root` = ?
			ORDER BY `sheets`.`parent`,
			`sheets`.`pageorder`",array($root));
		}

		if($result->num_rows() ){
			foreach($result->result() as $row){
				$style=array();
				(!$row->active) ? array_push($style,'muted') : '';
				($row->id == $sheet_id) ? array_push($style,"active") : '';
				switch ($row->root){
					case 2 :
						$type="П";
					break;
					case 1 :
						$type="Ст";
					break;
				}
				$tree.=(!strlen($tree)) ? "\..--".$row->parent."--" : '';

				$tree=str_replace("--".$row->parent."--",'<a href="/admin/sheets/edit/'.$row->id.'"><div class="menu_item" class="'.implode($style,";").'">'.$row->id.". ".$row->header.' ('.$type.')</div></a><div class="menu_item_container">--'.$row->id.'--</div>
				--'.$row->parent.'--',$tree);
			}
		}
		$tree=preg_replace("/(\-\-)(\d+)(\-\-)/","",$tree);
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
			'is_active'  => 1,
			'parent'     => 0,
			'pageorder'  => 0,
			'comment'    => "Умолчательный комментарий",
			'sheet_tree' => ''
		);
		$result=$this->db->query("SELECT 
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
		LIMIT 1",array($sheet_id));
		if($result->num_rows()){
			$act = $result->row_array();
			$act['sheet_id'] = $sheet_id;
			$act['sheet_tree'] = $this->sheet_tree(0,$sheet_id);
			$act['is_active'] = ($act['active']) ? 'checked="checked"' : "";
		}
		$result=$this->db->query("SELECT 
		CONCAT('/map/simple/',`map_content`.id) as id,
		`map_content`.name
		FROM
		`map_content`");
		if($result->num_rows()){
			foreach($result->result() as $row){
				$selected = ($row->id == $act['redirect']) ? 'selected="selected"' : "";
				$string = '<option value="'.$row->id.'" '.$selected.'>'.$row->name.'</option>';
				array_push($redirect, $string);
			}
		}
		$act['redirect'] = implode($redirect, "\n");
		$out = $this->load->view('fragments/sheets_editor',$act,true);
		return $out;
	}

	public function sheet_save($sheet_id){
		//$this->output->enable_profiler(TRUE);
		$is_active = ($this->input->post('is_active')) ? 1 : 0;
		if($this->input->post('save_new')){
			$result = $this->db->query("SELECT 
			MAX(`sheets`.pageorder) + 10 AS pageorder
			FROM
			`sheets`
			WHERE `sheets`.`parent` = ?",array($sheet_id));
			if($result->num_rows()){
				$row = $result->row();
				$pageorder = $row->pageorder;
			}else{
				$pageorder = 10;
			}
			$this->db->query("INSERT INTO `sheets`(
				`sheets`.`text`,
				`sheets`.`root`,
				`sheets`.`date`,
				`sheets`.`header`,
				`sheets`.`pageorder`,
				`sheets`.active,
				`sheets`.parent,
				`sheets`.redirect,
				`sheets`.comment
			) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ? )", array(
				$this->input->post('sheet_text',     TRUE),
				$this->input->post('sheet_root',     TRUE),
				date("Y-m-d"),
				$this->input->post('sheet_header',   TRUE),
				$pageorder,
				$is_active,
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
			WHERE
			`sheets`.`id`        = ?", array(
				$this->input->post('sheet_text',     TRUE),
				$this->input->post('sheet_header',   TRUE),
				$this->input->post('pageorder',      TRUE),
				$is_active,
				$this->input->post('sheet_parent',   TRUE),
				$this->input->post('sheet_redirect', TRUE),
				$this->input->post('sheet_comment',  TRUE),
				$this->input->post('sheet_root',     TRUE),
				$sheet_id
			));
		}
		$this->load->model('cachemodel');
		$this->cachemodel->menu_build(1, 0, 'file');
		//return "sheet_save";
	}

}
/* End of file docmodel.php */
/* Location: ./system/application/controllers/docmodel.php */