<h4>Семантика объектов&nbsp;&nbsp;&nbsp;&nbsp;<small><?=$og_name;?></small></h4>
<div class="semanticsManager">
	
	<form method=post id="ogp_edit_form" action="/admin/save_semantics">

		<div>
		<div class="input-prepend">
			<label class="add-on" for="ogp1">Страница:</label>
			<input type="text" id="ogp1" form="ogp_edit_form" name="page" value="<?=$page;?>">
		</div>
		</div>

		<div>
		<div class="input-prepend">
			<label class="add-on" for="ogp2">Строка:</label>
			<input type="text" id="ogp2" form="ogp_edit_form" name="row" value="<?=$row;?>">
		</div>
		</div>

		<div>
		<div class="input-prepend">
			<label class="add-on" for="ogp3">Порядок в строке:</label>
			<input type="text" id="ogp3" form="ogp_edit_form" name="element" value="<?=$element;?>">
		</div>
		</div>

		<div>
		<div class="input-prepend">
			<label class="add-on" for="ogp4">Метка:</label>
			<input type="text" id="ogp4" form="ogp_edit_form" name="label" value="<?=$label;?>">
		</div>
		</div>

		<div>
		<div class="input-prepend">
			<label class="add-on" for="ogp5">Имя:</label>
			<input type="text" id="ogp5" form="ogp_edit_form" name="selfname" value="<?=$selfname;?>">
		</div>
		</div>

		<div>
		<div class="input-prepend">
			<label class="add-on" for="ogp6">Алгоритм поиска:</label>
			<select form="ogp_edit_form" name="algoritm" id="ogp6">
				<option value="u" <? if ($algoritm === "u" ) {?> selected="selected"<? } ?>>Соответствует всем признакам</option>
				<option value="ud"<? if ($algoritm === "ud") {?> selected="selected"<? } ?>>Соответствует одному из признаков</option>
				<option value="d"<?  if ($algoritm === "d" ) {?> selected="selected"<? } ?>>d - алгоритм</option>
				<option value="me"<? if ($algoritm === "me") {?> selected="selected"<? } ?>>Больше или равно</option>
				<option value="le"<? if ($algoritm === "le") {?> selected="selected"<? } ?>>Меньше или равно</option>
				<option value="pr"<? if ($algoritm === "pr") {?> selected="selected"<? } ?>>Цена на дату запроса</option>
			</select>
		</div>
		</div>

		<div id="additionalFields" class="hide">
		<div class="input-prepend input-append">
			<label class="add-on" for="ogp6-1">Множитель</label>
			<input type="text" class="short" id="ogp6-1" form="ogp_edit_form" name="multiplier" value="<?=$multiplier;?>">
			<label class="add-on" for="ogp6-2">Делитель</label>
			<input type="text" class="short" id="ogp6-2" form="ogp_edit_form" name="divider" value="<?=$divider;?>">
		</div>
		</div>

		<div>
		<div class="input-prepend">
			<label class="add-on" for="ogp7">Группа свойств:</label>
			<input type="text" list="ogp7list" form="ogp_edit_form" value="<?=$property_group_name;?>" name="property_group" id="ogp7">
			<datalist id="ogp7list">
				<?=$property_group;?>
			</datalist>
		</div>
		</div>

		<div>
		<div class="input-prepend">
			<label class="add-on" for="ogp9">Категория:</label>
			<input type="text" form="ogp_edit_form" name="cat" value="<?=$cat_name;?>" list="ogp9list" id="ogp9">
			<datalist id="ogp9list">
				<?=$cat;?>
			</datalist>
		</div>
		</div>

		<div>
		<div class="input-prepend">
			<label class="add-on" for="ogp14">Привязка:</label>
			<select form="ogp_edit_form" name="linked" id="ogp14">
				<?=$linked;?>
			</select>
		</div>
		</div>

		<div>
		<div class="input-prepend">
			<label class="add-on" for="ogp8">Тип поля:</label>
			<select form="ogp_edit_form" name="fieldtype" id="ogp8">
				<option value="select"   <? if ($fieldtype === "select" )   {?> selected="selected"<? } ?>>Выпадающий список</option>
				<option value="checkbox" <? if ($fieldtype === "checkbox" ) {?> selected="selected"<? } ?>>Флажок</option>
				<option value="text"     <? if ($fieldtype === "text" )     {?> selected="selected"<? } ?>>Текст</option>
				<option value="textarea" <? if ($fieldtype === "textarea" ) {?> selected="selected"<? } ?>>Текстовое поле</option>
			</select>
		</div>
		</div>

		<div>
		<div class="input-prepend">
			<label class="add-on" for="ogp11">Параметры:</label>
			<input type="text" id="ogp11" form="ogp_edit_form" name="parameters" value="<?=$parameters;?>">
		</div>
		</div>

		<div>
		<div class="input-prepend">
			<label class="add-on" for="ogp12">В поиске:</label>
			<input form="ogp_edit_form" type="checkbox" name="searchable" id="ogp12" value="1" <?=$searchable?>>
		</div>
		</div>

		<div>
		<div class="input-prepend">
			<label class="add-on" for="ogp13">Включен:</label>
			<input form="ogp_edit_form" type="checkbox" name="active" id="ogp13" value="1" <?=$active?>>
		</div>
		</div>

		<input type="hidden" form="ogp_edit_form" name="obj" value="<?=$obj;?>">
		<input type="hidden" form="ogp_edit_form" name="object_group" value="<?=$object_group;?>">
		<button type="submit" form="ogp_edit_form" name="mode" class="btn" value="new" style="margin-top:10px;">Создать новый элемент</button>
		<button type="submit" form="ogp_edit_form" name="mode" class="btn btn-primary" value="save" style="margin-left:147px;margin-top:10px;">Сохранить элемент</button>
	</form>
</div>

<h4>Список параметров семантики для этого типа объектов&nbsp;&nbsp;&nbsp;&nbsp;<small>Для описания и поиска</small></h4>
<div class="semanticsManager">
	<table class="table table-bordered table-condensed table-hover">
		<tr>
			<th>Метка</th>
			<th>Название</th>
			<th>Алгоритм</th>
			<th>Группа свойств</th>
			<th>Категория</th>
			<th>Статус</th>
			<th>Действие</th>
		</tr>
		<?=$list;?>
	</table>
</div>

<script type="text/javascript">
<!--
	$("#ogp6").change(function(){
		if ($(this).val() === 'le' || $(this).val() === 'me') {
			$("#additionalFields").removeClass("hide");
		} else {
			$("#additionalFields").addClass("hide");
		}
	})
//-->
</script>