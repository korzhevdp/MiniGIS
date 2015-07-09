<!doctype html>
<html lang="en">
<head>
	<title>Мониторинг обстановки</title>
	<meta http-equiv="content-type" content="text/html; charset=windows-1251">
	<script type="text/javascript" src="<?=$this->config->item('api');?>/jscript/jquery.js"></script>
	<script type="text/javascript" src="<?=$this->config->item('api');?>/bootstrap/js/bootstrap.js"></script>
	<link href="<?=$this->config->item('api');?>/bootstrap/css/bootstrap.css" rel="stylesheet">
	<link href="<?=$this->config->item('api');?>/jqueryui/css/jqueryui.css" rel="stylesheet">
	<!-- API 2.0 -->
	<script type="text/javascript" src="http://api-maps.yandex.ru/2.0-stable/?coordorder=longlat&amp;load=package.full&amp;mode=debug&amp;lang=ru-RU"></script>
	<script type="text/javascript" src="<?=$this->config->item('api');?>/jscript/mon.js"></script>
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
					<a class="brand" href="/"><img src="<?=$this->config->item('api');?>/images/minigis24.png" style="width:24px;height:24px;border:none;" alt="">&nbsp;Мониторинг обстановки</a>
				</div>
			</div>
		</td>
	</tr>
	<tr>
		<td colspan=2 style="vertical-align:top;" id="YMapsID"></td>
	</tr>
</table>

<!-- плашка Modal -->

<div class="modal hide fade" id="modal_pics" style="width:440px;">
	<div class="modal-header" style="cursor:move;background-color: #d6d6d6">
		<button type="button" class="close" data-dismiss="modal"><i class="icon-remove"></i></button>
		<h4>Изображения объекта</h4>
	</div>
	<div class="modal-body" style="height:300px;overflow:hidden;vertical-align:middle">
		<div id="car_0" class="carousel slide" data-interval=5000 data-pause="hover">
			<!-- Carousel items -->
			<div class="carousel-inner" id="p_coll" style="text-align:center;vertical-align:middle;"></div>
			<!-- Carousel nav -->
			<!-- Carousel controls -->
			<a class="carousel-control left" href="#car_0" data-slide="prev">&lsaquo;</a>
			<a class="carousel-control right" href="#car_0" data-slide="next">&rsaquo;</a>
		</div>

	</div>
	<div class="modal-footer">
		<form method="post" action="/upload/loadimage" enctype="multipart/form-data" class="form-inline row-fluid">
			<input type="file" placeholder="Файл фотографии..." class="span8" size="46" name="userfile" id="userfile" style="width:100%"/>
			<input type="text" name="comment" placeholder="Подпись к картинке..." class="span12" id="upload_cmnt" maxlength="200" title="Подпись к фотографии. Может быть отредактирована в разделе Фотографии" />
			<button type="submit" class="btn btn-primary span12" style="margin-left:0px;margin-top:10px;">Загрузить</button>
			<input type="hidden" name="upload_user" value="frontend_user" />
			<input type="hidden" name="upload_from" value="monitoring/situation" />
			<input type="hidden" name="upload_to_location" id="upl_loc" value="" />
		</form>
	</div>
</div>

<!-- плашка Modal -->

<script type="text/javascript">
<!--
	var mp = {
		zoom: <?=$this->config->item('map_zoom');?>,
		center: [<?=$map_center;?>],
		type: <?=$this->config->item('map_type');?>,
	}
	$("#YMapsID").width($(window).width() - 4 + 'px').height($(window).height() - 50 + 'px');
	$('.modal').modal({show: 0})
//-->
</script>
</body>
</html>