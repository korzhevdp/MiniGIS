<h2>Профиль. <small>Информация о пользователе</small></h2><hr>

<form method=post id="user_form" action="/admin/user_save" class="form-horizontal well well-small" style="margin-left:0px;width:600px;float:left;">
	<h4>Информация о пользователе</h4>
	<div class="control-group" style="margin-bottom:3px;">
		<label class="control-label span2" for="user_nick" title="Имя (никнейм) пользователя используемое для авторизации">Пользователь:</label>
		<div class="controls">
			<input type="text" name="user_nick" id="user_nick" readonly value="<?=$nick;?>">
		</div>
	</div>
	
	<div class="control-group" style="margin-bottom:3px;">
		<label class="control-label span2" for="user_name_f" title="фамилия пользователя">Фамилия:</label>
		<div class="controls row">
			<input type="text" name="user_name_f" id="user_name_f" value="<?=$name_f;?>">
		</div>
	</div>

	<div class="control-group" style="margin-bottom:3px;">
		<label class="control-label span2" for="user_name_f" title="имя пользователя">Имя:</label>
		<div class="controls row">
			<input type="text" name="user_name_i" id="user_name_i" value="<?=$name_i;?>">
		</div>
	</div>

	<div class="control-group" style="margin-bottom:3px;">
		<label class="control-label span2" for="user_name_o" title="отчество пользователя">Отчество:</label>
		<div class="controls">
			<input type="text" name="user_name_o" id="user_name_o" value="<?=$name_o;?>">
		</div>
	</div>

	<div class="control-group" style="margin-bottom:3px;">
		<label class="control-label span2" for="user_info" title="Информация о пользователе: телефон, электронная почта, почтовый адрес и т.д.">Контактная информация:</label>
		<div class="controls">
			<textarea name="user_info" id="user_info" rows="6" cols="10" ><?=$info?></textarea>
		</div>
	</div>
	<button type="submit" class="btn btn-primary offset5" title="Сохранить информацию о пользователе">Сохранить</button>
</form>


<div class="well well-small" style="margin-left:0px;width:300px;float:right">
	<h4>Внимание!</h4>
	Для большей уверенности в безопасности Ваших данных рекомендуем, но не обязываем, использовать пароли, отвечающие следующим критериям:
	<ol>
		<li>Длина пароля должна быть от 6 символов и более;</li>
		<li>В пароли рекомендуется включать прописные и строчные буквы, цифры и иные символы, которые присутствуют на клавиатуре;</li>
		<li>Менять пароли не реже одного раз в месяц.</li>
	</ol>
	Хранение пароля в секрете, а также сответствие его рекомендуемым требованиям является первейшей обязанностью пользователя. Администраторы сайта не имеют доступа к содержимому паролей и по этой причине не смогут восстановить его. Пожалуйста, игнорируйте просьбы высылать или вводить Ваше имя пользователя и пароль куда-либо, кроме форм авторизации этого сайта.<br><br>
	<div class="alert alert-error">
		Заполняя форму, Вы предоставляете администрации сайта право хранить и обрабатывать переданные данные в объёме, необходимом для функционирования сайта.
	</div>
</div>


<form method=post action="/admin/user_newpassword" class="well well-small form-horizontal" style="margin-left:0px;width:600px;float:left">
		<h4>Сменить пароль</h4>

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
</form>

