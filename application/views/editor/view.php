<!doctype html>
<html lang="en">
<head>
	<title>Административная консоль - редактор объектов</title>
	<script type="text/javascript" src="<?=$this->config->item('api');?>/jscript/jquery.js"></script>
	<script type="text/javascript" src="<?=$this->config->item('api');?>/bootstrap/js/bootstrap.js"></script>
	<link href="<?=$this->config->item('api');?>/bootstrap/css/bootstrap.css" rel="stylesheet">
	<link href="<?=$this->config->item('api');?>/jqueryui/css/jqueryui.css" rel="stylesheet">
	<!-- API 2.0 -->
	<script type="text/javascript" src="http://api-maps.yandex.ru/2.0-stable/?coordorder=longlat&amp;load=package.full&amp;lang=ru-RU"></script>
	<!-- 	<script type="text/javascript" src="<?=$this->config->item('api');?>/jscript/map_calc.js" type="text/javascript"></script> -->
	<script type="text/javascript" src="<?=$this->config->item('api');?>/jscript/map_styles2.js"></script>
	<!-- EOT API 2.0 -->

	<style type="text/css">
		div.input-append,
		div.input-prepend{
			margin-bottom: 3px;
		}
		#mainPage .add-on{
			font-size:12px;
			width: 150px;
			margin: 0px;
		}
		#mainPage input[type=text]{
			width: 350px;
			font-size:12px;
			height:28px;
			padding: 0px 6px;
		}
		#mainPage select{
			width: 364px;
			font-size:12px;
			height:30px;
			padding: 0px 6px;
		}
		input[type=checkbox]{
			margin-top:9px;
			width:36px;
		}
		span.m_divider{
			border-left:2px dotted #c6c6c6;
			margin-left:4px;
			margin-right:4px;
			margin-top:4px;"
		}
		#lib-btn{
			margin-left:3px;
			margin-top:3px;
		}
		.brand img{
			width:24px;
			height:24px;
			margin-top:-6px;
			border:none;
		}
		#headerTable{
			border:none;
			width:100%;
			height:80%;
		}
		body{
			padding:0px;
			margin:0px;
		}
		#saveBtn{
			margin-right:4px;
			float:right;
		}
		#YMapsID{
			width:100%;
			height:650px;
			margin:0px;
			border:1px solid #c6c6c6;
		}
		.right-controls{
			width:270px;
		}
		#mainPage,
		#propPage {
			height:315px;
			overflow:auto;
			padding-left:5px;
			padding-right:5px;
		}
		#propPage{
			display:none;
		}
		.pageButtons{
			padding: 0px 5px;
			margin:0px;
			height:25px;
		}
		.displayMain, .displayPage{
			height:30px;
			width:20px;
		}
		#updDataBtn{
			float: right;
			margin-right:10px;
			margin-left:20px;
		}
		#closeBalloonBtn{
			float:right;
		}
		.balloonHeader{
			margin-bottom:10px;
			margin-left:10px;
		}
		#propPage label{
			font-size:12px;
			line-height:16px;
			margin-bottom:2px;
			cursor:pointer;
			width:49%;
			display:block;
			float:left;
		}
		#propPage label input[type=checkbox]{
			margin-top: -4px;
		}
		legend{
			margin:2px;
			line-height:28px;
			font-size:14px;
			font-weight:bold;
		}
		textarea{
			width:520px;
		}
		.typefetcher{
			cursor:pointer;
			margin-left:4px;
		}
	</style>
</head>

<body style="">
<table id="headerTable">
	<tr>
		<td colspan=2 class="navbar navbar-inverse">
			<div class="navbar-inner">
				<div class="container">
					<a class="brand" href="/">KORZHEVDP.COM <img src="<?=$this->config->item('api');?>/images/minigis24.png" alt="MiniGIS" title="MiniGis Project"></a>
					<?=$this->load->view('cache/menus/menu', array(), TRUE).$this->usefulmodel->rent_menu().$this->usefulmodel->admin_menu();?>
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
		<td colspan=2 style="vertical-align:top;"><?=$content;?></td>
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

<script type="text/javascript">
<!--
	//$("#YMapsID").width($(window).width() - 210 + 'px').height($(window).height() - 80 + 'px');
	$("#YMapsID").width($(window).width() - 4 + 'px').height($(window).height() - 83 + 'px');
	$("#library").width($(window).width() - 250 + 'px').height($(window).height() - 81 + 'px').css("margin-left","25px");
	$('.modal').modal({show: 0})
//-->
</script>
</body>
</html>