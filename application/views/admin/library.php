<? if($loc_type) { ?>
<h3><a href="/admin/library/<?=$obj_group;?>"><?=$name;?></a> / <a href="/admin/library/<?=$obj_group;?>/<?=$loc_type;?>"><?=$type_name;?></a></h3><hr>

<? }else{ ?>
<h2><a href="/admin/library/<?=$obj_group;?>"><?=$name;?></a></h2><hr>
<? } ?>
<div id="library">
	<ul class="thumbnails">
		<?=$library;?>
	</ul>
</div>
<script type="text/javascript">
<!--
	$("#library").width($(window).width() - 240 + 'px').css("margin-left","0px");
//-->
</script>