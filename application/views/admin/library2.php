<ul class="nav nav-tabs" style="clear:both;">
	<li<? if ($page == 1) { ?>  class="active"<? } ?>><a href="#tabr1" data-toggle="tab">Группы объектов</a></li>
	<li<? if ($page == 2) { ?>  class="active"<? } ?>><a href="#tabr2" data-toggle="tab">Семантика</a></li>
</ul>

<div class="tab-content" style="clear:both;">
	<div id="tabr1" class="tab-pane<? if ($page == 1) { ?> active<? } ?>">
		<?=$content?>
	</div>
	
	<div id="tabr2" class="tab-pane<? if ($page == 2) { ?> active<? } ?>">
		<?=$content2?>
	</div>
</div>

<script type="text/javascript">
<!--
	$("#library").width($(window).width() - 240 + 'px').css("margin-left","0px");
//-->
</script>