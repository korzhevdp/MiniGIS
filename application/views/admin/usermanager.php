<h3>Управление пользователями.&nbsp;&nbsp;&nbsp;&nbsp;<small>Рейтинг</small></h3>

<form method="post" action="/admin/user_save">
	<h4><?=$name;?></h4>
	<div class="input-prepend control-group">
		<span class="add-on pre-label">Администратор</span>
		<input type="checkbox" name="admin" value="1"<?=$admin;?>>
	</div>
	<div class="input-prepend control-group">
		<span class="add-on pre-label">Активен</span>
		<input type="checkbox" name="active" value="1"<?=$active;?>>
	</div>
	<div class="input-prepend control-group">
		<span class="add-on pre-label">Проверен</span>
		<input type="checkbox" name="valid" value="1"<?=$valid;?>>
	</div>
	<div class="input-prepend control-group">
		<span class="add-on pre-label">Рейтинг</span>
		<input name="rating" title="Название группы объектов" class="long" maxlength="60" value="<?=$rating;?>" type="text">
	</div>
	<input type="hidden" name="id" value="<?=$id?>">
	<h4>Доступ</h4>
	<ul class="accessgroups">
		<?=$layers?>
	</ul>
	<button type="submit" class="btn btn-primary" style="display:block;clear:both">Сохранить</button>
</form>
<hr>
<table id="userTable" class="table table-bordered table-condensed table-striped">
<tr>
	<th style="width:150px;">Пользователь</th>
	<th>Информация</th>
	<th style="width:100px;">Рейтинг</th>
	<th style="">Админ</th>
	<th style="width:15px;"><i class="icon-ok-sign"       title="Активен"></i></th>
	<th style="width:15px;"><i class="icon-question-sign" title="Проверен"></i></th>
	<th style="width:100px;">Действие</th>
</tr>
<?=$table;?>
</table>