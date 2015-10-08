<!-- <script type="text/javascript" src="<?=$this->config->item('api');?>/jscript/map_styles2.js"></script> -->
<script type="text/javascript" src="<?=$this->config->item('api');?>/jscript/styles2.js"></script>
<script type="text/javascript" src="<?=$this->config->item('api');?>/jscript/yandex_styles.js"></script>
<h4>Справочник типов объектов&nbsp;&nbsp;&nbsp;&nbsp;<small> и их свойства</small></h4>
<form method=post id="gis_edit_form" action="/admin/gis_save">
	<div class="input-prepend">
		<label class="add-on" for="name">Имя:</label>
		<input type="text" id="name" form="gis_edit_form" name="name" value="<?=(isset($name)) ? $name : "";?>">
	</div>

	<div class="input-prepend">
		<label class="add-on" for="haschild">Имеет подчинённые объекты</label>
		<input type="checkbox" form="gis_edit_form" name="has_child" id="haschild" value="1">
	</div>

	<div class="input-prepend">
		<label class="add-on" for="attributes">Оформление:</label>
		<select name="attributes" id="attributes" form="gis_edit_form" name="attributes"></select>
	</div>

	<div class="input-prepend">
		<label class="add-on" for="og">Группа объектов:</label>
		<select form="gis_edit_form" name="obj_group" id="og">
			<?=(isset($obj_group)) ? $obj_group : "";?>
		</select>
	</div>

	<div class="input-prepend">
		<label class="add-on" for="pl_num" title="ID в таблице свойств свойства">Индекс свойства</label>
		<input type="text" id="pl_num" form="gis_edit_form" name="pl_num" value="<?=(isset($pl_num)) ? $pl_num : "";?>">
	</div>

	<div class="input-prepend">
		<label class="add-on" for="pr_type">Тип представления:</label>
		<select form="gis_edit_form" name="pr_type" id="pr_type">
			<option value="1"<?=($pr_type == 1) ? ' selected="selected"' : '';?>>точка</option>
			<option value="2"<?=($pr_type == 2) ? ' selected="selected"' : '';?>>линия</option>
			<option value="3"<?=($pr_type == 3) ? ' selected="selected"' : '';?>>полигон</option>
			<option value="4"<?=($pr_type == 4) ? ' selected="selected"' : '';?>>круг</option>
			<option value="5"<?=($pr_type == 5) ? ' selected="selected"' : '';?>>прямоугольник</option>
		</select>
	</div>

	<button type="submit" form="gis_edit_form" name="obj" value="0" class="btn offset3">Создать новый элемент</button>
	<button type="submit" form="gis_edit_form" name="obj" value="<?=(isset($id)) ? $id : 0;?>" class="btn btn-primary" style="margin-left:20px;">Сохранить элемент</button>
</form>

<table class="table table-bordered table-condensed table-hover">
		<tr>
			<th>Название</th>
			<th>Стиль</th>
			<th>Группа объектов</th>
			<th>Действие</th>
		</tr>
<?=$table2;?>
</table>

<script type="text/javascript">
<!--
	var attributes = '<?=$attributes;?>',
		pr_type    = <?=$pr_type;?>,
		a;
	$("#attributes").prepend('<option value="">Выберите тип</option>');
	for (a in userstyles) {
		if (userstyles.hasOwnProperty(a)) {
			if (pr_type !== undefined && userstyles[a].type === pr_type && a.split("#")[0] !== 'paid') {
				icon = (userstyles[a].iconUrl === undefined) ? "" : 'style="background-image:url(' + userstyles[a].iconUrl + ');background-repeat:no-repeat;background-size: 24px auto;text-indent:22px;"';
				string   = '<option ' + icon + ' value="' + a + '">' + userstyles[a].title + '</option>';
				$("#attributes").append(string);
			}
		}
	}

	if (pr_type !== undefined && pr_type == 1) {
		$("#attributes").append(yandex_styles.join("\n"));
		$("#attributes").append(yandex_markers.join("\n"));
	}

	$("#attributes option[value=\"" + attributes + "\"]").prop('selected', true);
//-->
</script>
