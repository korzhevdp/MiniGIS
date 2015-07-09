<div id="YMapsID"></div>
<script type="text/javascript">
<!--
var prop = {
	current_zoom:	<?=$this->session->userdata('map_zoom');?>,
	current_type:	<?=$this->session->userdata('map_type');?>,
	ttl:			<?=$id;?>,
	description:	'<?=$description;?>',
	attr:			'<?=$attributes;?>',
	name:			'<?=$location_name;?>',
	address:		'<?=$address;?>',
	active:			<?=$active;?>,
	contact:		'<?=$contact_info;?>',
	map_center:		'<?=$this->session->userdata("map_center");?>',
	otype:			<?=$type;?>,
	pr:				<?=$pr_type;?>,
	coords:			'<?=$coord_y;?>',
	pagelist:		'<?=$pagelist;?>'
}

//-->
</script>
<script type="text/javascript" src="<?=$this->config->item('api');?>/jscript/maps2.js"></script>


<input type="hidden" form="tForm" id="l_id" name="id" value="<?=$id;?>">
<input type="hidden" form="tForm" id="l_type" name="type">
<input type="hidden" form="tForm" id="l_active" name="active">
<input type="hidden" form="tForm" id="l_coord_y" name="coord_y">
<input type="hidden" form="tForm" id="l_coord_y_aux" name="coord_y_aux">
<input type="hidden" form="tForm" id="l_coord_y_array" name="coord_y_array">

<form method=post id="tForm" class="hide" style="display:none;" action="/editor/saveobject">
</form>