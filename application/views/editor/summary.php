<script type="text/javascript">
<!--
var prop = {
	current_zoom : <?=$this->session->userdata('map_zoom');?>,
	current_type : <?=$this->session->userdata('map_type');?>,
	map_center   : [<?=$this->session->userdata('map_center');?>],
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
},
mp = { lang: '<?=$this->session->userdata("lang");?>'}
//-->
</script>
