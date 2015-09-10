<div id="YMapsID"></div>
<script type="text/javascript">
<!--
var prop = {
	current_zoom : <?=$this->session->userdata('map_zoom');?>,
	current_type : <?=$this->session->userdata('map_type');?>,
	map_center   : '<?=$this->session->userdata("map_center");?>',
	pagelist     : '<?=$pagelist;?>',
	ttl          : <?=$id;?>,
	description  : '<?=$description;?>',
	attr         : '<?=$attributes;?>',
	name         : '<?=$location_name;?>',
	address      : '<?=$address;?>',
	active       : <?=$active;?>,
	contact      : '<?=$contact_info;?>',
	type         : <?=$type;?>,
	pr           : <?=$pr_type;?>,
	coords       : '<?=$coord_y;?>',
	coords_array : '',
	coords_aux   : '',
	comments     : <?=$comments?>

}

//-->
</script>
<script type="text/javascript" src="<?=$this->config->item('api');?>/jscript/maps2.js"></script>
<form method=post id="tForm" class="hide" style="display:none;" action="/editor/saveobject">
	<input type="hidden" form="tForm" id="l_id" name="id" value="<?=$id;?>">
	<input type="hidden" form="tForm" id="l_name" name="name">
	<input type="hidden" form="tForm" id="l_addr" name="addr">
	<input type="hidden" form="tForm" id="l_desc" name="desc">
	<input type="hidden" form="tForm" id="l_attr" name="attr">
	<input type="hidden" form="tForm" id="l_type" name="type">
	<input type="hidden" form="tForm" id="l_active" name="active">
	<input type="hidden" form="tForm" id="l_contact" name="contact">
	<input type="hidden" form="tForm" id="l_coord_y" name="coord_y">
	<input type="hidden" form="tForm" id="l_coord_y_aux" name="coord_y_aux">
	<input type="hidden" form="tForm" id="l_coord_y_array" name="coord_y_array">
</form>