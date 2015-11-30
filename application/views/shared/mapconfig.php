<script type="text/javascript">
<!--
	var mp = {
		zoom   : <?=$this->config->item('map_zoom');?>,
		center : [<?=$map_center;?>],
		site   : '<?=$this->config->item("base_url");?>',
		group  : <?=$group;?>,
		type   : <?=$this->config->item('map_type');?>,
		lang   : '<?=$this->session->userdata("lang");?>',
		mapset : <?=$mapset;?>,
		otype  : <?=$otype;?>
	},
	<?=$switches?>
//-->
</script>