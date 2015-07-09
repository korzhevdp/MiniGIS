<span class="control-group form-inline" style="margin:5px;">
	<label class="control-label" for="type" style="margin-top:5px;">Тип объекта:</label>
	<span class="controls">
		<select name="type" id="type" style="margin-left:5px;"><?=$typelist;?></select>
	</span>
</span>



<span id="cpanel1" class="panels controls form-inline hide" style="margin:3px;">
	<span class="input-prepend pull-left" style="margin-left:3px;">
		<span class="add-on" style="margin:0px; width:15px;">Ш</span><input type="text" style="margin:0px; width:90px;" id="m_lat" placeholder="широта точки" title="широта точки" class="pointcoord" />
	</span>
	<span class="input-prepend pull-left" style="margin-left:3px;">
		<span class="add-on" style="margin:0px; width:15px;">Д</span><input type="text" style="margin:0px; width:90px;" id="m_lon" placeholder="долгота точки" title="долгота точки" class="pointcoord" />
	</span>
	<div class="btn-group pull-left">
		<a class="btn dropdown-toggle btn-info btn-small" data-toggle="dropdown" href="#" style="margin-left:2px;margin-top:2px;">Установить <span class="caret"></span></a>
		<ul class="dropdown-menu">
			<li id="setCenter" title="Перемещает центр карты в точку с указанными координатами"><a href="#">Центровать карту</a><li>
			<li id="moveTo" title="Перемещает маркер в точку с указанными координатами"><a href="#">Переместить метку</a><li>
		</ul>
	</div>
	<span style="border-left: 2px dotted #c6c6c6;margin-left:4px;margin-right:4px;margin-top:5px;">&nbsp;</span>
	<label class="checkbox" title="Запрашивать и обновлять адрес объекта при перемещении его метки" for="traceAddress"><input type="checkbox" style="margin-top:4px;" id="traceAddress">Отслеживать адрес</label>
</span>

<!--  -->

<span id="cpanel2" class="panels controls form-inline hide">
	<span style="border-left:2px dotted #c6c6c6; margin-left:4px;margin-right:4px">&nbsp;</span>
	<button type="button" class="btn btn-small addrGetter" title="Рассчитывает адреса узлов">Адреса узлов</button>
	<button type="button" class="btn btn-small nodeGetter" title="Рассчитывает, с улавливанием, совпадения координат узлов и координат объектов из библиотеки">Связи узлов</button>
	<span class="pull-right controls" style="margin-left:5px;margin-top:6px;">Длина: <span class="f_len">0</span> м.&nbsp;&nbsp;Вершин: <span class="f_vtx">0</span></span>
	<span style="border-left:2px dotted #c6c6c6; margin-left:4px;margin-right:4px">&nbsp;</span>
</span>

<!--  -->

<span id="cpanel3" class="panels controls form-inline hide" style="margin:3px;">
	<small>Длина периметра: <span class="f_len">0</span> м.</small>
	<small>Количество вершин: <span class="f_vtx">0</span></small>
	<span style="border-left:2px dotted #c6c6c6; margin-left:4px;margin-right:4px">&nbsp;</span>
	<button type="button" class="btn btn-small addrGetter" title="Рассчитывает адреса узлов">Адреса узлов</button>
	<button type="button" class="btn btn-small chopContour" title="Разбивает геометрию на вектора, расцвеченные по направлению">Разбить на вектора</button>
	<button type="button" class="btn btn-small nodeGetter" title="Рассчитывает, с улавливанием, совпадения координат узлов и координат объектов из библиотеки">Связи узлов</button>
	<button type="button" class="btn btn-small nodeExport" title="Экспорт координат узлов">Экспорт узлов</button>
</span>

<!--  -->

<span id="cpanel4" class="panels controls form-inline hide">
	<span class="input-prepend">
		<span class="add-on" style="width:15px">Ш</span><input type="text" id="cir_lat" placeholder="широта центра" title="широта центра" class="circlecoord" style="width:60px;" />
	</span>
	<span class="input-prepend">
		<span class="add-on" style="width:15px">Д</span><input type="text" id="cir_lon" placeholder="долгота центра" title="долгота центра" class="circlecoord" style="width:60px;" />
	</span>
	<span class="input-prepend input-append">
		<span class="add-on" style="width:45px">радиус</span><input type="text" id="cir_radius" placeholder="радиус круга" title="радиус" class="circlecoord" style="width:60px;" value="100" /><span class="add-on" title="Установка радиуса круга">м.</span>
	</span>
	<span class="btn btn-info btn-small" id="cir_setter" style="margin-left:3px;">Установить</span>
	<small>Площадь: <span id="cir_field">0</span> м.<sup>2</sup></small>
	<small>Окружность: <span id="cir_len">0</span> м.</small>
</span>

<!--  -->

<span id="cpanel5" class="panels controls form-inline hide" style="margin:3px;">
</span>

