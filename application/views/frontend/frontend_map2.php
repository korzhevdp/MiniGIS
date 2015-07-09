<!DOCTYPE html>
<head>
<title>Менеджер ГеоТочек: <?=$title;?></title>
<meta name='yandex-verification' content='74872298f6a53977' />
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">
	<meta name="keywords" content="<?=$keywords;?>">
	<!-- API 2.0 -->
	<script type="text/javascript" src="<?=$this->config->item('api');?>/jscript/jquery.js"></script>
	<script src="http://api-maps.yandex.ru/2.0/?coordorder=longlat&amp;load=package.full&amp;mode=debug&amp;lang=ru-RU" type="text/javascript"></script>
	<script type="text/javascript" src="<?=$this->config->item('api');?>/jscript/map_styles2.js"></script>

	<script type="text/javascript" src="<?=$this->config->item('api');?>/jqueryui/js/jqueryui.js"></script>
	<script type="text/javascript" src="<?=$this->config->item('api');?>/bootstrap/js/bootstrap.js"></script>
	<script type="text/javascript" src="<?=$this->config->item('api');?>/jscript/maps_frontend3.js"></script>
	<!-- EOT API 2.0 -->
	<link href="<?=$this->config->item('api');?>/bootstrap/css/bootstrap.css" rel="stylesheet">
	<link href="<?=$this->config->item('api');?>/jqueryui/css/jqueryui.css" rel="stylesheet">
	<link href="<?=$this->config->item('api');?>/css/frontstyle.css" rel="stylesheet" media="screen" type="text/css">
</head>
<body>
<!-- навигацыя -->
	<div class="navbar">
		<div class="navbar-inner">
			<a class="brand" href="http://www.korzhevdp.com">KORZHEVDP.COM</a>
			<ul class="nav">
				<li><a href="http://www.korzhevdp.com">Дом</a></li>
				<li class="active"><a href="http://maps.korzhevdp.com">Проекты</a></li>
				<li><a href="http://works.korzhevdp.com">Работы</a></li>
				<li><a href="http://flood.korzhevdp.com">Проза</a></li>
				<li><a href="http://rock.korzhevdp.com">Музыка</a></li>
			</ul>
		</div>
	</div>
	<div class="navbar navbar-inverse">
		<div class="navbar-inner">
			<a class="brand" href="http://maps.korzhevdp.com">ПРОЕКТ&nbsp;&nbsp;<small>Minigis.NET</small>&nbsp;&nbsp;<img src="<?=$this->config->item('api')?>/images/minigis24.png" style="width:24px;height:24px;border:none;margin-top:-6px;" alt=""></a>
			<?=$menu;?>
		</div>
	</div>
	<div class="well span4 map_name"><?=$map_header;?></div>
<!-- навигацыя -->
	<table class="main_page_body" id="main_table">
		<tr>
			<td id="YMapsID"><!-- сам текст -->
				<div id="SContainer" class="well">
					<div id="YMHead" class="well">
						<span class="pull-left" style="margin-left:10px;">
							<i class="icon-move icon-white" style="margin:0px;padding:0px;vertical-align:middle"></i> 
							Навигатор 
						</span>
						<i class="icon-chevron-down icon-white pull-right" id="navdown" style="display:none;"></i>
						<i class="icon-chevron-up icon-white pull-right" id="navup"></i>
					</div>
						<ul class="nav nav-tabs" id="navheader">
							<li class="active"><a href="#mainselector" data-toggle="tab">Я ищу</a></li>
							<li><a href="#results" data-toggle="tab">Я нашёл <span id="ResultHead2"></span></a></li>
						</ul>
						
						<div class="tab-content" id="navigator">
							<div id="mainselector" class="tab-pane active">
								<?=$selector;?>
							</div>
							<div id="results" class="tab-pane">
								Фильтр<br>
								<input type="text" id="objfilter" style="margin-left:4px;width:170px;height:24px;" title="Отобрать объекты по содержимому этой строки">
								<i class="icon-filter" style="vertical-align:middle;"></i>
								<ul id="resultBody">

								</ul>
							</div>
						</div>
					</div>
				</div>
			</td>
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
			<input type="file" placeholder="Файл..." class="span8" size="46" name="userfile" id="userfile" />
			<input type="text" name="comment" placeholder="Подпись к картинке..." class="span12" id="upload_cmnt" maxlength="200" title="Подпись к фотографии. Может быть отредактирована в разделе Фотографии" />
			<button type="submit" class="btn btn-primary span12" style="margin-left:0px;margin-top:10px;">Загрузить</button>
			<input type="hidden" name="upload_user" value="frontend_user" />
			<input type="hidden" name="upload_from" value="page/map/<?=$mapset?>" />
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
		mapset: <?=$mapset?>
	}
//-->
</script>
<script type="text/javascript" src="<?=$this->config->item('api');?>/jscript/mapUI.js"></script>
<?=$footer;?>
	<!-- Yandex.Metrika counter -->
	<script type="text/javascript">
	(function (d, w, c) {
		(w[c] = w[c] || []).push(function() {
			try {
				w.yaCounter24426704 = new Ya.Metrika({id:24426704,
						clickmap:true,
						trackLinks:true,
						accurateTrackBounce:true});
			} catch(e) { }
		});

		var n = d.getElementsByTagName("script")[0],
			s = d.createElement("script"),
			f = function () { n.parentNode.insertBefore(s, n); };
		s.type = "text/javascript";
		s.async = true;
		s.src = (d.location.protocol == "https:" ? "https:" : "http:") + "//mc.yandex.ru/metrika/watch.js";

		if (w.opera == "[object Opera]") {
			d.addEventListener("DOMContentLoaded", f, false);
		} else { f(); }
	})(document, window, "yandex_metrika_callbacks");
	</script>
	<noscript><div><img src="//mc.yandex.ru/watch/24426704" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
	<!-- /Yandex.Metrika counter -->
</body>
</html>