<!doctype html>
<html lang="en">
<head>
	<title>Административная консоль - редактор объектов</title>
	<script type="text/javascript" src="<?=$this->config->item('api');?>/jscript/jquery.js"></script>
	<script type="text/javascript" src="<?=$this->config->item('api');?>/bootstrap/js/bootstrap.js"></script>
	<link href="<?=$this->config->item('api');?>/bootstrap/css/bootstrap.css" rel="stylesheet">
	<link href="<?=$this->config->item('api');?>/css/editor.css" rel="stylesheet">
	<!-- API 2.0 -->
	<script type="text/javascript" src="http://api-maps.yandex.ru/2.0-stable/?coordorder=longlat&amp;load=package.full&amp;lang=ru-RU"></script>
	<!-- 	<script type="text/javascript" src="<?=$this->config->item('api');?>/jscript/map_calc.js" type="text/javascript"></script> -->
	<script type="text/javascript" src="<?=$this->config->item('api');?>/jscript/map_styles2.js"></script>
	<!-- EOT API 2.0 -->
</head>

<body class="altEditor">

<table class="editorTable">
<tr>
	<td class="leftColumn" style="height:50px;">
		<a href="/admin/library/<?=$liblink?>" id="lib-btn" class="btn btn-primary btn-block">В библиотеку</a>
	</td>
	<td class="rightColumn">
		<h4 class="altEditorHeader"><?=$location_name;?>&nbsp;&nbsp;&nbsp;&nbsp;<small><?=$description;?></small>
			<span class="btn-group pull-right">
				<button class="btn btn-info" id="pointsLoad" title="Загрузить опорные точки из имеющихся в библиотеке объектов">Опорные точки</button>
				<button class="btn dropdown-toggle btn-info" data-toggle="dropdown"><span class="caret"></span></button>
				<ul class="dropdown-menu">
					<li><a href="#" id="pointsClear">Очистить опорные точки</a></li>
				</ul>
			</span>
			<span class="btn-group pull-right" data-toggle="buttons-radio">
				<span class="btn btn-info mapsw" id="toYandex">Yandex</span>
				<span class="btn btn-info mapsw" id="toGoogle">Google</span>
			</span>
		</h4>
		<ul class="nav nav-tabs">
			<?=$pagelist_alt;?>
		</ul>
	</td>
</tr>
<tr>
	<td class="leftColumn">
		<div class="input-prepend">
			<span class="add-on" style="margin:0px; width:70px;">Название</span><input type="text" form="tForm" id="l_name" name="object[name]" value="<?=$location_name;?>">
		</div>
		<div class="input-prepend">
			<span class="add-on" style="margin:0px; width:70px;">Адрес</span><input type="text" form="tForm" id="l_addr" class="l_addr" name="object[addr]" value="<?=$address;?>">
		</div>
		<div class="input-prepend">
			<span class="add-on" style="margin:0px; width:70px;">Стиль</span><select form="tForm" id="l_attr" name="object[attr]" class="styles"></select>
		</div>
		<div class="input-prepend">
			<span class="add-on" style="margin:0px; width:70px;">Телефон</span><input type="text" form="tForm" id="l_cont" name="object[contact]" value="<?=$contact_info;?>">
		</div>

		<hr>
		<?=$panel;?>
		<label class="checkbox" title="Объект доступен для поиска" for="l_act"><input type="checkbox" class="l_act" style="margin-top:4px;" id="l_act">Опубликовано</label>
		<hr>

		<button type="button" class="btn btn-primary btn-block" id="saveBtn" title="Сохранить данные объекта">Сохранить</button>
	</td>
	<td class="rightColumn tab-content">
		<div class="tab-pane active" id="YMapsID"></div>
		<div class="tab-pane propPage" id="propPage"></div>
	</td>


	</td>
</tr>
</table>
<?=$content;?>

<div id="loadPoints" class="modal hide fade" style="width:700px;">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3>Выберите типы объектов для создания опорных точек</h3>
	</div>
	<div class="modal-body" style="height:400px;overflow:auto;">
		<table class="table table-bordered table-condensed table-hover">
		<tr>
			<th style="width:15px;"><input type="checkbox" id="checkAll"></th>
			<th>Типы объектов</th>
			<th style="width:135px;">Тип</th>
			<th style="width:25px;"><i class="icon-info-sign"></i></th>
			<th style="width:25px;"><i class="icon-list"></i></th>
		</tr>
		<?=(isset($baspointstypes)) ? $baspointstypes : "";?>
		</table>
	</div>
	<div class="modal-footer">
		<a href="#" data-dismiss="modal" class="btn">Закрыть</a>
		<a href="#" id="loadSelectedObjects" class="btn btn-primary">Загрузить точки</a>
	</div>
</div>

<div id="nodeExport" class="modal hide fade" style="width:700px;">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3>Координаты вершин геометрии</h3>
	</div>
	<div class="modal-body" style="height:400px;overflow:auto;" id="exportedNodes"></div>
	<div class="modal-footer">
		<a href="#" data-dismiss="modal" class="btn">Закрыть</a>
		<a href="#" id="loadSelectedObjects" class="btn btn-primary">Загрузить точки</a>
	</div>
</div>

<script type="text/javascript">
<!--
	$('.modal').modal({ show: 0 })
//-->
</script>
<script type="text/javascript" src="<?=$this->config->item('api');?>/jscript/maps2.js"></script>

</body>
</html>