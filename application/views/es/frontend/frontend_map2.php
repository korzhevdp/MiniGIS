<!DOCTYPE html>
<head>
<title>Gestor De Puntos De Geo: <?=$title;?></title>
<meta name='yandex-verification' content='74872298f6a53977' />
<?=$this->load->view("shared/shared_js_css");?>
</head>
<body>
<!-- навигацыя -->
	<div class="navbar navbar-inverse">
		<div class="navbar-inner">
			<?=$brand.$menu;?>
		</div>
	</div>
	<div class="well span4 map_name"><?=$map_header;?></div>
<!-- навигацыя -->
	<table class="main_page_body" id="main_table">
		<tr>
			<td id="YMapsID"><!-- сам текст -->
				<div id="SContainer" class="well">
					<div class="head well">
						<span class="pull-left tag">
							<i class="icon-move icon-white"></i>
							Navegador
						</span>
						<i class="icon-chevron-down icon-white pull-right" id="navdown"></i>
						<i class="icon-chevron-up icon-white pull-right" id="navup"></i>
					</div>
						<ul class="nav nav-tabs" id="navheader">
							<li class="active"><a href="#mainselector" id="iSearch" data-toggle="tab">Estoy buscando</a></li>
							<li><a href="#results" id="iFound" data-toggle="tab">He encontrado <span id="ResultHead2"></span></a></li>
						</ul>
						
						<div class="tab-content" id="navigator">
							<div id="mainselector" class="tab-pane active">
								<?=$selector;?>
							</div>
							<div id="results" class="tab-pane">
								<div class="grouplabel">Filtro</div>
								<input type="text" id="objfilter" title="Seleccionar objetos por el contenido de esta línea" placeholder="escriba el nombre">
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
		<h4>La imagen de un objeto</h4>
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
			<input type="file" placeholder="El archivo..." class="span8" size="46" name="userfile" id="userfile" />
			<input type="text" name="comment" placeholder="En la imagen de la firma..." class="span12" id="upload_cmnt" maxlength="200" title="La leyenda de la foto. Puede ser editado en la sección de Fotos" />
			<button type="submit" class="btn btn-primary span12" style="margin-left:0px;margin-top:10px;">Descargar</button>
			<input type="hidden" name="upload_user" value="frontend_user" />
			<input type="hidden" name="upload_from" value="page/map/<?=$mapset?>" />
			<input type="hidden" name="upload_to_location" id="upl_loc" value="" />
		</form>
	</div>
</div>

<div class="modal hide fade" id="langSelector" style="width:440px;">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal"><i class="icon-remove"></i></button>
		Choose your preferred language
	</div>
	<div class="modal-body">
		<form method=post action="/map/set_language">
			<button type="submit" class="btn btn-large langSubmitter" name="lang" value="ru" title="Russian"><img src="http://api.korzhevdp.com/images/flag_ru.png" width="32" height="32" border="0" alt=""></button>
			<button type="submit" class="btn btn-large langSubmitter" name="lang" value="en" title="English"><img src="http://api.korzhevdp.com/images/flag_en.png" width="32" height="32" border="0" alt=""></button>
			<button type="submit" class="btn btn-large langSubmitter" name="lang" value="de" title="German"><img src="http://api.korzhevdp.com/images/flag_de.png" width="32" height="32" border="0" alt=""></button>
			<button type="submit" class="btn btn-large langSubmitter" name="lang" value="es" title="Spanish"><img src="http://api.korzhevdp.com/images/flag_es.png" width="32" height="32" border="0" alt=""></button>
			<input type="hidden" name="redirect" value="<?=$this->uri->uri_string()?>">
		</form>
	</div>
	<div class="modal-footer"></div>
</div>
<!-- плашка Modal -->

<?=$mapconfig;?>

<script type="text/javascript" src="<?=$this->config->item('api');?>/jscript/maps_frontend3.js"></script>
<script type="text/javascript" src="<?=$this->config->item('api');?>/jscript/mapUI.js"></script>
<?=$footer;?>

</body>
</html>