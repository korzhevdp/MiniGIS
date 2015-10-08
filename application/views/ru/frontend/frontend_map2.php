<!DOCTYPE html>
<head>
<title>Менеджер ГеоТочек: <?=$title;?></title>
<meta name='yandex-verification' content='74872298f6a53977' />
<?=$this->load->view("shared/shared_js_css");?>
</head>
<body>
<!-- навигацыя -->
	<div class="navbar navbar-inverse">
		<div class="navbar-inner">
			<?=$this->config->item('brand');?>
			<?=$menu;?>
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
							Навигатор
						</span>
						<i class="icon-chevron-down icon-white pull-right" id="navdown"></i>
						<i class="icon-chevron-up icon-white pull-right" id="navup"></i>
					</div>
						<ul class="nav nav-tabs" id="navheader">
							<li class="active"><a href="#mainselector" id="iSearch" data-toggle="tab">Я ищу</a></li>
							<li><a href="#results" id="iFound" data-toggle="tab">Я нашёл <span id="ResultHead2"></span></a></li>
						</ul>
						
						<div class="tab-content" id="navigator">
							<div id="mainselector" class="tab-pane active">
								<?=$selector;?>
							</div>
							<div id="results" class="tab-pane">
								<div class="grouplabel">Фильтр</div>
								<input type="text" id="objfilter" title="Отобрать объекты по содержимому этой строки" placeholder="введите название">
								<ul id="resultBody">

								</ul>
							</div>
						</div>
					</div>
				</div>
			</td>
		</tr>
	</table>

<!-- плашки Modal -->
<?=$this->load->view($this->session->userdata('lang')."/frontend/modals");?>
<!-- плашки Modal -->


<script type="text/javascript">
<!--
	var mp = {
		zoom   : <?=$this->config->item('map_zoom');?>,
		center : [<?=$map_center;?>],
		type   : <?=$this->config->item('map_type');?>,
		lang   : '<?=$this->session->userdata("lang");?>',
		mapset : <?=$mapset;?>,
		otype  : <?=$otype;?>
	},
	<?=$switches?>
//-->
</script>
<script type="text/javascript" src="<?=$this->config->item('api');?>/jscript/maps_frontend3.js"></script>
<script type="text/javascript" src="<?=$this->config->item('api');?>/jscript/mapUI.js"></script>
<?=$footer;?>

</body>
</html>