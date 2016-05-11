<?
$uppermenu = array (
		'ru' => array("Êàðòû", "ÃÈÑ"),
		'en' => array("Maps", "GIS"),
		'de' => array("Map", "GIS"),
		'es' => array("Tarjeta", "GIS")
	);
?>
<!-- nav START -->
<ul class="nav navbar-inverse">
<?=$rest;?>
<li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="icon-map-marker icon-white"></i>&nbsp;<?=$uppermenu[$this->session->userdata("lang")][0];?>&nbsp;<b class="caret"></b></a>
<?=$housing;?>
</li>
<li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="icon-tags icon-white"></i>&nbsp;<?=$uppermenu[$this->session->userdata("lang")][1];?>&nbsp;<b class="caret"></b></a>
<?=$gis;?>
</li>
</ul>
<!-- nav END -->