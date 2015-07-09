<h4>Семантика объектов&nbsp;&nbsp;&nbsp;&nbsp;<small><?=$og_name;?></small></h4>
	<div class="well well-small" style="padding-bottom:35px;">
		<div class="input-prepend">
			<label class="add-on" for="ogp1">Страница:</label>
			<input type="text" id="ogp1" form="ogp_edit_form" name="page" value="<?=$page;?>">
		</div><br>

		<div class="input-prepend">
			<label class="add-on" for="ogp2">Строка:</label>
			<input type="text" id="ogp2" form="ogp_edit_form" name="row" value="<?=$row;?>">
		</div><br>

		<div class="input-prepend">
			<label class="add-on" for="ogp3">Порядок в строке:</label>
			<input type="text" id="ogp3" form="ogp_edit_form" name="element" value="<?=$element;?>">
		</div><br>

		<div class="input-prepend">
			<label class="add-on" for="ogp4">Метка:</label>
			<input type="text" id="ogp4" form="ogp_edit_form" name="label" value="<?=$label;?>">
		</div><br>

		<div class="input-prepend">
			<label class="add-on" for="ogp5">Имя:</label>
			<input type="text" id="ogp5" form="ogp_edit_form" name="selfname" value="<?=$selfname;?>">
		</div><br>

		<div class="input-prepend">
			<label class="add-on" for="ogp6">Алгоритм:</label>
			<select form="ogp_edit_form" name="algoritm" id="ogp6">
				<?=$algoritm;?>
			</select>
		</div><br>

		<div class="input-prepend">
			<label class="add-on" for="ogp7">Группа свойств:</label>
			<input type="text" list="ogp7list" form="ogp_edit_form" value="<?=$property_group_name;?>" name="property_group" id="ogp7">
			<datalist id="ogp7list">
				<?=$property_group;?>
			</datalist>
		</div><br>

		<div class="input-prepend">
			<label class="add-on" for="ogp9">Категория:</label>
			<input type="text" form="ogp_edit_form" name="cat" value="<?=$cat_name;?>" list="ogp9list" id="ogp9">
			<datalist id="ogp9list">
				<?=$cat;?>
			</datalist>
		</div><br>

		<div class="input-prepend">
			<label class="add-on" for="ogp14">Привязка:</label>
			<select form="ogp_edit_form" name="linked" id="ogp14">
				<?=$linked;?>
			</select>
		</div><br>

		<div class="input-prepend">
			<label class="add-on" for="ogp8">Тип поля:</label>
			<select form="ogp_edit_form" name="fieldtype" id="ogp8">
				<?=$fieldtype;?>
			</select>
		</div><br>

		<div class="input-prepend">
			<label class="add-on" for="ogp11">Параметры:</label>
			<input type="text" id="ogp11" form="ogp_edit_form" name="parameters" value="<?=$parameters;?>">
		</div><br>

		<div class="input-prepend">
			<label class="add-on" for="ogp12">В поиске:</label>
			<input type="checkbox" name="searchable" id="ogp12" value="1" <?=$searchable?>>
		</div><br>

		<div class="input-prepend">
			<label class="add-on" for="ogp13">Включен:</label>
			<input type="checkbox" name="active" id="ogp13" value="1" <?=$active?>>
		</div><br>

		<input type="hidden" form="ogp_edit_form" name="obj" value="<?=$obj;?>">
		<input type="hidden" form="ogp_edit_form" name="object_group" value="<?=$object_group;?>">
		<button type="submit" form="ogp_edit_form" name="mode" class="btn" value="new" style="margin-top:10px;">Создать новый элемент</button>
		<button type="submit" form="ogp_edit_form" name="mode" class="btn btn-primary" value="save" style="margin-left:147px;margin-top:10px;">Сохранить элемент</button>

	</div>
<form method=post id="ogp_edit_form" action="/admin/save_semantics"></form>

<h4>Список параметров семантики для этого типа объектов&nbsp;&nbsp;&nbsp;&nbsp;<small>Для описания и поиска</small></h4>
<table class="table table-bordered table-condensed">
	<tr>
		<th>Метка</th>
		<th>Название</th>
		<th>Группа свойств</th>
		<th>Категория</th>
		<th>Статус</th>
		<th>Действие</th>
	</tr>
	<?=$list;?>
</table>
<hr>