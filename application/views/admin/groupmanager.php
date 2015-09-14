<script src="http://api-maps.yandex.ru/2.0/?coordorder=longlat&amp;load=package.standard&amp;lang=ru-RU" type="text/javascript"></script>
<h3>Управление группами объектов.&nbsp;&nbsp;&nbsp;&nbsp;<small>Города и отрасли</small></h3>
<div id="YMaps" style="width:570px;height:260px;border:1px solid grey;margin-bottom:20px;"></div>

<form method="post" action="/admin/group_save">
	<div class="input-prepend control-group">
		<span class="add-on pre-label">Название</span>
		<input name="nick" title="Название группы объектов" class="long" maxlength="60" value="<?=$name;?>" type="text">
	</div>
	<div class="input-prepend control-group">
		<span class="add-on pre-label">Иконка</span>
		<input name="icon" title="Иконка группы объектов" class="long" maxlength="60" value="<?=$icon;?>" type="text">
	</div>
	<div class="input-prepend control-group">
		<span class="add-on pre-label">Активна</span>
		<?=$active;?>
	</div>
	<input type="hidden" name="country"    id="country"><br>
	<input type="hidden" name="map_center" id="map_center" value="<?=$coord;?>">
	<input type="hidden" name="map_zoom"   id="map_zoom"   value="<?=$zoom;?>">
</form>

<span class="btn">Создать группу</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="btn btn-primary">Сохранить группу</span>
<table id="userTable" class="table table-bordered table-condensed table-striped">
<tr>
	<th style="width:250px;">Название</th>
	<th style="width:50px;">Иконка</th>
	<th style="">Опорный масштаб</th>
	<th style="">Центр области</th>
	<th>Активна</th>
	<th>Редактировать</th>
</tr>
<?=$table;?>
</table>
<input type="hidden" id="map_center" value="<?=$this->session->userdata("map_center");?>">
<input type="hidden" id="map_zoom"   value="<?=$this->session->userdata("map_zoom");?>">
<input type="hidden" id="country"    value="">
<script type="text/javascript">
<!--
var map,
	maptypes = { 1: 'yandex#map', 2: 'yandex#satellite', 3: 'google#map', 4: 'osm#map' };
ymaps.ready(init);

function init () {
	var object,
		mapcenter,
		cursor,
		maptype = maptypes[1],
		mapzoom = parseInt($("#map_zoom").val(), 10);

	if ($("#map_center").val().length >= 3) {
		mc = $("#map_center").val();
		mapcenter = [parseFloat(mc.split(",")[0]), parseFloat(mc.split(",")[1])];
	} else {
		mapcenter = [ymaps.geolocation.longitude, ymaps.geolocation.latitude];
	}
	if (mapzoom < 0 || mapzoom > 23){
		mapzoom = 11;
	}

	function updateHiddens() {
		$("#map_center").val(object.geometry.getCoordinates());
		$("#country").val(ymaps.geolocation.country + "  " + ymaps.geolocation.region);
	}

	map = new ymaps.Map('YMaps', {
		center    : mapcenter,
		type      : maptype,
		zoom      : mapzoom,
		behaviors : ["default", "scrollZoom"]
	}, {});
		
	cursor = map.cursors.push('crosshair', 'arrow');
	cursor.setKey('crosshair');
	map.controls.add('zoomControl');
	object = new ymaps.Placemark(
		{ type: 'Point', coordinates: mapcenter },
		{ description: 'Пользовательский центр карты' },
		ymaps.option.presetStorage.get("twirl#violetIcon")
	);

	map.geoObjects.add(object);
	object.options.set({ draggable: true });
	object.events.add('dragend', function() {
		updateHiddens();
	});
	map.events.add('click', function(click) {
		object.geometry.setCoordinates(click.get('coordPosition'));
	});
	map.events.add('boundschange', function(e) {
		$("#map_zoom").val(e.get('newZoom'));
	});
	updateHiddens();
}
//-->
</script>