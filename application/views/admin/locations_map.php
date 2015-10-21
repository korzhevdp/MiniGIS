<div class="controls">
	<? if(($pr==2 || $pr==3) && !strlen($encpath)){ ?>
		<label class="checkbox">опорные точки</label>&nbsp;&nbsp;&nbsp;
		<?=$bas_points;?>&nbsp;&nbsp;&nbsp;<?=$hasdynamic;?>
		Общая длина: <span id="f_len">0</span> м.
	<?}?>
	<? if($pr==3 && !strlen($encpath)){ ?>
		<button type="button" class="btn btn-info btn-small" style="margin:0px;" id="toVertex"><i class="icon-arrow-right icon-white"></i> Опорные вершины</button>
		<button type="button" class="btn btn-info btn-small" style="margin:0px;display:none;" id="toGeometry"><i class="icon-arrow-right icon-white"></i> Полигон</button><?=$hasdynamic;?>
		Периметр: <span id="f_per">0</span> м.
	<?}?>
</div>

<!-- API 2.0 -->
	<script type="text/javascript" src="http://api.home/jscript/map_styles2.js"></script>
	<script type="text/javascript" src="http://api.home/jscript/maps2.js"></script>
<!-- EOT API 2.0 -->

<div id="YMapsID" class="span12" style="height:450px;margin-left:0px;"></div>
<button type="submit" value="1" name="save" class="btn btn-primary span3">Разместить на карте</button>
<button type="submit" value="0" name="save" class="btn span3">Очистить отметку</button>

<input type="hidden" id="current_zoom" value="15">
<input type="hidden" id="current_type" value="yandex#satellite">
<input type="hidden" id="description" value="<?=$description?>">
<input type="hidden" id="location_id" value="<?=$id?>">
<input type="hidden" id="map_center" name="map_center" value="<?=form_prep($maps_center);?>">
<input type="hidden" id="pr" name="pr" value="<?=$pr;?>">
<input type="hidden" id="coords" name="yandex_coords" value="<?=$encpath;?>">
<input type="hidden" id="encpath" name="encpath" value="<?=$encpath;?>">
<input type="hidden" id="baspath" name="baspath" value="">
<script type="text/javascript">
<!--
// список объектов;
	<?=$objects;?>
//-->
</script>