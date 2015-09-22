<script src="http://api-maps.yandex.ru/1.1/index.xml?key=<?=$yandex_key;?>" type="text/javascript"></script>
<SCRIPT type="text/javascript" src="/jscript/map_styles.js"></SCRIPT>
<script src="/jscript/maps.js" type="text/javascript"></script>
<div id="YMapsID"></div>
<INPUT TYPE="hidden" ID="map_center" name="map_center" value="<?=form_prep($maps_center);?>">
<SCRIPT TYPE="text/javascript">
<!--
	<?=$objects;?>
//-->
</SCRIPT>