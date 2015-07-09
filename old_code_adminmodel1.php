
/*	DEPRECATED
	function _index_common_username($userid){
		$result=$this->db->query("SELECT
		CONCAT_WS(' ',laz_users_admins.name_i, laz_users_admins.name_o) as `io`
		FROM
		laz_users_admins
		WHERE
		laz_users_admins.uid = ?
		LIMIT 1",array($userid));
		if($result->num_rows() ){
			$user=$result->row();
		}
		return $user->io;
	}

	function set_init_location(){
		$redirect="/admin";
		$query=$this->db->query("SELECT 
		CONCAT('/admin/pages/',MIN(`laz_locations`.id)) AS `link`
		FROM 
		`laz_locations`
		WHERE `laz_locations`.`owner` = ? 
		LIMIT 1" , array($this->session->userdata('user_id')));
		if ($query->num_rows() ){
			$row = $query->row();
			$redirect = $row->link;
		}
		redirect($redirect);
	}

	function save_location($location_id){
		$params=$this->_get_properties($location_id);
		$savedata=array(
			htmlspecialchars($this->input->post('frm_name',TRUE)),
			$this->input->post('frm_location_type',TRUE),
			htmlspecialchars($this->input->post('frm_contact_info',TRUE)),
			htmlspecialchars($this->input->post('frm_address',TRUE)),
			$params['coord_y'],
			$this->input->post('frm_style_override',TRUE),
			$location_id
		);
		$savedata2=array(
			$this->input->post('frm_address',TRUE),
			$params['coord_y'],
			$location_id
		);

		if($this->input->post('frm_img_order') && strlen($this->input->post('frm_img_order'))){
			$array=explode(",",$this->input->post('frm_img_order'));
			foreach($array as $key=>$val){
				if(strlen($val)){
					$tval=explode("_",$val);
					$this->db->query("UPDATE `laz_images` SET `laz_images`.`order` = ? WHERE `laz_images`.`id` = ?",array($key,$tval[1]));
				}
			}
		}

		$query = "UPDATE `laz_locations` SET `laz_locations`.`location_name`= ?,
		`laz_locations`.`type` = ?,
		`laz_locations`.`contact_info` = ?,
		`laz_locations`.`address` = ?,
		`laz_locations`.`coord_y` = ?,
		`laz_locations`.`style_override` = ?
		WHERE `laz_locations`.`id` = ?";
		$query2 = "UPDATE `laz_locations` SET `laz_locations`.`address` = ?, `laz_locations`.`coord_y` = ? WHERE `laz_locations`.`parent` = ?";

		$this->db->query($query,$savedata);
		$this->db->query($query2,$savedata2);
		$sp=Array();
		($this->input->post('frm_location_active')) ? array_push($sp,1) : array_push($sp,0);
		($this->input->post('frm_location_comments')) ? array_push($sp,1) : array_push($sp,0);
		array_push($sp,$location_id);
		
		$this->db->query("UPDATE laz_locations SET laz_locations.active = ?, laz_locations.comments = ? WHERE laz_locations.id = ?",$sp);

			$this->db->query("UPDATE 
			laz_properties_assigned 
			SET 
			laz_properties_assigned.property_id = (SELECT laz_locations_types.pl_num FROM laz_locations_types WHERE `laz_locations_types`.`id` = ?)
			WHERE
			laz_properties_assigned.location_id = ? AND
			laz_properties_assigned.property_id IN (SELECT laz_locations_types.pl_num FROM laz_locations_types WHERE laz_locations_types.object_group = (SELECT laz_locations_types.object_group FROM laz_locations_types WHERE laz_locations_types.id = ?))", array($this->input->post('frm_location_type',TRUE),$location_id,$this->input->post('frm_location_type',TRUE)));

/*
		$this->db->query("DELETE 
		FROM laz_properties_assigned 
		WHERE laz_properties_assigned.property_id IN 
		(0,(SELECT GROUP_CONCAT(`laz_locations_types`.pl_num) as `str` FROM `laz_locations_types`)) AND 
		laz_properties_assigned.location_id = ?", array($location_id));

		$this->db->query("INSERT INTO laz_properties_assigned (
		laz_properties_assigned.property_id, 
		laz_properties_assigned.location_id, 
		laz_properties_assigned.value) VALUES (
			(SELECT laz_locations_types.pl_num FROM laz_locations_types WHERE `laz_locations_types`.`id` = ?),
			?,
			'Y'
		)",array($this->input->post('frm_location_type',TRUE), $location_id));
		//exit;
	}


	function new_location($page=0,$obj_group=1,$loc_type=0){
		//$params=$this->show_location_in_text($page);
		$params=$this->_get_properties($page);
		$result=$this->db->query("SELECT 
		laz_locations_types.id
		FROM
		laz_locations_types
		WHERE
		(laz_locations_types.object_group = ?) AND
		`laz_locations_types`.`pl_num` = 0
		LIMIT 1",array($obj_group));
		if($result->num_rows()){
			$row=$result->row();
			$default_type = $row->id;
		}
		if(!$page){
			$redirect = 'admin/index/'.$obj_group.'/'.$loc_type;
			//print "INSERT INTO `laz_locations` (`owner`, `date`,`parent`,`type`) VALUES (".$this->session->userdata('user_id').",".date("Y-m-d").",".$page.",".$default_type.")";
			$this->db->query("INSERT INTO `laz_locations` (
			`owner`,
			`date`,
			`parent`,
			`type`) VALUES (?,?,?,?)",array(
			$this->session->userdata('user_id'),
			date("Y-m-d"),
			$page,
			$default_type));
		}else{
			$redirect='admin/pages/'.$page.'/6';
			$this->db->query("INSERT INTO 
			`laz_locations` (
			`owner`,
			`date`,
			`parent`,
			`type`,
			`address`,
			`contact_info`,
			`coord_y`) VALUES (?,?,?,?,?,?,?)",array(
				$this->session->userdata('user_id'),
				date("Y-m-d"),
				$page,
				$default_type,
				$params['address'],
				$params['contact_info'],
				$params['coord_y']));
		}
		$id=$this->db->insert_id();
		// эта вставка - поисковая заглушка, чтобы система могла адекватно найти локацию по 
		$this->db->query("INSERT INTO
		`laz_properties_assigned` (
		`laz_properties_assigned`.`location_id`,
		`laz_properties_assigned`.`property_id`,
		`laz_properties_assigned`.`value`) VALUES
		(?,?,?)",array($id,0,$id));
		redirect($redirect);
	}

	function save_onmap($location_id){
		$coords = ($this->input->post('save')) ? $this->input->post('yandex_coords',1) : NULL;
		$coords_obj = $this->input->post('baspath',1);
		$this->db->query("UPDATE laz_locations
		SET 
		laz_locations.coord_y = ?,
		laz_locations.coord_obj = ?
		WHERE (laz_locations.id = ?) OR 
		(laz_locations.parent = ?)",array($coords,$coords_obj,$location_id,$location_id));
		$this->db->query("INSERT INTO laz_coord_history (
		laz_coord_history.location_id,
		laz_coord_history.coord
		)
		VALUES (?,?)",array($location_id,$coords));
	}

	function save_onmap_route($location_id){
		$coords_obj = $this->input->post('baspath',1);
		$this->db->query("UPDATE laz_locations
		SET 
		laz_locations.coord_y = ?
		laz_locations.coord_obj = ?
		WHERE laz_locations.id = ?", array($this->input->post("encpath",1),$coords_obj,$location_id));
	}

	function save_params($page,$location_id){
		if(!$location_id){return false;}
		//check_owner!
		$delparams= array($page,$location_id);
		$to_delete=$this->db->query("SELECT laz_properties_list.id FROM laz_properties_list WHERE laz_properties_list.page = ?",array($page));
		if($to_delete->num_rows()){
			$ds=Array();
			foreach($to_delete->result() as $row){
				array_push($ds,$row->id);
			}
			$this->db->query("DELETE FROM
			laz_properties_assigned
			WHERE laz_properties_assigned.property_id IN (".implode($ds,",").") AND
			laz_properties_assigned.location_id = ?", $location_id);
		}
		if(!strlen($this->input->post('param_1'))){unset($_POST['param_1']);unset($_POST['sel_4']);}
		if($this->input->post('sel_2') || $this->input->post('sel_3') || $this->input->post('sel_4')){
			//if($this->input->post('sel_2')){$cmod = $this->input->post('sel_2');}
			//if($this->input->post('sel_3')){$cmod = $this->input->post('sel_3');}
			if($this->input->post('sel_4')){$cmod = $this->input->post('sel_4');}
			if($cmod==2){$_POST['param_1']=$_POST['param_1'] + 10000000;}
			if($cmod==3){$_POST['param_1']=($_POST['param_1']*1000) + (10000000*2);}
			if($cmod==4){$_POST['param_1']=($_POST['param_1']*90) + (10000000*3);}
		}
		$parameters=Array();
		foreach($_POST as $name => $val){
			$valnum=explode("_", $name);
			if($valnum[0]=="param"){
				if (strlen($val)) {
					array_push($parameters,"(".$this->db->escape($valnum[1]).",".$this->db->escape($location_id).",".$this->db->escape($val).")");
				}else{
					unset($_POST[$name]);
				}
			}
			if($valnum[0]=="sel"){
				array_push($parameters,"(".$this->db->escape($val).",".$this->db->escape($location_id).",".$this->db->escape($val).")");
			}
		}
		if(sizeof($parameters)){
			$this->db->query("INSERT INTO `laz_properties_assigned` (`laz_properties_assigned`.`property_id`, `laz_properties_assigned`.`location_id`, `laz_properties_assigned`.`value`) VALUES\n".implode($parameters,",\n"));
		}
	}

	function _params_sync_traversal($location_id){
		$affected_locations = Array();
		$properties_list = Array();
		$properties_numbers = Array();
		$out_result=Array();
		$result=$this->db->query("SELECT 
		`laz_locations`.id
		FROM
		`laz_locations`
		WHERE
		`laz_locations`.`parent` = ?",array($location_id));
		if ($result->num_rows() ){
			foreach ($result->result() as $row){
				array_push($affected_locations,$row->id);
			}
		}else{
			return FALSE;
		}
		if(sizeof($affected_locations)){
			$result=$this->db->query("SELECT 
			`laz_properties_assigned`.property_id,
			`laz_properties_assigned`.value
			FROM
			`laz_properties_assigned`
			WHERE `laz_properties_assigned`.`location_id` = ?",array($location_id));
			if ($result->num_rows() ){
				foreach ($result->result() as $row){
					$properties_list[$row->property_id]=$row->value;
					array_push($properties_numbers,$row->property_id);
				}
			}else{
				return FALSE;
			}
		}
		if(sizeof($properties_list)){
			foreach($affected_locations as $key=>$val){
				foreach($properties_list as $key2=>$val2){
					array_push($out_result,"(".$val.",".$key2.",'".$val2."')");
				}
			}
		}else{
			return FALSE;
		}
		$this->db->query("DELETE FROM
			laz_properties_assigned
			WHERE laz_properties_assigned.property_id IN (".implode($properties_numbers,",").") AND
			laz_properties_assigned.location_id IN (".implode($affected_locations,",").")");
		$this->db->query("INSERT INTO laz_properties_assigned (
			laz_properties_assigned.location_id,
			laz_properties_assigned.property_id,
			laz_properties_assigned.value
			) VALUES 
			".implode($out_result,",\n"));
		return TRUE;
	}

	function _params_sync_all(){
		$source_locations = Array();
		$affected_locations = Array();
		$target_locations = Array();
		$properties_list = Array();
		$properties_numbers = Array();

		$out_result=Array();

		### локации
		$result=$this->db->query("SELECT 
		`laz_locations`.id,
		`laz_locations`.parent
		FROM
		`laz_locations`
		ORDER BY
		`laz_locations`.parent");
		if ($result->num_rows() ){
			foreach ($result->result() as $row){
				if($row->parent){
					if(!isset($affected_locations[$row->parent])){
						$affected_locations[$row->parent]=Array();
						array_push($source_locations,$row->parent);
					}
					array_push($affected_locations[$row->parent],$row->id);
					array_push($target_locations,$row->id);
				}
			}
		}else{
			return FALSE;
		}
		
		
		if(sizeof($affected_locations)){
			$result=$this->db->query("SELECT 
			`laz_properties_assigned`.property_id,
			`laz_properties_assigned`.location_id,
			`laz_properties_assigned`.value
			FROM
			`laz_properties_assigned`
			WHERE `laz_properties_assigned`.`location_id` IN (".implode($source_locations,",").")
			order by `laz_properties_assigned`.location_id");
			if ($result->num_rows() ){
				foreach ($result->result() as $row){
					if(!isset($properties_list[$row->location_id])){
						$properties_list[$row->location_id] = Array();
					}
					$properties_list[$row->location_id][$row->property_id]=$row->value;
					array_push($properties_numbers,$row->property_id);
					//array_push($properties_numbers,$row->property_id);
				}
				$properties_numbers=array_unique($properties_numbers);
			}else{
				return FALSE;
			}
		}else{
			return FALSE;
		}

		if(sizeof($properties_list) ){
			foreach($affected_locations as $key=>$val){
				foreach($val as $key2=>$location){
					//print $key."=>".$location."<BR>";
					foreach($properties_list[$key] as $key3 => $dbvalue){
						array_push($out_result,"(".$location.",".$key3.",'".$dbvalue."')");
					}
				}
			}
			$this->db->query("DELETE FROM
			laz_properties_assigned
			WHERE laz_properties_assigned.property_id IN (".implode($properties_numbers,",").") AND
			laz_properties_assigned.location_id IN (".implode($target_locations,",").")");
			$this->db->query("INSERT INTO laz_properties_assigned (
			laz_properties_assigned.location_id,
			laz_properties_assigned.property_id,
			laz_properties_assigned.value
			) VALUES 
			".implode($out_result,",\n"));
			return TRUE;
			/*$this->db->query("DELETE FROM
			laz_properties_assigned
			WHERE laz_properties_assigned.property_id IN (".implode($properties_numbers,",").") AND
			laz_properties_assigned.location_id IN (".implode($affected_locations,",").")");
		$this->db->query("INSERT INTO laz_properties_assigned (
			laz_properties_assigned.location_id,
			laz_properties_assigned.property_id,
			laz_properties_assigned.value
			) VALUES 
			".implode($out_result,",\n"));
		}else{
			return FALSE;
		}
	}


	function _get_assigned_properties($page,$location_id){
		$assigned=Array();
		if($location_id!=='new'){
			$query=$this->db->query('SELECT
			`laz_properties_assigned`.property_id,
			`laz_properties_assigned`.value
			FROM
			`laz_properties_assigned`
			INNER JOIN laz_properties_list ON (`laz_properties_assigned`.property_id = laz_properties_list.id)
			WHERE laz_properties_list.page = '.$page.' AND
			`laz_properties_assigned`.`location_id` = '.$location_id);
			if ($query->num_rows() ){
				foreach ($query->result_array() as $row){$assigned[$row['property_id']]=$row['value'];}
			}
		}
		return $assigned;
	}

	function show_form_content($page,$location_id,$object_group=1,$columns=2){
		$this->load->helper('array');
		$assigned=Array();
		if($location_id!=='new'){
			$assigned=$this->_get_assigned_properties($page,$location_id);
		}
		$output=Array();
		$query=$this->db->query('SELECT
			laz_properties_list.id,
			laz_properties_list.`row`,
			laz_properties_list.element,
			laz_properties_list.label,
			laz_properties_list.selfname,
			laz_properties_list.fieldtype,
			laz_properties_list.parameters,
			laz_properties_list.style
			FROM
			laz_properties_list
			WHERE
			laz_properties_list.object_group = '.$object_group.' AND
			laz_properties_list.active = 1 AND
			laz_properties_list.page = '.$page.'
			ORDER BY
			laz_properties_list.`row`,
			laz_properties_list.element,
			laz_properties_list.cat,
			laz_properties_list.selfname');
			if ($query->num_rows() ){
				foreach ($query->result() as $row){
					if(!isset($output[$row->row])){$output[$row->row] = Array();}
					if(!isset($output[$row->row][$row->element])){$output[$row->row][$row->element] = Array();}
					if(!isset($output[$row->row][$row->element][$row->id])){$output[$row->row][$row->element][$row->id]=Array();}
					if(!isset($output[$row->row]['label'])){$output[$row->row]['label']=$row->label;}
					$output[$row->row][$row->element][$row->id]['name']=$row->selfname;
					$output[$row->row][$row->element][$row->id]['fieldtype']=$row->fieldtype;
					$output[$row->row][$row->element][$row->id]['style']=$row->style;
					$output[$row->row][$row->element][$row->id]['parameters']=$row->parameters;
				}
			}
			//print_r($assigned);
			$table=array('<table class="elements">');
			foreach($output as $key => $val){
				$element="";
				$elementarray=Array();
				foreach($val as $key2 => $val2){
					if($key2!=="label"){
						$values=array();// исключительно для случая, если элемент типа select
						$backcounter=sizeof($val2);
						foreach($val2 as $obj => $val3){
							//print $obj."<br>";
							$value="";
							$options=Array();
							switch ($val3['fieldtype']){
								// следует ли ещё написать функционал для  radio?!
								case 'text' :
									if(isset($assigned[$obj])){
									$value = $assigned[$obj];
									## странная врезка, но ничего не поделать :( ага, щас!
										if($object_group==1 && $obj == 1){
											$dm=substr($assigned[$obj],0,1);
											if($dm==1){$value = $value - 10000000;}
											if($dm==2){$value = ($value - 20000000)/1000;}
											if($dm==3){$value = ($value - 30000000)/90;}
										}
									#########################################
										$value = 'value="'.$value.'"';
									}
									$element.='<input type="text" name="param_'.$obj.'" id="param_'.$obj.'" '.$value.' '.$val3['parameters'].' class="'.$val3['style'].'">'.$val3['name'];
								break;
								case 'textarea' :
									$value = (isset($assigned[$obj])) ? $assigned[$obj] : '';
									$element.=$val3['name'].'<textarea name="param_'.$obj.'" id="param_'.$obj.'" '.$val3['parameters'].' rows="3" cols="20" class="span12">'.$value.'</textarea>';
								break;
								case 'select' :
									$selected = (isset($assigned[$obj])) ? "SELECTED" : "";
									array_push($values,'<OPTION value="'.$obj.'" '.$selected.'>'.$val3['name'].'</OPTION>');
									--$backcounter;
									if(!$backcounter){
										$element.='<select name="sel_'.$obj.'" id="sel_'.$obj.'" class="'.$val3['style'].'">'.implode($values,"\n").'</select>';
									}
								break;
								case 'checkbox' :
									$value = (isset($assigned[$obj])) ? 'CHECKED' : '';
									array_push($elementarray,'<label class="checkbox inline span5" style="margin-left:0px;cursor:pointer;" title="'.$val['label'].' - '.$val3['name'].'" for="param_'.$obj.'"><input type="checkbox" name="param_'.$obj.'" id="param_'.$obj.'" '.$value.' value="Y" title="щёлкните чтобы пометить" class="'.$val3['style'].'">'.$val3['name'].'</label>');
									--$backcounter;
									if (!$backcounter){
										$element=implode($elementarray,"\n");
									}
								break;
							}
						}
					}
				}
			(!strlen($val['label'])) ? $val['label']="&nbsp;" : "";
			array_push($table,'<tr style="height: 20px;">
			<td class="span3">
				<label for="param">'.$val['label'].'</label>
			</td>
			<td class="span9">'.$element.'</td>
		</tr>');
		}
		array_push($table,"</table>");
		return implode($table,"\n");
	}

	function _show_location_images($location_id,$obj_group,$src="adm"){
		$this->load->helper('html');
		$related_images_act=Array();
		$related_images_inact=Array();
		if($location_id!=="new"){
			$act['related_img']="";
			$this->db->cache_off();
			$result=$this->db->query("SELECT
			`laz_images`.filename,
			`laz_images`.s128,
			`laz_images`.id,
			`laz_images`.active
			FROM
			`laz_images`
			WHERE
				`laz_images`.`location_id` = '".$location_id."'
			ORDER BY `laz_images`.`active` DESC,`laz_images`.`order`");
			if($result->num_rows() ){
				foreach($result->result() as $row){
					$measures=explode(",",$row->s128);
					if($row->active){
						$switcher3='<img src="/images/yes.png" width="16" height="16" border="0" alt="Показывать" title="Показывается. Щёлкните чтобы отключить показ.">';
					}else{
						$switcher3='<img src="/images/no.png" width="16" height="16" border="0" alt="Не показывать" title="Не показывается. Щёлкните чтобы включить показ.">';
					}
					$string='<li style="width:130px;height:150px;text-align: center; float:left;" id="img_'.$row->id.'">
					<a href="/userimages/800/'.$row->filename.'" target="_blank">
						<img src="/userimages/128/'.$row->filename.'" alt="location photo" class="loc_image" width="'.$measures[0].'" height="'.$measures[1].'" title="Щёлкните, чтобы просмотреть большое изображение">
					</a>
					<div class="img_switchers">
					<a href="/dop/dop_image_rotate_right/'.$obj_group.'/'.$row->filename.'">
						<img src="/images/shape_rotate_clockwise.png" width="16" height="16" border="0" alt="поворот вправо" title="Повернуть по часовой стрелке">
					</a>
					<a href="/dop/dop_image_rotate_left/'.$obj_group.'/'.$row->filename.'">
						<img src="/images/shape_rotate_anticlockwise.png" width="16" height="16" border="0" alt="поворот влево" title="Повернуть против часовой стрелки">
					</a>
					<a href="/dop/dop_image_switch_enable/'.$obj_group.'/'.$row->filename.'">
						'.$switcher3.'
					</a>
					</div>
					</li>';
					if($row->active){
						array_push($related_images_act,$string);
					}else{
						array_push($related_images_inact,$string);
					}

				}
			}
			$act['related_act']=implode($related_images_act,"\n");
			$act['related_inact']=implode($related_images_inact,"\n");
			$act['location_id']=$location_id;
			$act['src']=$src;
			$act['user_id']=$this->session->userdata('user_id');
			return $this->load->view('admin/locations_images', $act, true);
		}else{
			return "";
		}
	}

	function show_location($location_id,$obj_group=1){
		$act['locations_contact_info_text']="";
		$act['address_text']="";
		$act['name_text']="";
		$resulted['type']=0;
		$this->db->cache_off();
		$result=$this->db->query("SELECT
		laz_locations.id,
		laz_locations.location_name,
		laz_locations.active,
		laz_locations.parent,
		laz_locations.`type`,
		laz_locations.contact_info,
		laz_locations.address,
		`laz_locations`.comments,
		`laz_locations`.style_override,
		`laz_locations_types`.has_child,
		`laz_locations_types`.pr_type
		FROM
		`laz_locations_types`
		INNER JOIN laz_locations ON (`laz_locations_types`.id = laz_locations.`type`)
		WHERE
		laz_locations.id  = ?", array($location_id));
		if($result->num_rows() ){
			$resulted=$result->row_array();
			$act['locations_contact_info_text']=form_prep($resulted['contact_info']);
			$act['address_text']=form_prep($resulted['address']);
			$act['name_text']=form_prep($resulted['location_name']);
			$act['active']=$resulted['active'];
			$act['comments']=$resulted['comments'];
			$act['pr_type']=$resulted['pr_type'];
			$act['style_override']=$resulted['style_override'];
			$parent = $resulted['parent'];

		}

		$where = ($parent) ? "AND `laz_locations_types`.`has_child` = 0" : "";

		$result=$this->db->query("SELECT
		laz_locations_types.id,
		laz_locations_types.name
		FROM
		laz_locations_types
		WHERE `laz_locations_types`.`object_group` = ?
		".$where."
		ORDER BY
		`laz_locations_types`.`has_child` DESC,
		laz_locations_types.name",array($obj_group));
		$options=Array();
		if($result->num_rows()){
			foreach ($result->result() as $row){
				$options[$row->id]=$row->name;
			}
		}
		$act['related_img']=$this->_show_location_images($location_id,$obj_group);
		$act['location_type']=form_dropdown('frm_location_type', $options, $resulted['type'],'id="frm_loc_type" class="span9"');
		return $this->load->view('admin/locations_contactinfo', $act, true);//.$this->load->view('fragments/edit_location_state', $act, true);
	}

	function show_location_in_text($location_id=0,$obj_group=1){
		$result=$this->db->query("SELECT
		laz_locations.id,
		laz_locations.location_name,
		laz_locations.parent,
		laz_locations.contact_info,
		laz_locations.active,
		laz_locations.address,
		laz_locations.coord_y,
		laz_locations_types.name
		FROM
		laz_locations
		INNER JOIN laz_locations_types ON (laz_locations.`type` = laz_locations_types.id)
		WHERE
		laz_locations.id = '".$location_id."'");
		$row=Array();
		if($result->num_rows() ){
			$row=$result->row_array();
			$warnings=array();
			if(!strlen($row['coord_y'])){
				$string='<div class="alert alert-error" title="Щёлкните, чтобы перейти к странице размещения на карте">Предложение не может быть показано, потому что оно не размещено на карте. &nbsp;&nbsp;&nbsp;&nbsp;<a href="/admin/pages/'.$row['id'].'/5/'.$obj_group.'">Исправить</a></div>';
				array_push($warnings,$string);
			}
			if(!$row['active']){
				$string='
				<div class="alert alert-error" title="Щёлкните, чтобы перейти к странице, откуда предложение можно опубликовать">Предложение не может быть показано, потому что оно не опубликовано&nbsp;&nbsp;&nbsp;&nbsp;<a href="/admin/pages/'.$row['id'].'/1/'.$obj_group.'">Исправить</a></div>';
				array_push($warnings,$string);
			}
			$row['warnings'] = (sizeof($warnings)) ? implode($warnings,"\n") : '';
		}else{
			return "Нет данных";
		}
		return $this->load->view('admin/locations_contactinfo_in_text', $row, true);
	}

	function show_modes($location_id,$page,$obj_group=1){
		$act=$this->_get_properties($location_id);
		$act['current_page']=$page;
		$act['current_location']=$location_id;
		$act['location_summary']=$this->show_location_in_text(($act['parent']) ? $act['parent'] : $location_id,$obj_group);
		$act['form_header']='<FORM METHOD=POST ACTION="/admin/datatrap/'.$page.'/'.$location_id.'/'.$obj_group.'" id="frm_main_form" class="form-inline">';
		$act['savetab']= '<button type="submit" title="Сохранить описание" class="btn btn-primary btn-block" name="submit">Сохранить описание</button>';
		$act['formcontent']="";
		$act['obj_group']=$obj_group;
		$act['locations_images']="";
		$act['content']="";
		//$act['yandex_key']=$this->config->item('yandex_key');
		$act['maps_center']=$this->config->item('maps_center');
		$act['form_close']="</FORM>";
		$nchunk = ($obj_group==$this->config->item('mod_housing')) ? 1 : 2;
		$act['navigation']=$this->load->view('fragments/edit_location_nav_chunk_'.$nchunk, $act, true);
		$fullcontent=Array();
		$act['summary_table'] = "";//($act['parent']) ? $this->_places_info($act['parent'],$location_id) : implode($this->_index_info_table(0,$location_id),"");

		if ($page==1){
			if($obj_group==1){
				array_push($fullcontent,'<h3>Фотографии</h3>'."\n");
				array_push($fullcontent,$this->_show_location_images($location_id,$obj_group));
				array_push($fullcontent,$act['form_header']);
				array_push($fullcontent,'<INPUT TYPE="hidden" NAME="frm_img_order" id="frm_img_order">');
				array_push($fullcontent,'<h3>Название и контактная информация</h3>'."\n");
				array_push($fullcontent,$this->show_location($location_id,$obj_group));
				array_push($fullcontent,$act['savetab']);
				array_push($fullcontent,$act['form_close']);
			}else{
				array_push($fullcontent,'<h3>Фотографии</h3>'."\n");
				array_push($fullcontent,$this->_show_location_images($location_id,$obj_group));
				array_push($fullcontent,$act['form_header']);
				array_push($fullcontent,'<INPUT TYPE="hidden" NAME="frm_img_order" id="frm_img_order">');
				array_push($fullcontent,$this->show_location($location_id,$obj_group));
				array_push($fullcontent,$act['savetab']);
				array_push($fullcontent,$act['form_close']);
			}
		}
		if ($page==2){
			array_push($fullcontent,'<h3>Расположение</h3>'."\n");
			array_push($fullcontent,$act['form_header']);
			array_push($fullcontent,$this->show_form_content($page,$location_id,$obj_group));
			array_push($fullcontent,$act['savetab']);
			array_push($fullcontent,$act['form_close']);
			array_push($fullcontent,'<button class="btn btn-info" id="map_calc">Рассчитать зависимости</button>');
		}
		if ($page==3){
			array_push($fullcontent,'<h3>Услуги и сервисы</h3>'."\n");
			array_push($fullcontent,$act['form_header']);
			array_push($fullcontent,$this->show_form_content($page,$location_id,$obj_group));
			array_push($fullcontent,$act['savetab']);
			array_push($fullcontent,$act['form_close']);
		}
		if ($page==4){
			array_push($fullcontent,'<h3>Оформление</h3>'."\n");
			array_push($fullcontent,$act['form_header']);
			array_push($fullcontent,$this->show_form_content($page,$location_id,$obj_group));
			array_push($fullcontent,$act['savetab']);
			array_push($fullcontent,$act['form_close']);
		}
		if ($page==5){
			array_push($fullcontent,$act['form_header']);
			array_push($fullcontent,$this->show_map($location_id));
			array_push($fullcontent,$act['form_close']);
		}
		if ($page==6){
			$act['savetab']='<input type="submit" class="btn btn-primary span12" title="Создание новой записи" value="Создать описание новой комнаты/номера">';
			array_push($fullcontent,'<h3>Список помещений, зарегистрированных для этого размещения.</h3>'."\n");
			array_push($fullcontent,'<FORM METHOD=POST ACTION="/admin/pages/new/'.$location_id.'" id="frm_main_form">');
			array_push($fullcontent,$this->places_index($location_id));
			array_push($fullcontent,$act['savetab']);
			array_push($fullcontent,$act['form_close']);
			array_push($fullcontent,'<h3>Установить ценовые периоды</h3>'."\n");
			array_push($fullcontent,'<FORM METHOD=POST ACTION="/admin/datatrap/pp/'.$location_id.'" id="frm_main_form1">');
			array_push($fullcontent,$this->load->view('fragments/payment_plan_switcher',$act,true));
			array_push($fullcontent,$this->pp_display($location_id));
			array_push($fullcontent,form_submit(array('title' => 'Установить цены на указанные периоды','class' => 'btn btn-primary span12','name' => 'submit'), 'Установить цены на указанные периоды'));
			array_push($fullcontent,$act['form_close']);
			array_push($fullcontent,$this->load->view('admin/payment_plan',Array(),true));
		}
		if ($page==7){
			$result=$this->db->query("SELECT laz_locations.parent, laz_locations.location_name FROM laz_locations WHERE laz_locations.id = ?",array($location_id));
			if($result->num_rows()){
				$resulted=$result->row(0);
			}
			$act['subloc_selfname']=$resulted->location_name;
			$act['locations_images']=$this->_show_location_images($location_id,$obj_group);
			array_push($fullcontent,'<FORM METHOD=POST ACTION="/admin/datatrap/'.$page.'/'.$location_id.'" id="frm_main_form">');
			array_push($fullcontent,$this->load->view('admin/places',$act,true));
			array_push($fullcontent,$this->show_form_content($page,$location_id));
			array_push($fullcontent,form_submit(array('title' => 'Сохранить описание комнаты / номера','class' => 'btn btn-primary span12','name' => 'submit'), 'Сохранить описание'));
			array_push($fullcontent,$act['form_close']);
		}
		if ($page==8){
			array_push($fullcontent,'<h3>Изменить план цен</h3>'."\n");
			array_push($fullcontent,'<FORM METHOD=POST ACTION="/admin/datatrap/pp/'.$location_id.'" id="frm_main_form">');
			array_push($fullcontent,$this->load->view('fragments/payment_plan_switcher',$act,true));
			array_push($fullcontent,'<h3>Действующий план цен</h3>'."\n");
			array_push($fullcontent,$this->pp_display($location_id));
			array_push($fullcontent,form_submit(array('title' => 'Установить цены на указанные периоды','class' => 'btn btn-primary span12','name' => 'submit'), 'Установить цены на указанные периоды'));
			array_push($fullcontent,$act['form_close']);
			array_push($fullcontent,$this->load->view('admin/payment_plan',Array(),true));
		}

		$act['fullcontent']=implode($fullcontent,"\n");
		$act['lid'] = $location_id;
		$all=$this->load->view('admin/locations_container',$act,true);
		return $all;
	}
/*
	function new_place($parent){
		$result=$this->db->query("SELECT
		laz_locations.coord_y,
		laz_locations.address,
		laz_locations.contact_info,
		laz_locations.object_group,
		laz_locations.owner,
		laz_locations.location_name
		FROM
		laz_locations
		WHERE
		laz_locations.id = ?",array($parent));
		if($result->num_rows() ){
			$resulted=$result->row(0);
		}
		$query = $this->db->query("INSERT INTO laz_locations (
		laz_locations.coord_y,
		laz_locations.address,
		laz_locations.contact_info,
		laz_locations.object_group,
		laz_locations.`type`,
		laz_locations.owner,
		laz_locations.location_name,
		laz_locations.`date`,
		laz_locations.parent)
		VALUES (?,?,?,?,?,?,?,?,?)\n",
			array($resulted->coord_y,
			$resulted->address,
			$resulted->contact_info,
			$resulted->object_group,
			9,
			$resulted->owner,
			$resulted->location_name,
			date("Y-m-d"),
			$parent));
		if($query){
			redirect('admin/places/'.$parent);
		}
	}

	function save_place($place){
		$this->save_params();
		$query = "UPDATE `laz_locations` SET `laz_locations`.`location_name`= '".$this->input->post('subloc_selfname')."' WHERE `laz_locations`.`id` = ?";
		$this->db->query($query, array($place));
	}

	function pp_display($location_id){
		$payment_plan = Array();
		$result=$this->db->query("SELECT 
		DATE_FORMAT(`laz_timers`.start_point,'%d.%m.%Y') as start_point,
		DATE_FORMAT(`laz_timers`.end_point,'%d.%m.%Y') as end_point,
		`laz_timers`.price
		FROM
		`laz_timers`
		WHERE `laz_timers`.`type` = 'price' AND
		`laz_timers`.`location_id` = ?
		ORDER BY `laz_timers`.`order`",Array($location_id));
		if($result->num_rows() ){
			$i=0;
			foreach ($result->result() as $row){
				$string='<DIV>
				с: <input type="text" class="halfwidth" name="d_start_'.$i.'" value="'.$row->start_point.'" title="Дата начала периода" READONLY tabindex=0>
				по: <input type="text" class="halfwidth" name="d_end_'.$i.'" value="'.$row->end_point.'" title="Дата окончания периода" READONLY tabindex=0>
				цена: <input type="text" class="quarterwidth" name="price_'.$i.'" maxlength="5" tabindex='.($i+1).' value="'.$row->price.'"> руб./сутки;
				</DIV>';
				array_push($payment_plan,$string);
				$i++;
			}
		}
		else{
			array_push($payment_plan,"Ценовые периоды не заданы");
		}
		return '<table id="section6_4" style="margin-top:20px;">
	<tr style="height: 20px;">
		<td class="adm_cell_1st"><label for="payment_plan">&nbsp;</label></td>
		<td class="adm_cell_2nd" id="frm_pp_container">
		'.implode($payment_plan,"").'
		</td>
	</tr>
	</table>
	<input type="hidden" id="period_count" name="period_count" value="'.sizeof($payment_plan).'">';
	}

	function pp_save($location_id,$traversing){
		$rows=Array();
		$result=$this->db->query("DELETE FROM `laz_timers` WHERE `laz_timers`.`type` = 'price' AND `laz_timers`.`location_id` = ?", array($location_id));
		for($i=0; $i<$this->input->post('period_count');$i++){
			$sd = preg_replace("/[^0-9\.]/","",$this->input->post('d_start_'.$i));
			$ed = preg_replace("/[^0-9\.]/","",$this->input->post('d_end_'.$i));
			$sd = implode(array_reverse(explode(".",$sd)),"-")." 00:00:00";
			$ed = implode(array_reverse(explode(".",$ed)),"-")." 00:00:00";
			$price = $this->input->post('price_'.$i);
			$price = preg_replace("/[^0-9]/","",$price);
			$price = (!strlen($price)) ? 0 : $price;
			array_push($rows,"('".$sd."','".$ed."','".$price."','price','".$location_id."','".$i."')");
		}
		if($traversing){
			$result = $this->db->query("SELECT `laz_locations`.id FROM `laz_locations` WHERE `laz_locations`.`parent` = ?", array($location_id));
			if($result->num_rows() ){
				foreach ($result->result() as $row){
					$result = $this->db->query("DELETE FROM `laz_timers` WHERE `laz_timers`.`type` = 'price' AND `laz_timers`.`location_id` = ?",Array($row->id));
					for($i=0; $i<$this->input->post('period_count');$i++){
						$sd = preg_replace("/[^0-9\.]/","",$this->input->post('d_start_'.$i));
						$ed = preg_replace("/[^0-9\.]/","",$this->input->post('d_end_'.$i));
						$sd = implode(array_reverse(explode(".",$sd)),"-")." 00:00:00";
						$ed = implode(array_reverse(explode(".",$ed)),"-")." 00:00:00";
						$price = $this->input->post('price_'.$i);
						$price = preg_replace("/[^0-9]/","",$price);
						$price = (!strlen($price)) ? 0 : $price;
						array_push($rows,"('".$sd."','".$ed."','".$price."','price','".$row->id."','".$i."')");
					}
				}
			}
		}
		if(sizeof($rows)){
			$this->db->query("INSERT INTO laz_timers (`start_point`,`end_point`,`price`,`type`,`location_id`,`order`) VALUES ".implode($rows,",\n"));
		}
		
	}

	function show_places($location_id,$page){ // MARK FOR DEPRECATION?
		//$all=$this->load->view('admin/places_container',$act,true);
		//return $act;
	}

	function show_map($location_id){
		$act['yandex_key']=$this->config->item('yandex_key');
		$act['maps_center']=$this->config->item('maps_center');
		$act['encpath']="";
		$act['hasdynamic']="";
		$act['pr']="";

		$result = $this->db->query("SELECT 
		laz_locations.coord_y AS `coords` ,
		laz_locations.coord_obj,
		laz_locations.id AS `id`,
		CONCAT_WS(' ', laz_locations_types.name, laz_locations.location_name) AS location_name,
		IF(LENGTH(`laz_locations`.`style_override`) > 1, `laz_locations`.`style_override`, IF(LENGTH(laz_locations_types.attributes) > 0, laz_locations_types.attributes, '')) AS attr,
		laz_locations_types.pr_type AS `pr`
		FROM
		laz_locations_types
		INNER JOIN laz_objects_groups ON (laz_locations_types.object_group = laz_objects_groups.id)
		INNER JOIN laz_locations ON (laz_locations_types.id = laz_locations.`type`)
		WHERE
		(laz_locations.`type` = (SELECT laz_locations.`type` FROM laz_locations WHERE (laz_locations.id = ?))) AND 
		(laz_locations.owner = ?)", array($location_id,$this->session->userdata('user_id')));
		$jsarray=array("var objects=[];");
		if($result->num_rows()){
			foreach($result->result() as $row){
				$string = "objects[".$row->id."] = {description : '".$row->location_name."', coord : '".$row->coords."', pr : ".$row->pr.", attr : '".$row->attr."'}";
				array_push($jsarray,$string);
				if($row->id == $location_id){
					$act['encpath'] = $row->coords;
					$act['hasdynamic'] = (strlen($row->coord_obj)) ? '<span class="badge badge-info">dd</span>' : '' ;
					$act['pr'] = $row->pr;
					$act['baspath'] = $row->coord_obj;
				}
			}
		}
		$act['objects']=implode($jsarray,"\n");

		$options = Array('<option value="0" selected>Выберите тип опорных точек</option>');
		$result = $this->db->query("SELECT
		laz_locations_types.id,
		laz_locations_types.name
		FROM
		laz_locations_types
		WHERE
		`laz_locations_types`.`pr_type` = 1 AND
		`laz_locations_types`.`pl_num` > 0");
		if($result->num_rows()){
			foreach($result->result() as $row){
				array_push($options,'<option value='.$row->id.'>'.$row->name.'</option>');
			}
		}
		$act['bas_points']='<select id="bas_points">'.implode($options,"\n").'</select>';
		return $this->load->view('admin/locations_map',$act,true);
	}
*/
/*
	function _places_info($parent, $location_id='none'){
		$div_class = ($location_id=='none') ? "info_table_innerdiv" : $div_class="info_table_innerdiv2";
			//выбираем назначенные параметры объекта
		$all=Array();
		$result2=$this->db->query("SELECT
			laz_properties_assigned.location_id,
			laz_properties_assigned.value,
			laz_properties_list.id,
			laz_properties_list.selfname,
			laz_properties_list.fieldtype,
			laz_properties_list.property_group
			FROM
			laz_properties_assigned
			INNER JOIN laz_properties_list ON (laz_properties_assigned.property_id = laz_properties_list.id)
			INNER JOIN `laz_locations` ON (laz_properties_assigned.location_id = `laz_locations`.id)
			WHERE laz_locations.parent = ?", Array($parent));
		foreach($result2->result() as $row2){
			if(!isset($all[$row2->property_group])){$all[$row2->property_group]=Array();}
			if(!isset($all[$row2->property_group][$row2->location_id])){$all[$row2->property_group][$row2->location_id]=Array();}
			$all[$row2->property_group][$row2->location_id][$row2->id] = ($row2->fieldtype=="checkbox" || $row2->fieldtype=="select") ? $row2->selfname : $row2->value;
		}

		$result=$this->db->query("SELECT
			`laz_locations`.`id`,
			LCASE(DATE_FORMAT(`laz_locations`.`date`, '%d %M %Y')) as `date`,
			`laz_locations`.`address`,
			`laz_locations`.`contact_info`,
			`laz_locations_types`.`name`,
			`laz_locations`.`location_name`
			FROM
			`laz_locations`
			INNER JOIN `laz_locations_types` ON (`laz_locations`.`type` = `laz_locations_types`.`id`)
			WHERE laz_locations.parent = ?", Array($parent));
		$table=Array();
		if($result->num_rows() ){
			foreach ($result->result() as $row){
				if(!isset($table[$row->id])){$table[$row->id] = Array();}
				if(!isset($all['equipment'][$row->id])){$all['equipment'][$row->id]=Array();}
				if(!isset($all['type'][$row->id])){$all['type'][$row->id]=Array();}
				if(!isset($all['view'][$row->id])){$all['view'][$row->id]=Array();}
				if(!isset($all['architect'][$row->id])){$all['architect'][$row->id]=Array();}
				if(!isset($all['architect'][$row->id][40])){$all['architect'][$row->id][40]="";}
				if(!isset($all['architect'][$row->id][41])){$all['architect'][$row->id][41]=0;}
				if(!isset($all['architect'][$row->id][42])){$all['architect'][$row->id][42]=0;}

				$table[$row->id]='<DIV class="'.$div_class.'" id="info_table_'.$row->id.'">
				<TABLE style="border: 0px;border-collapse: collapse;width:710px;">
				<TR>
					<TD class="stc" colspan=2><B>Контактные лица:</B>&nbsp;&nbsp;&nbsp;'.$row->contact_info.'</TD>
				</TR>
				<TR>
					<TD class="stc" width=150><B>Тип номера / комнаты:</B></TD>
					<TD class="stc">'.implode($all['type'][$row->id],"&nbsp;").'</TD>
				</TR>
				<TR>
					<TD class="stc" colspan=2><B>Описание:</B></TD>
				</TR>
				<TR>
					<TD class="stc"><B>Площадь:</B></TD>
					<TD class="stc">'.$all['architect'][$row->id][40].'&nbsp;м.<SUP>2</SUP></TD>
				</TR>
				<TR>
					<TD class="stc"><B>Места:</B></TD>
					<TD class="stc">Основные: <B>'.$all['architect'][$row->id][41].'</B>, дополнительные: <B>'.$all['architect'][$row->id][42].'</B></TD>
				</TR>
				<TR>
					<TD class="stc"><B>Вид из окна:</B></TD>
					<TD class="stc">'.implode($all['view'][$row->id],",&nbsp;").'</TD>
				</TR>
				<TR>
					<TD class="stc"><B>Оснащение номера:</B></TD>
					<TD class="stc">'.implode($all['equipment'][$row->id],"<br />").'</TD>
				</TR>
				</TABLE>
				</DIV>';
			}
		}
		if($location_id=='none'){
			return $table;
		}else{
			return $table[$location_id];
		}
	}

	function places_index($location_id){
		$info=$this->_places_info($location_id);
		//$userid = $this->session->userdata('user_id');
		$act['current_location']=$location_id;
		$act['locations_summary']=$this->show_location_in_text($location_id);

		$list=Array();
		$result=$this->db->query("SELECT
		`laz_locations`.address,
		`laz_locations`.location_name,
		`laz_locations`.id
		FROM
		`laz_locations`
		WHERE `laz_locations`.`parent` = ?", array($location_id));
		$places_list=Array();
		if($result->num_rows() ){
			$i="0";
			foreach ($result->result() as $row){
				$cellstyle=' onmouseover="this.className=\'locations_table_active\'" onmouseout="this.className=\'a_lti a_col\'" ';
				array_push($places_list,'<tr style="font-size:8pt;font-family: Tahoma;vertical-align: middle;height:32px;">
					<td class="a_lti a_col" style="width:20px;">'.++$i.'</td>
					<td class="a_lti a_col" '.$cellstyle.' title="Щёлкните для просмотра описания номеров" onclick="show_info_table('.$row->id.');" style="width:253px;">'.$row->location_name.'&nbsp;<br>по адресу:&nbsp;'.$row->address.'</td>
					<td class="a_lti a_col" '.$cellstyle.' onclick="window.location =\'/admin/pages/'.$row->id.'/1\'" title="Править описание, оснащение, и характеристики комнат или номеров">редактировать</td>
				</tr>
				<tr>
					<td colspan=3>'.$info[$row->id].'</td>
				</tr>');
				$list[sizeof($list)]=$row->id;
			}
		}
		$act['places_list']='<table style="width:710px;border: 1px solid #CACACA; border-spacing:0px;border-collapse:collapse;margin-top:20px;margin-bottom:20px;">
		<tr style="font-weight:bold;font-size:8pt;font-family: Tahoma;text-align: center;vertical-align: middle;background-color:#EAEAEA;height:20px;">
			<TD style="width:20px;">#</TD>
			<TD style="width:350px;">Название</TD>
			<TD>Действия</TD>
		</tr>'.implode($places_list,"\n").'</table>
		<INPUT TYPE="hidden" id="locations_ids" value="'.implode($list,",").'">';
		//$act['places_list'].=$this->show_modes($location_id,6,1);
		//$act['places_list'].=$this->load->view('admin/payment_plan',Array(),true);
		//return $this->load->view('admin/places_index',$act,true);
		return $act['places_list'];
	}

	function check_owner($location_id){
		//на будущее: создание хэша// UPDATE `laz_users_admins` SET `laz_users_admins`.`uid` = sha1(concat(sha1('uid'),`laz_users_admins`.`id`))
		// or GOLDEN hash :)
		if($this->session->userdata('user_id') == '6f4a7ee73542a7f4a19797d7e80d3bb7adefde04'){
			return TRUE;
		}
		$result=$this->db->query("SELECT
		`laz_locations`.`owner`
		FROM
		`laz_locations`
		WHERE
		laz_locations.id = ? AND laz_locations.owner = ?", array($location_id,$this->session->userdata('user_id')));
		if($result->num_rows()){
			return TRUE;
		}else{
			return FALSE;
		}
	}

	function _get_properties($location_id){
		$out = array(
			'has_child' => 0,
			'type' => 10,
			'parent' => 0,
			'coord_y' => "",
			'address' => "",
			'contact_info' => "");
		$result=$this->db->query("SELECT
		`laz_locations`.parent,
		`laz_locations`.address,
		`laz_locations`.type,
		`laz_locations`.coord_y,
		`laz_locations`.contact_info,
		`laz_locations_types`.has_child
		FROM
		`laz_locations_types`
		INNER JOIN laz_locations ON (`laz_locations_types`.id = laz_locations.`type`)
		WHERE
		laz_locations.id = ?", array($location_id));
		if($result->num_rows()){
			$out = $result->row_array();
		}
		return $out;
	}
#######################################################################
### управление списками параметров
#######################################################################
	function show_ogp($object_group){
		$table=Array('<table class="table table-bordered table-condensed">');
		array_push($table,'<tr>
		<th>Метка</th>
		<th>Название</th>
		<th>Группа свойств</th>
		<th>Категория</th>
		<th>Статус</th>
		<th>Действие</th>
		</tr>');
		$result=$this->db->query("SELECT 
		laz_properties_list.id,
		laz_properties_list.`label`,
		laz_properties_list.selfname,
		laz_properties_list.property_group,
		laz_properties_list.cat,
		laz_properties_list.linked,
		laz_properties_list.searchable,
		laz_properties_list.active
		FROM
		laz_properties_list
		WHERE
		(laz_properties_list.object_group = ?)
		ORDER BY
		laz_properties_list.page,
		laz_properties_list.`row`,
		laz_properties_list.element", array($object_group));
		if($result->num_rows()){
			//array_push($table,'<ul class="span12 row-fluid" style="list-style-type: none; margin-left:0px;">');
			foreach ($result->result() as $row){
				array_push($table,'<tr>
				<td>'.$row->label.'</td>
				<td>'.$row->selfname.'</td>
				<td>'.$row->property_group.'</td>
				<td>'.$row->cat.'</td>
				<td>
					<img src="/images/'.(($row->searchable) ? 'find.png' : 'lightbulb_off.png').'" width="16" height="16" border="0" alt="">
					<img src="/images/'.(($row->active) ? 'lightbulb.png' : 'lightbulb_off.png').'" width="16" height="16" border="0" alt="">
				</td>
				<td>
					<button type="button" class="btn btn-primary btn-mini" onclick="window.location=\'/admin/modify_ogp/'.$object_group.'/'.$row->id.'\'">Редактировать</button>
				</td>
				</tr>');
			}
			array_push($table,'</table>');
		}
		$out = (sizeof($table)) ? implode($table,"\n") : "Nothing Found!";
		return $out;
	}

	function show_ogp_values($object_group = 1,$obj=0){
		$output= array(
			'row' => '',
			'element' => '',
			'label' => '',
			'selfname' => '',
			'algoritm' => '',
			'page' => '',
			'property_group' => '',
			'fieldtype' => '',
			'row' => '',
			'cat' => '',
			'style' => '',
			'parameters' => '',
			'searchable' => '',
			'active' => '',
			'og_name' => '',
			'linked' => ''
		);

		$result=$this->db->query("SELECT 
		laz_properties_list.`row`,
		laz_properties_list.element,
		laz_properties_list.label,
		laz_properties_list.selfname,
		laz_properties_list.page,
		laz_properties_list.parameters,
		laz_properties_list.algoritm,
		if(length(laz_properties_list.linked) = 0, 0, laz_properties_list.linked) as linked,
		laz_properties_list.searchable,
		laz_properties_list.property_group,
		laz_properties_list.fieldtype,
		laz_properties_list.cat,
		laz_properties_list.style,
		laz_properties_list.active,
		`laz_objects_groups`.name as `og_name`
		FROM
		`laz_objects_groups`
		INNER JOIN laz_properties_list ON (`laz_objects_groups`.id = laz_properties_list.object_group)
		WHERE
		(laz_properties_list.id = ?)", array($obj));
		if($result->num_rows() ){
			$output=$result->row_array();
		}
		$output['searchable'] = form_checkbox('searchable', $obj, $output['searchable'],'id = "ogp12"');
		$output['active'] = form_checkbox('active', $obj, $output['active'],'id = "ogp13"');
		$output['og_name'] = $output['og_name'];

		$result=$this->db->query("SELECT DISTINCT laz_properties_list.fieldtype FROM laz_properties_list");
		$array =array();
		if($result->num_rows()){
			foreach($result->result() as $row){
				$array[$row->fieldtype] = $row->fieldtype;
			}
		}
		$output['fieldtype']=form_dropdown('fieldtype', $array, $output['fieldtype'],'id = "ogp8" class="span12"');

		$result=$this->db->query("SELECT DISTINCT laz_properties_list.property_group FROM laz_properties_list");
		$array = array();
		if($result->num_rows() ){
			foreach($result->result() as $row){
				$array[$row->property_group] = $row->property_group;
			}
		}
		$output['property_group'] = form_dropdown('property_group', $array, $output['property_group'],'id = "ogp7" class="span12"');


		$result=$this->db->query("SELECT DISTINCT laz_properties_list.cat FROM laz_properties_list");
		$array = array();
		if($result->num_rows()){
			foreach($result->result() as $row){
				$array[$row->cat] = $row->cat;
			}
		}
		$output['cat']=form_dropdown('cat', $array, $output['cat'],'id = "ogp9" class="span12"');

		$result=$this->db->query("SELECT DISTINCT laz_properties_list.style FROM laz_properties_list");
		$array = array();
		if($result->num_rows()){
			foreach($result->result() as $row){
				$array[$row->style] = $row->style;
			}
		}
		$output['style']=form_dropdown('style', $array, $output['style'],'id = "ogp10" class="span12"');

		$result=$this->db->query("SELECT DISTINCT laz_properties_list.algoritm FROM laz_properties_list");
		$array = array();
		if($result->num_rows()){
			foreach($result->result() as $row){
				$array[$row->algoritm] = $row->algoritm;
			}
		}
		$output['algoritm']= form_dropdown('algoritm', $array, $output['algoritm'],'id = "ogp6" class="span12"');
		
		$result=$this->db->query("SELECT 
		laz_locations.id,
		CONCAT_WS(' ', laz_locations_types.name, laz_locations.location_name) AS name
		FROM
		laz_locations
		INNER JOIN laz_locations_types ON (laz_locations.`type` = laz_locations_types.id)
		WHERE
		(laz_locations_types.pr_type = 3)
		ORDER BY name");
		$array = array("0" => "Не установлена");
		if($result->num_rows()){
			foreach($result->result() as $row){
				$array[$row->id] = $row->name;
			}
		}
		$output['linked']= form_dropdown('linked', $array, $output['linked'],'id = "ogp14" class="span12"');

		$output['object_group']=$object_group;
		$output['obj']=$obj;
		return $output;
	}


#######################################################################
### объекты ГИС
#######################################################################
	function gis_objects_show($obj=0){
		//Выбираем объект
		$object = array(
			'id' => 0,
			'has_child' => 0,
			'name' => '',
			'attributes' => '',
			'object_group' => 0,
			'obj' => 0,
			'pr_type' => 0,
			'pl_num' => 0
		);
		if($obj){
			$result=$this->db->query("SELECT 
			laz_locations_types.id,
			laz_locations_types.has_child,
			laz_locations_types.name,
			laz_locations_types.attributes,
			laz_locations_types.object_group,
			laz_locations_types.object_group AS `obj`,
			laz_locations_types.pl_num,
			laz_locations_types.pr_type
			FROM
			laz_locations_types
			WHERE 
			laz_locations_types.id = ?", array($obj));
			if($result->num_rows()){
				$object = $result->row_array();
			}
		}
		// группы объектов для формы редактирования
		$object['obj_group'] = array('<option value="0">Выберите группу объектов</option>');
		$result=$this->db->query("SELECT `laz_objects_groups`.id,`laz_objects_groups`.name FROM `laz_objects_groups` WHERE `laz_objects_groups`.`active`");
		if($result->num_rows()){
			foreach($result->result() as $row){
				array_push($object['obj_group'],'<option value="'.$row->id.'" '.(($row->id == $object['object_group']) ? 'selected="selected"' : '').'>'.$row->name.'</option>');
			}
		}
		$object['obj_group'] = implode($object['obj_group'],"\n");
		//для таблицы свойств

		$result=$this->db->query("SELECT 
		laz_locations_types.id,
		laz_locations_types.has_child,
		laz_locations_types.name,
		laz_locations_types.attributes,
		laz_locations_types.object_group,
		laz_locations_types.pl_num,
		laz_locations_types.pr_type,
		laz_objects_groups.name AS object_group_name
		FROM
		laz_objects_groups
		RIGHT OUTER JOIN laz_locations_types ON (laz_objects_groups.id = laz_locations_types.object_group)
		ORDER BY
		laz_locations_types.object_group,
		laz_locations_types.name");
		$table2 = array('<table class="table table-bordered table-condensed table-hover">
		<tr>
			<th>Название</th>
			<th>Стиль</th>
			<th>Группа объектов</th>
			<th>Действие</th>
		</tr>');
		if($result->num_rows()){
			foreach ($result->result() as $row){
				$pic = "marker.png";
				$pictitle = "точечная метка";
				switch($row->pr_type){
					case 2:
						$pic = 'layer-shape-polyline.png';
						$pictitle = "Ломаная";
					break;
					case 3:
						$pic = 'layer-shape-polygon.png';
						$pictitle = "Полигон";
					break;
					case 4:
						$pic = 'layer-shape-ellipse.png';
						$pictitle = "Круг";
					break;
					case 5:
						$pic = 'layer-select.png';
						$pictitle = "Прямоугольник";
					break;
				}
				array_push($table2,'<tr>
				<td><img src="'.$this->config->item('api').'/images/'.$pic.'" title="'.$pictitle.'" style="width:16px;height:16px;border:none;" alt="'.$pictitle.'">&nbsp;&nbsp;'.$row->name.'</td> 
				<td title="Стиль оформления метки">'.$row->attributes.'</td>
				<td title="Группа объектов">'.$row->object_group_name.'</td>
				<td>
					<a class="btn btn-primary btn-mini" style="margin:2px;" href="/admin/gis/'.$row->id.'">Редактировать</a>
				</td>
				</tr>');
			}
			array_push($table2,'</table>');
			$out=$this->load->view('admin/object_types_control_table',$object,true).implode($table2,"\n");
			//$out = implode($table2,"\n");
		}else{
			$out = "Справочник объектов пуст.";
		}
		return $out;
	}

	function gis_save(){
		//print $this->input->post('obj');
		$hc = ($this->input->post('has_child')) ? 1 : 0;
		if(!$this->input->post('obj')){
			$this->db->query("INSERT INTO
			`laz_properties_list` (
			`laz_properties_list`.`row`,
			`laz_properties_list`.element,
			`laz_properties_list`.label,
			`laz_properties_list`.algoritm,
			`laz_properties_list`.selfname,
			`laz_properties_list`.page,
			`laz_properties_list`.property_group,
			`laz_properties_list`.fieldtype,
			`laz_properties_list`.cat,
			`laz_properties_list`.style,
			`laz_properties_list`.object_group,
			`laz_properties_list`.parameters,
			`laz_properties_list`.active,
			`laz_properties_list`.searchable,
			`laz_properties_list`.coef
			)VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",array(
				1,
				1,
				1,
				"",
				$this->input->post('name',TRUE),
				1,
				"",
				"select",
				"",
				"singlewidth",
				$this->input->post('obj_group',TRUE),
				"",
				0,
				1,
				1
			));
			$insert_id=$this->db->insert_id();
			$this->db->query("INSERT INTO
			laz_locations_types(
			laz_locations_types.has_child,
			laz_locations_types.name,
			laz_locations_types.attributes,
			laz_locations_types.object_group,
			laz_locations_types.pl_num,
			laz_locations_types.pr_type)
			VALUES(?,?,?,?,?,?)", Array(
				$hc,
				$this->input->post('name',TRUE),
				$this->input->post('attributes',TRUE),
				$this->input->post('obj_group',TRUE),
				$insert_id,
				$this->input->post('pr_type',TRUE)
			));
		}else{
			$this->db->query("UPDATE
			laz_locations_types
			SET
			laz_locations_types.has_child = ?,
			laz_locations_types.name = ?,
			laz_locations_types.attributes = ?,
			laz_locations_types.object_group = ?,
			laz_locations_types.pl_num = ?,
			laz_locations_types.pr_type = ?
			WHERE
			laz_locations_types.id = ?", Array(
				$hc,
				$this->input->post('name',TRUE),
				$this->input->post('attributes',TRUE),
				$this->input->post('obj_group',TRUE),
				$this->input->post('pl_num',TRUE),
				$this->input->post('pr_type',TRUE),
				$this->input->post('obj',TRUE),
			));

			$this->db->query("UPDATE
			laz_properties_list
			SET
			laz_properties_list.selfname = ?
			WHERE
			laz_properties_list.id = ?", Array(
				$this->input->post('name',TRUE),
				$this->input->post('pl_num',TRUE)
			));
		}
		$this->cachemodel->_menu_build(1,$this->config->item('mod_housing_root'),$this->config->item('mod_gis'));
		$this->cachemodel->_build_object_lists();
		$result = $this->db->query("SELECT 
		GROUP_CONCAT(laz_map_content.id ORDER BY laz_map_content.id SEPARATOR ',') as id
		FROM
		laz_map_content
		INNER JOIN laz_locations_types ON (laz_map_content.a_types = laz_locations_types.id)
		WHERE
		(laz_map_content.a_layers = ?) OR 
		(laz_locations_types.object_group = ?)",array($this->input->post('obj_group',TRUE),$this->input->post('obj_group',TRUE)));
		if($result->num_rows){
			$row = $result->row();
			$mapsets = explode(",",$row->id);
			if(sizeof($mapsets)){
				foreach($mapsets as $mapset){
					if(strlen($mapset)){
						$this->cachemodel->_cache_selector_content($mapset);
					}
				}
			}
		}
	}

#######################################################################
### редактор страниц
#######################################################################
	function find_initial_sheet($root=1){
		$result=$this->db->query("SELECT 
		laz_sheets.id
		FROM
		laz_sheets
		WHERE
		laz_sheets.`root` = ?
		ORDER BY `id`
		LIMIT 1",array($root));
		if($result->num_rows()){
			$row = $result->row();
			$out = $row->id;
		}else{
			$out = $root;
		}
		return $out;
	}
//$sheet_id = $this->find_initial_sheet($root);
	
	function _sheet_tree($root=0,$sheet_id=1){
		$tree="";
		if(!$root){
			$result=$this->db->query("SELECT 
			`laz_sheets`.`id`,
			`laz_sheets`.`parent`,
			`laz_sheets`.`pageorder`,
			`laz_sheets`.`header`,
			`laz_sheets`.`active`,
			`laz_sheets`.`root`
			FROM
			`laz_sheets`
			ORDER BY `laz_sheets`.`parent`,
			`laz_sheets`.`pageorder`",array($root));
		}else{
			$result=$this->db->query("SELECT 
			`laz_sheets`.`id`,
			`laz_sheets`.`parent`,
			`laz_sheets`.`pageorder`,
			`laz_sheets`.`header`,
			`laz_sheets`.`active`,
			`laz_sheets`.`root`
			FROM
			`laz_sheets`
			WHERE
			`laz_sheets`.`root` = ?
			ORDER BY `laz_sheets`.`parent`,
			`laz_sheets`.`pageorder`",array($root));
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

	function sheet_edit($sheet_id){
		$result=$this->db->query("SELECT 
		concat('/page/map/',`laz_map_content`.id) as id,
		`laz_map_content`.name
		FROM
		`laz_map_content`");
		$map_options = array("" => "Не перенаправляется");
		if($result->num_rows()){
			foreach($result->result() as $row){
				$map_options[$row->id] = $row->name;
			}
		}
		$result=$this->db->query("SELECT 
		`laz_sheets`.`id`,
		`laz_sheets`.`text`,
		`laz_sheets`.`root`,
		`laz_sheets`.`owner`,
		`laz_sheets`.`header`,
		`laz_sheets`.`redirect`,
		`laz_sheets`.`date`,
		`laz_sheets`.`ts`,
		`laz_sheets`.`active`,
		`laz_sheets`.`parent`,
		`laz_sheets`.`pageorder`,
		`laz_sheets`.`comment`
		FROM
		`laz_sheets`
		WHERE `laz_sheets`.`id` = ?
		LIMIT 1",array($sheet_id));
		if($result->num_rows()){
			$row = $result->row();
			$act=Array();
			$act['sheet_text']=$row->text;
			$act['header']=$row->header;
			$act['date']=$row->date;
			$act['ts']=$row->ts;
			$act['pageorder']=$row->pageorder;
			$act['redirect']=  form_dropdown('shirts', $map_options, $row->redirect,'id = "sheet_redirect" class="span12"');
			$act['parent']=$row->parent;
			$act['root']=form_dropdown('sheet_root', Array('1'=>"Статьи",'2'=>"Помощь"), $row->root,'id="sheet_root" class="span12"');
			$data = array('name'=>'is_active','id'=>'is_active','value'=>'on','checked'=> $row->active);
			$act['sheet_tree']=$this->adminmodel->_sheet_tree(0,$sheet_id);
			$act['is_active']=form_checkbox($data);
			$act['sheet_id']=$sheet_id;
			$act['comment']=$row->comment;
			$out = $this->load->view('fragments/sheets_editor',$act,true);
		}else{
			$out = "Статья не найдена";
		}
		return $out;
	}

	function sheet_save($sheet_id){
		$is_active = ($this->input->post('is_active')) ? 1 : 0;
		if($this->input->post('save_new')){
			$result=$this->db->query("SELECT 
			MAX(`laz_sheets`.pageorder) AS pageorder
			FROM
			`laz_sheets`
			WHERE `laz_sheets`.`parent` = ?",array($sheet_id));
			if($result->num_rows()){
				$row = $result->row();
				$pageorder=($row->pageorder + 10);
			}else{
				$pageorder= 10;
			}
			$result=$this->db->query("INSERT INTO `laz_sheets`(
			`laz_sheets`.`text`,
			`laz_sheets`.`root`,
			`laz_sheets`.`date`,
			`laz_sheets`.`header`,
			`laz_sheets`.`pageorder`,
			`laz_sheets`.active,
			`laz_sheets`.parent,
			`laz_sheets`.redirect,
			`laz_sheets`.comment
			)VALUES(?,?,?,?,?,?,?,?,?)",array(
				$this->input->post('sheet_text',TRUE),
				$this->input->post('sheet_root',TRUE),
				date("Y-m-d"),
				$this->input->post('sheet_header',TRUE),
				$pageorder,
				$is_active,
				$sheet_id,
				$this->input->post('sheet_redirect',TRUE),
				$this->input->post('sheet_comment',TRUE)
			));
		}else{
			$result=$this->db->query("UPDATE `laz_sheets` SET
			`laz_sheets`.`ts` = NOW(),
			`laz_sheets`.`text` = ?,
			`laz_sheets`.`header` = ?,
			`laz_sheets`.`pageorder` = ?,
			`laz_sheets`.`active` = ?,
			`laz_sheets`.`parent` = ?,
			`laz_sheets`.`redirect` = ?,
			`laz_sheets`.`comment` = ?,
			`laz_sheets`.`root` = ?
			WHERE
			`laz_sheets`.`id` = ?",array(
				$this->input->post('sheet_text',TRUE),
				$this->input->post('sheet_header',TRUE),
				$this->input->post('pageorder',TRUE),
				$is_active,
				$this->input->post('sheet_parent',TRUE),
				$this->input->post('sheet_redirect',TRUE),
				$this->input->post('sheet_comment',TRUE),
				$this->input->post('sheet_root',TRUE),
				$sheet_id
			));
		}
		//return "sheet_save";
		redirect('admin/sheets/edit/'.$sheet_id);
	}

#
######################### BEGIN usermanager section ############################
#
	function users_show(){
		$result=$this->db->query("SELECT 
		`laz_users_admins`.id,
		`laz_users_admins`.class_id,
		`laz_users_admins`.nick,
		`laz_users_admins`.registration_date,
		CONCAT_WS(' ',`laz_users_admins`.name_f,`laz_users_admins`.name_i,`laz_users_admins`.name_o) AS `fio`,
		SUBSTRING(`laz_users_admins`.`info`,1,400) as info,
		`laz_users_admins`.active,
		`laz_users_admins`.rating,
		`laz_users_admins`.valid
		FROM
		`laz_users_admins`
		ORDER BY `laz_users_admins`.`class_id` ASC, fio ASC");
		$output=Array();
		if($result->num_rows()){
			foreach($result->result() as $row){
				$string='<form method=post action="/admin/usermanager/'.$row->id.'/save" class="form-inline span9 well well-small" style="margin-left:0px;">
				<h3>'.$row->nick.' <small>'.$row->fio.'</small></h3>
				'.$row->registration_date.'
				<div><em>'.$row->info.'</em></div>
					<label for="rating" class="span2">Рейтинг</label><input type="text" name="rating" class="span9" id="rating" value="'.$row->rating.'">
					<label for="active" class="span2">Разрешён</label><input type="checkbox" class="span9" id="active" value=1 name="active" '.(($row->active) ? "checked" : '' ).'>
					<label for="valid" class="span2">Проверен</label><input type="checkbox" class="span9" name="valid" value=1 id="valid" '.(($row->valid) ? "checked" : '' ).'>
					<input type="submit" class="btn btn-primary pull-right" value="Сохранить">
				</form>';
				array_push($output,$string);
			}
		}
		return implode($output,"\n");
	}

	function users_save($id){
		$result=$this->db->query("UPDATE laz_users_admins 
		SET
		laz_users_admins.active = ?,
		laz_users_admins.valid = ?,
		laz_users_admins.rating = ?
		WHERE
		laz_users_admins.id = ?", array(
			$this->input->post('active',true),
			$this->input->post('valid',true),
			$this->input->post('rating',true),
			$id
		));
	}
#
######################### END usermanager section ############################
#

#
######################### photoeditor section ############################
#*/