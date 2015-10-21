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
		width        : 250px;
		height       : 34px;
		float        : left;
		border       : 1px solid #dddddd;
		padding-left : 5px;
	}
	#afl li label, #bfl li label{
		padding-top: 5px;
		padding-bottom: 7px;
	}
	#afl li:hover, #bfl li:hover{
		background-color: #F6F6F6;
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
	<div class="input-prepend input-append control-group">
		<span class="add-on pre-label">Ссылка: /map/simple/<?=$mapset?></span>
		<select name="map_view" id="map_view">
			<?=$options?>
		</select> 
		<button class="add-on pre-label btn btn-primary" type="submit" style="height:30px;">Показать</button>
	</div>
</form>

<form method=post action="/admin/maps" class="form-horizontal" style="clear:both">
	<div class="input-prepend input-append control-group">
		<span class="add-on pre-label">Название</span>
		<input type="text" name="mapset_name" class="span4" id="mapset_name" value="<?=$mapname;?>" placeholder="Название представления карты">
	</div>

	<ul class="nav nav-tabs" style="clear:both;">
		<li class="active"><a href="#tabr1" data-toggle="tab">Активные объекты</a></li>
		<li><a href="#tabr2" data-toggle="tab">Задний план</a></li>
	</ul>

	<div class="tab-content" style="clear:both;">
		<div id="tabr1" class="tab-pane active">
			<ul id="afl">
				<?=$ca_layers;?>
			</ul>
			<ul id="afl2">
				<?=$ca_types;?>
			</ul>
		</div>
		<div id="tabr2" class="tab-pane">
			<div id="bfl">
				<?=$cb_layers;?>
			</div>
			<ul id="afl2">
				<?=$cb_types;?>
			</ul>
			<input type="hidden" name="mapset" value="<?=$mapset;?>">
		</div>
	</div>
	<div style="clear:both;margin-top:20px;">
		<button type="submit" class="btn btn-primary" style="margin-left:0px;" name="save" value="save">Сохранить представление</button>
		<button type="submit" class="btn" name="new" value="new">Новое представление</button>	
	</div>
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
