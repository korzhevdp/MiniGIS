<h1>Карты. <small>Редактирование представления</small></h1>
<form method=post action="/admin/maps" class="form-horizontal" style="clear:both;">
	<div class="control-group span2" style="margin-bottom:4px">
		<label class="control-label" for="map_view"><span class="label label-info">Ссылка: /map/simple/<?=$mapset?></span></label>
		<div class="controls">
			<select name="map_view" id="map_view" class="span4 offset2" onchange="this.form.submit();"><?=$options?></select>
		</div>
	</div>
</form>
<form method=post action="/admin/maps" class="form-horizontal" style="clear:both">
	<div class="control-group span2">
		<label class="control-label" for="mapset_name">Название</label>
		<div class="controls">
			<input type="text" name="mapset_name" class="span4" id="mapset_name" value="<?=$mapname;?>" placeholder="Email">
		</div>
	</div>

	<div style="clear:both">
		<button type="submit" class="btn btn-primary" style="margin-left:0px;" name="save" value="save">Сохранить представление</button>
		<button type="submit" class="btn" name="new" value="new">Новое представление</button>	
	</div>


	<input type="hidden" name="mapset" value="<?=$mapset;?>">

	<div class="form-horizontal" style="margin-left:0px;clear:both;" id="afl">
		<h3><abbr title="Объекты по, которым осуществляется поиск" class="initialism">Активный слой</abbr></h3>
		<?=$af_layers;?>
	</div>


	<div class="form-horizontal" id="afl2" style="margin-left:0px;margin-top:15px;display:none;clear:both;border-left:3px solid #dddddd;padding-left:5px;margin-top:15px;">
		<h4>Типы объектов</h4>
		<?=$af_types;?>
	</div>

	<button type="button" class="btn btn-info" style="margin-left:0px;margin-top:15px;display:block;clear:both;" id="bflSwitcher">Подключить объекты заднего плана</button>

	<div class="form-horizontal hide bfl" style="margin-left:0px;clear:both;width:600px;" id="bfl"><h3><abbr title="Вспомогательные, статичные объекты. Для удобства пользования картой их число должно быть невелико." class="initialism">Задний план</abbr></h3><?=$ab_layers;?>
	</div>
	<?=$ab_types;?>
</form>
<script type="text/javascript" src="<?=$this->config->item('api');?>/jscript/mc.js"></script>
