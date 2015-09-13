<script src="http://api-maps.yandex.ru/2.0/?coordorder=longlat&amp;load=package.standard&amp;lang=ru-RU" type="text/javascript"></script>
<h3>Профиль.&nbsp;&nbsp;&nbsp;&nbsp;<small><?=$name_f."&nbsp;".$name_i;?> с нами с <?=$registration_date?></small></h3>

<ul class="nav nav-tabs" style="clear:both;">
	<li><a href="#tabr1" data-toggle="tab" class="active">Информация о пользователе</a></li>
	<li><a href="#tabr2" data-toggle="tab">Сменить пароль</a></li>
</ul>
<div class="tab-content" style="clear:both;">
	<div id="tabr1" class="tab-pane active">
		<form method="post" action="/user/user_save">
		<div class="input-prepend control-group">
			<span class="add-on pre-label">Пользователь</span>
			<input name="nick" id="nick" readonly="readonly" title="Имя авторизации" class="long" maxlength="60" value="<?=$nick;?>" type="text">
		</div>

		<div class="input-prepend control-group">
			<span class="add-on pre-label">Фамилия</span>
			<input name="name_f" id="name_f" title="Фамилия пользователя" class="long" maxlength="60" value="<?=$name_f;?>" type="text">
		</div>

		<div class="input-prepend control-group">
			<span class="add-on pre-label">Имя</span>
			<input name="name_i" id="name_i" title="Фамилия пользователя" class="long" maxlength="60" value="<?=$name_i;?>" type="text">
		</div>

		<div class="input-prepend control-group">
			<span class="add-on pre-label">Отчество</span>
			<input name="name_o" id="name_o" title="Фамилия пользователя" class="long" maxlength="60" value="<?=$name_o;?>" type="text">
		</div>
		<div class="input-prepend control-group">
			<span class="add-on pre-label">Язык системы</span>
			<select name="lang" id="lang">
				<?=$lang;?>
			</select>
		</div>
		<div class="input-prepend control-group">
			<span class="add-on pre-label">Дополнительная информация</span>
			<textarea name="user_info" id="user_info" rows="1" cols="1" ><?=$info?></textarea>
		</div>
		
		<div id="YMaps" style="width:570px;height:260px;border:1px solid grey;">
			
		</div>
		<input type="hidden" name="country"    id="country"><br>
		<input type="hidden" name="map_center" id="map_center" value="<?=$map_center;?>">
		<input type="hidden" name="map_zoom"   id="map_zoom"   value="<?=$map_zoom;?>">
		<input type="hidden" name="map_type"   id="map_type"   value="<?=$map_type;?>">
		<button type="submit" class="btn btn-primary" title="Сохранить информацию о пользователе">Сохранить</button>
		</form>
	</div>

	<div id="tabr2" class="tab-pane">
		<div class="control-group" style="margin-bottom:3px;">
			<label class="control-label span2" for="oldpass">Старый пароль:</label>
			<div class="controls">
				<input type="password" name="oldpass" id="oldpass">
			</div>
		</div>

		<div class="control-group" style="margin-bottom:3px;">
			<label class="control-label span2" for="pass1">Новый пароль:</label>
			<div class="controls">
				<input type="password" name="pass1" id="pass1">
			</div>
		</div>


		<div class="control-group" style="margin-bottom:3px;">
			<label class="control-label span2" for="pass2">Новый пароль ещё раз:</label>
			<div class="controls">
				<input type="password" name="pass2" id="pass2">
			</div>
		</div>
		

		<button type="reset" class="btn offset3" value="Очистить поля">Очистить поля</button>
		<button type="submit" class="btn btn-primary" title="Изменить пароль" style="margin-left:20px;">Изменить пароль</button>

		<div class="alert alert-info hide" id="password_memo"
			<h4>Внимание!</h4>
			Для большей уверенности в безопасности Ваших данных рекомендуем, но не обязываем, использовать пароли, отвечающие следующим критериям:
			<ol>
				<li>Длина пароля должна быть от 6 символов и более;</li>
				<li>В пароли рекомендуется включать прописные и строчные буквы, цифры и иные символы, которые присутствуют на клавиатуре;</li>
				<li>Менять пароли не реже одного раз в месяц.</li>
			</ol>
			Хранение пароля в секрете, а также сответствие его рекомендуемым требованиям является первейшей обязанностью пользователя. Администраторы сайта не имеют доступа к содержимому паролей и по этой причине не смогут восстановить его. Пожалуйста, игнорируйте просьбы высылать или вводить Ваше имя пользователя и пароль куда-либо, кроме форм авторизации этого сайта.<br><br>
			Кроме того, Вы предоставляете администрации сайта право хранить и обрабатывать переданные данные в объёме, необходимом для функционирования сайта.
		</div>
	</div>
</div>
<script type="text/javascript">
<!--
var map;
ymaps.ready(init);

function init () {
	var object,
		mapcenter,
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
		zoom      : mapzoom,
		behaviors : ["default", "scrollZoom"]
	}, {});
	map.controls.add('zoomControl');
	object = new ymaps.Placemark(
		{ type: 'Point', coordinates: mapcenter },
		{ description: 'Пользовательский центр карты' },
		ymaps.option.presetStorage.get("twirl#violetIcon")
	);
	map.geoObjects.add(object);
	object.options.set({ draggable: true });
	object.events.add('dragend', function(){
		updateHiddens();
	});
	map.events.add('click', function(click){
		object.geometry.setCoordinates(click.get('coordPosition'));
	});
	updateHiddens();
}
//-->
</script>