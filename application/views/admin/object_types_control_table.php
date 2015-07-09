<h4>Справочник типов объектов&nbsp;&nbsp;&nbsp;&nbsp;<small> и их свойства</small></h4>

<div class="input-prepend">
	<label class="add-on" for="name">Имя:</label>
	<input type="text" id="name" form="gis_edit_form" name="name" value="<?=(isset($name)) ? $name : "";?>">
</div><br>

<div class="input-prepend">
	<label class="add-on" for="haschild">Имеет подч.</label>
	<input type="checkbox" form="gis_edit_form" name="has_child" id="haschild" value="">
</div><br>

<div class="input-prepend" style="margin-bottom:2px;">
	<label class="add-on" for="attributes">Атрибуты:</label>
	<input type="text" id="attributes" form="gis_edit_form" name="attributes" value="<?=(isset($attributes)) ? $attributes : "";?>">
</div><br>

<div class="input-prepend" style="margin-bottom:2px;">
	<label class="add-on" for="og">Группа объектов:</label>
	<select form="gis_edit_form" name="obj_group" id="og">
		<?=(isset($obj_group)) ? $obj_group : "";?>
	</select>
</div><br>

<div class="input-prepend" style="margin-bottom:2px;">
	<label class="add-on" for="pl_num" title="ID в таблице свойств свойства">Индекс свойства</label>
	<input type="text" id="pl_num" form="gis_edit_form" name="pl_num" value="<?=(isset($pl_num)) ? $pl_num : "";?>">
</div><br>

<div class="input-prepend" style="margin-bottom:2px;">
	<label class="add-on" for="pr_type">Тип представления:</label>
	<select form="gis_edit_form" name="pr_type" id="pr_type">
		<option value="1" <?=($pr_type == 1) ? 'selected="selected"' : '';?>>точка</option>
		<option value="2" <?=($pr_type == 2) ? 'selected="selected"' : '';?>>линия</option>
		<option value="3" <?=($pr_type == 3) ? 'selected="selected"' : '';?>>полигон</option>
		<option value="4" <?=($pr_type == 4) ? 'selected="selected"' : '';?>>круг</option>
		<option value="5" <?=($pr_type == 5) ? 'selected="selected"' : '';?>>прямоугольник</option>
	</select>
</div><br>

<button type="submit" form="gis_edit_form" name="obj" value=0 class="btn offset4">Создать новый элемент</button>
<button type="submit" form="gis_edit_form" name="obj" value="<?=(isset($id)) ? $id : 0;?>" class="btn btn-primary" style="margin-left:20px;">Сохранить элемент</button>

<form method=post id="gis_edit_form" action="/admin/gis_save" style="display:none"></form>
<table class="table table-bordered table-condensed table-hover">
		<tr>
			<th>Название</th>
			<th>Стиль</th>
			<th>Группа объектов</th>
			<th>Действие</th>
		</tr>
<?=$table2;?>
</table>