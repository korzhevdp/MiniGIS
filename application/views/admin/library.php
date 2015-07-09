<? if($loc_type) { ?>
<h3><a href="/admin/library/<?=$obj_group;?>"><?=$name;?></a> / <a href="/admin/library/<?=$obj_group;?>/<?=$loc_type;?>"><?=$type_name;?></a></h3><hr>
<? }else{ ?>
<h2><a href="/admin/library/<?=$obj_group;?>"><?=$name;?></a></h2><hr>
<? } ?>
<div id="library">
	<ul class="thumbnails">
		<?=$library;?>
		<li style="margin-left:5px;width:98%;">
			<div class="thumbnail">
				<a style="text-decoration:none;" href="/editor/forms/0/<?=$loc_type;?>" title="Добавить новый объект этого класса"><center><h2> + Добавить объект</h2></center></a>
			</div>
		</li>
	</ul>
</div>
<script type="text/javascript">
<!--
	$("#library").width($(window).width() - 240 + 'px').css("margin-left","0px");
//-->
</script>