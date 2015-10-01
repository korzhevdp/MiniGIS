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

<body>
<table id="headerTable">
	<tr>
		<td colspan=2 class="navbar navbar-inverse">
			<div class="navbar-inner">
				<div class="container">
					<?=$this->config->item('brand');?>
					<?=$menu;?>
				</div>
			</div>
		</td>
	</tr>
	<tr>
		<td>
			<a href="/admin/library/<?=$liblink?>" id="lib-btn" class="btn btn-primary btn-small pull-left">В библиотеку</a>
			<span class="m_divider" >&nbsp;</span>
			<?=$panel;?>
		</td>
		<td class="right-controls">
			<span class="m_divider" >&nbsp;</span>
			<span class="btn-group">
				<button class="btn btn-small btn-info" id="pointsLoad" title="Загрузить опорные точки из имеющихся в библиотеке объектов">Опорные точки</button>
				<button class="btn btn-small dropdown-toggle btn-info" data-toggle="dropdown"><span class="caret"></span></button>
				<ul class="dropdown-menu">
					<li><a href="#" id="pointsClear">Очистить опорные точки</a></li>
				</ul>
			</span>
			<span class="m_divider" >&nbsp;</span>
			<button type="button" class="btn btn-primary btn-small" id="saveBtn" title="Сохранить данные объекта">Сохранить</button>
		</td>
	</tr>
	<tr>
		<td colspan=2 style="vertical-align:top;">
		<div id="YMapsID"></div>
		<?=$content;?>
		</td>
	</tr>
</table>

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

<!-- <div class="console"><pre>&nbsp;&nbsp;</pre></div> -->

<script type="text/javascript">
<!--
	$("#YMapsID").width($(window).width() - 4 + 'px').height($(window).height() - 83 + 'px');
	$('.modal').modal({show: 0})
//-->
</script>
<script type="text/javascript" src="<?=$this->config->item('api');?>/jscript/maps2.js"></script>
<form method=post id="tForm" class="hide" style="display:none;" action="/editor/saveobject">
	<input type="hidden" form="tForm" id="l_id" name="id" value="<?=$id;?>">
	<input type="hidden" form="tForm" id="l_name" name="name">
	<input type="hidden" form="tForm" id="l_addr" name="addr">
	<input type="hidden" form="tForm" id="l_desc" name="desc">
	<input type="hidden" form="tForm" id="l_attr" name="attr">
	<input type="hidden" form="tForm" id="l_type" name="type">
	<input type="hidden" form="tForm" id="l_active" name="active">
	<input type="hidden" form="tForm" id="l_contact" name="contact">
	<input type="hidden" form="tForm" id="l_coord_y" name="coord_y">
	<input type="hidden" form="tForm" id="l_coord_y_aux" name="coord_y_aux">
	<input type="hidden" form="tForm" id="l_coord_y_array" name="coord_y_array">
</form>
</body>
</html>