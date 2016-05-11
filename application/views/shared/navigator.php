<?
$navigator = array (
		'ru' => array("Навигатор", "Я ищу", "Я нашёл", "Фильтр", "Отобрать только содержащие текст", "Искомый текст"),
		'en' => array("Navigator", "I'm looking for", "I found", "Filter", "Selects objects by pattern", "input pattern"),
		'de' => array("Register", "Ich suche nach", "Gefunden", "Selektion", "Отобрать объекты по содержимому этой строки", "введите название"),
		'es' => array("Navegador", "Estoy buscando", "He encontrado", "Filtro", "Seleccionar objetos por el contenido de esta línea", "escriba el nombre"),
	);
?>
<div id="SContainer" class="well">
	<div class="head well">
		<span class="pull-left tag">
			<i class="icon-move icon-white"></i>
			<?=$navigator[$this->session->userdata("lang")][0];?>
		</span>
		<i class="icon-chevron-down icon-white pull-right" id="navdown"></i>
		<i class="icon-chevron-up icon-white pull-right" id="navup"></i>
	</div>
		<ul class="nav nav-tabs" id="navheader">
			<li class="active"><a href="#mainselector" id="iSearch" data-toggle="tab"><?=$navigator[$this->session->userdata("lang")][1];?></a></li>
			<li><a href="#results" id="iFound" data-toggle="tab"><?=$navigator[$this->session->userdata("lang")][2];?>&nbsp;&nbsp;<span id="ResultHead2"></span></a></li>
		</ul>
		
		<div class="tab-content" id="navigator">
			<div id="mainselector" class="tab-pane active">
				<?=$selector;?>
			</div>
			<div id="results" class="tab-pane">
				<div class="grouplabel"><?=$navigator[$this->session->userdata("lang")][3];?></div>
				<input type="text" id="objfilter" title="<?=$navigator[$this->session->userdata("lang")][4];?>" placeholder="<?=$navigator[$this->session->userdata("lang")][5];?>">
				<ul id="resultBody">

				</ul>
			</div>
		</div>
	</div>
</div>