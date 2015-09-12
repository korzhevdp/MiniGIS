<style type="text/css">
	#afl, #bfl {
		height       : 100px;
		margin   : 0px;
		padding-left : 5px;
		clear        : both;
	}
	#afl2 {
		margin-left  : 0px;
		padding      : 0px;
		clear        : both;
	}
	#afl li, #bfl li{
		margin       : 2px;
		display      : block;
		width        : 350px;
		height       : 40px;
		float        : left;
		border       : 1px solid #dddddd;
		padding-left : 5px;
	}
	#afl li label, #bfl li label{
		padding-top: 10px;
		padding-bottom: 10px;
	}
	#bflSwitcher {
		margin-left  : 0px;
		margin-top   : 15px;
		clear: both;
		display:block;
	}
	.object_list {
		margin       : 5px;
		border       : 1px solid #eeeeee;
		float        : left;
		width        : 250px;
		height       : 300px;
		overflow     : auto;
		display      : inline-block;
		padding      : 4px;
		padding-top  : 0px;
	}
	.object_list h5{
		background-color :  #eeeeee;
		margin           : 0px -4px -2px -4px;
	}
	.inactive{
		color: #aaa;
		background-color:#eee;
	}
</style>
<h2>Карты. <small>Редактирование представления</small></h2>
<form method=post action="/admin/maps" class="form-horizontal" style="clear:both;">
	<div class="control-group span2" style="margin-bottom:4px">
		<label class="control-label" for="map_view"><span class="label label-info">Ссылка: /map/simple/<?=$mapset?></span></label>
		<div class="controls">
			<select name="map_view" id="map_view" class="span4 offset2">
				<?=$options?>
			</select> <button type="submit">показать</button>
		</div>
	</div>
	
</form>
<form method=post action="/admin/maps" class="form-horizontal" style="clear:both">
	<div class="control-group span2">
		<label class="control-label" for="mapset_name">Название</label>
		<div class="controls">
			<input type="text" name="mapset_name" class="span4" id="mapset_name" value="<?=$mapname;?>" placeholder="Название представления карты">
		</div>
	</div>

	<div style="clear:both">
		<button type="submit" class="btn btn-primary" style="margin-left:0px;" name="save" value="save">Сохранить представление</button>
		<button type="submit" class="btn" name="new" value="new">Новое представление</button>	
	</div>


	<input type="hidden" name="mapset" value="<?=$mapset;?>">

	<ul id="afl">
		<?=$ca_layers;?>
	</ul>

	<h5>Типы объектов</h5>
	<ul id="afl2">
		<?=$ca_types;?>
	</ul>

	
	<div id="bfl">
	<h5>Задний план</h5>
		<?=$cb_layers;?>
	</div>

	<ul id="afl2">
		<?=$cb_types;?>
	</ul>

</form>
<script type="text/javascript">
<!--
	var a_layers  = [<?=$a_layers;?>],
		a_types   = [<?=$a_types;?>],
		b_layers  = [<?=$b_layers;?>],
		b_types   = [<?=$b_types;?>],
		disabled_layers   = [<?=$disabled_layers;?>];
//-->
</script>
<script type="text/javascript" src="<?=$this->config->item('api');?>/jscript/mc.js"></script>
