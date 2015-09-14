<h5><a href="/admin/library">Группы объектов</a>
<? if ($obj_group) {?>
	<? if($loc_type) { ?>
		/ <a href="/admin/library/<?=$obj_group;?>"><?=$name;?></a> / <a href="/admin/library/<?=$obj_group;?>/<?=$loc_type;?>"><?=$type_name;?></a>
	<? }else{ ?>
		/ <a href="/admin/library/<?=$obj_group;?>"><?=$name;?></a>
	<? }
} ?>
</h5><hr>

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