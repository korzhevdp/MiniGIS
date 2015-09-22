<!doctype html>
<html>
	<head>
		<title> Административная консоль сайта </title>
		<link href="<?=$this->config->item("api");?>/bootstrap/css/bootstrap.css" rel="stylesheet" media="screen" type="text/css">
		<link href="<?=$this->config->item("api");?>/css/login.css" rel="stylesheet" media="screen" type="text/css">
	</head>
<body>
<script type="text/javascript" src="<?=$this->config->item("api");?>/jscript/jquery.js"></script>
<script type="text/javascript" src="<?=$this->config->item('api');?>/bootstrap/js/bootstrap.js"></script>

<div class="navbar navbar-inverse">
	<div class="navbar-inner">
		<div class="container">
			<a class="brand span2" href="/"><img src="/images/minigis24.png" width="24" height="24" border="0" alt=""> Minigis.NET</a>
				<?=$menu;?>
		</div>
	</div>
</div>

<ul class="nav nav-tabs" style="clear:both;">
	<li><a href="#tabr1" data-toggle="tab" <? if ($page == 1) { ?>class="active"<? } ?>>Авторизация</a></li>
	<li><a href="#tabr2" data-toggle="tab" <? if ($page == 2) { ?>class="active"<? } ?>>Регистрация</a></li>
	<li><a href="#tabr3" data-toggle="tab" <? if ($page == 3) { ?>class="active"<? } ?>>Восстановление пароля</a></li>
</ul>

<div class="tab-content" style="clear:both;">
	<div id="tabr1" class="tab-pane<? if ($page == 1) { ?> active <? } ?>">
		<h3 style="margin-bottom:24px;">Авторизуйтесь. <small>Мы ценим Ваше участие</small></h3>
		<form method=post action="/login">
			<label class="span2">Имя пользователя:</label>
			<input class="span6" type="text" name="name"><br>
			<label class="span2">Пароль:</label>
			<input class="span6" type="password" name="pass"><br>
			
			<a class="btn btn-large" title="Не туда попал" href="<?=base_url();?>">Возврат на главную страницу</a>
			<button type="submit" class="btn btn-primary btn-large">Вход</button>
		</form>
	</div>

	<div id="tabr2" class="tab-pane<? if ($page == 2) { ?> active <? } ?>">
		<form method=post action="/login/register" class="form-inline">
			<h3 style="margin-bottom:24px;">Зарегистрируйтесь. <small>Мы ценим Вашу готовность помочь проекту</small></h3>

			<label class="span2" for="name">Имя пользователя:<span style="color:red">*</span></label>
			<input class="span6" title="Имя пользователя будет использоваться при входе в систему." type="text" id="name" name="name" value="<?=$this->input->post('name', true);?>"><br>
			
			<label class="span2" for="pass">Пароль:<span style="color:red">*</span></label>
			<input class="span6" type="password" title="Введите пароль. Не менее 6 букв и цифр." id="pass" name="pass"><br>

			<label class="span2" for="pass2">Повторите пароль:<span style="color:red">*</span></label>
			<input class="span6" type="password" title="Повторите пароль" id="pass2" name="pass2"><br>

			<label class="span2" for="email">Адрес e-mail:<span style="color:red">*</span></label>
			<input class="span6" title="Введите адрес электронной почты, куда будет направлено письмо для завершения регистрации" type="text" id="email" name="email" value="<?=$this->input->post('email', true);?>"><br>

			<label class="span2">Введите символы с картинки:<span style="color:red">*</span></label>
			<input class="span6" title="Всего лишь одна маленькая проверка на человечность - картинка ниже." type="text" id="cpt" name="cpt"><br><br><br>
			
			<label class="span2">Картинка:<span style="color:red">*</span></label>
			<img src="/<?=$captcha;?>" class="well" title="Введите с клавиатуры наиболее похожие английские буквы и/или цифры. Регистр неважен." alt=""><br>
			
			<a class="btn btn-large" title="Не туда попал" href="<?=base_url();?>">Возврат на главную страницу</a>
			<button type="submit" class="btn btn-primary btn-large">Регистрация</button>
		</form>
	</div>

	<div id="tabr3" class="tab-pane<? if ($page == 3) { ?> active <? } ?>">
		<h3 style="margin-bottom:24px;">Восстановление пароля. <small>Мы ценим Вашу целеустремлённость</small></h3>
		<form method=post action="/login/rpass/run">
			<label class="span2" title="введите адрес электронной почты, куда будет направлено письмо для восстановления пароля">Адрес e-mail:</label>
			<input class="span6" type="text" id="email" name="email" value="<?=$this->input->post('email',TRUE);?>"><br>

			<label class="span2" title="всего лишь одна маленькая проверка на человечность">Введите символы с картинки:<span style="color:red">*</span></label>
			<input class="span6" type="text" id="cpt" name="cpt"><br><br><br>
			
			<label class="span2">Картинка:<span style="color:red">*</span></label>
			<img src="/<?=$captcha;?>" class="well" title="Введите с клавиатуры наиболее похожие английские буквы и/или цифры. Регистр неважен." alt="captcha"><br>
			
			<a class="btn btn-large" title="Не туда попал" href="<?=base_url();?>">Возврат на главную страницу</a>
			<button type="submit" class="btn btn-primary btn-large">Выслать новый код авторизации!</button>
		</form>
	</div>
</div>

<div class="alert alert-warning<? if (!strlen($errorlist)) { ?> hide<? } ?>" style="clear:both;margin:40px; width:500px;"><a class="close" data-dismiss="alert" href="#">x</a>
	<h4 class="alert-heading">Незадача...&nbsp;&nbsp;&nbsp;<small>К несчастью, обнаружены досадные недоразумения:</small></h4>
	<ol>
		<li>
		<?=$errorlist;?>
		</li>
	</ol>
</div>

<?=$this->config->item("site_reg_hello");?>
<div id="announcer"></div>
</body>
</html>