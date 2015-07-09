<!doctype html>
<html lang="en">
<head>
	<title>Административная консоль - редактор объектов</title>
	<meta http-equiv="content-type" content="text/html; charset=windows-1251">
	<script type="text/javascript" src="<?=$this->config->item('api');?>/jscript/jquery.js"></script>
	<script type="text/javascript" src="<?=$this->config->item('api');?>/bootstrap/js/bootstrap.js"></script>
	<link href="<?=$this->config->item('api');?>/bootstrap/css/bootstrap.css" rel="stylesheet">
	<link href="<?=$this->config->item('api');?>/jqueryui/css/jqueryui.css" rel="stylesheet">
	<!-- API 2.0 -->
	<script type="text/javascript" src="http://api-maps.yandex.ru/2.0-stable/?coordorder=longlat&amp;load=package.full&amp;mode=debug&amp;lang=ru-RU"></script>
	<!-- 	<script type="text/javascript" src="<?=$this->config->item('api');?>/jscript/map_calc.js" type="text/javascript"></script> -->
	<script type="text/javascript" src="<?=$this->config->item('api');?>/jscript/map_styles2.js"></script>
	<!-- EOT API 2.0 -->
</head>

<body style="padding:0px;margin:0px;">
<table style="border:none;width:100%;height:80%">
	<tr>
		<td colspan=2 class="navbar navbar-inverse">
			<div class="navbar-inner">
				<div class="container">
					<a class="brand" href="/"><img src="<?=$this->config->item('api');?>/images/minigis24.png" style="width:24px;height:24px;border:none;" alt="">Home</a>
					<?=$this->load->view('cache/menus/menu',array(),TRUE).$this->usefulmodel->rent_menu().$this->usefulmodel->admin_menu();?>
				</div>
			</div>
		</td>
	</tr>
	<tr>
		<td colspan=2 style="height:30px;padding:0px;">
			<a href="/admin/library" class="btn btn-primary btn-small pull-left" style="margin-left:3px;">В библиотеку</a>
			<button type="button" class="btn btn-primary btn-small pull-right" id="saveBtn" style="margin-right:2px" title="Сохранить данные объекта">Сохранить</button>
			<span class="pull-left" style="border-left: 2px dotted #c6c6c6;margin-left:4px;margin-right:4px">&nbsp;</span>
		</td>
	</tr>
	<tr>
		<td style="vertical-align:top;"><?=$content;?></td>
		<td style="vertical-align:top;width:200px;">
		<div style="width:100%;margin-bottom:10px;border-bottom:1px dotted #EEEEFF;margin-bottom:3px;">
			<a href="/editor/geosemantics/2" class="btn btn-warning btn-mini" title="без координатной привязки">без [x,y]</a>
			<a href="/editor/geosemantics/1" class="btn btn-success btn-mini" title="с координатной привязкой">c [x,y]</a>
		</div>
		<?=$objects;?></td>
	</tr>
</table>

<div id="loadPoints" class="modal hide fade" style="width:700px;">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3>Выберите типы объектов для создания опорных точек</h3>
	</div>
	<div class="modal-body">
		<table class="table table-bordered table-condensed table-hover">
		<tr>
			<th style="width:15px;"><input type="checkbox" id="checkAll"></th>
			<th>Типы объектов</th>
			<th style="width:135px;">Тип представления</th>
			<th style="width:75px;">Примечание</th>
		</tr>
		<?=(isset($baspointstypes)) ? $baspointstypes : "";?>
		</table>
	</div>
	<div class="modal-footer">
		<a href="#" data-dismiss="modal" class="btn">Закрыть</a>
		<a href="#" id="loadSelectedObjects" class="btn btn-primary">Загрузить точки</a>
	</div>
</div>

<script type="text/javascript">
<!--
	//$("#YMapsID").width($(window).width() - 210 + 'px').height($(window).height() - 80 + 'px');
	$("#YMapsID").width($(window).width() - 210 + 'px').height($(window).height() - 80 + 'px');
	$('.modal').modal({show: 0})
//-->
</script>
</body>
</html>