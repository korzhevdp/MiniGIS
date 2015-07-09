<!doctype html>
<html>
	<head>
		<title> Административная консоль сайта </title>
		<meta http-equiv="content-type" content="text/html; charset=windows-1251">
		<link href="<?=$this->config->item("api");?>/bootstrap/css/bootstrap.css" rel="stylesheet">
		<link href="<?=$this->config->item("api");?>/css/frontstyle.css" rel="stylesheet" media="screen" type="text/css">

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

<ul class="nav nav-tabs span9" style="clear:both;">
	<li><a href="#tabr1" data-toggle="tab" class="active">Авторизация</a></li>
	<li><a href="#tabr2" data-toggle="tab">Регистрация</a></li>
	<li><a href="#tabr3" data-toggle="tab">Восстановление пароля</a></li>
</ul>

<div class="tab-content span9" style="clear:both;">
	<div id="tabr1" class="tab-pane active">
		<h1 style="margin-bottom:24px;">Авторизуйтесь. <small>Мы ценим Ваше участие</small></h1>
		<form method=post action="/login">
			<label class="span2">Имя пользователя:</label>
			<input class="span6" type="text" name="name"><br>
			<label class="span2">Пароль:</label>
			<input class="span6" type="password" name="pass"><br>
			
			<a class="btn span2" title="Не туда попал" href="http://giscenter.home">Возврат на главную страницу</a>
			<button type="submit" class="btn btn-primary pull-right btn-large span4 offset2">Вход</button>
		</form>
	</div>

	<div id="tabr2" class="tab-pane">
		<form method=post action="/login/register" class="form-inline">
			<h1 style="margin-bottom:24px;">Зарегистрируйтесь. <small>Мы ценим Вашу готовность помочь проекту</small></h1>

			<label class="span2" for="name">Имя пользователя:<span style="color:red">*</span></label>
			<input class="span6" title="Имя пользователя будет использоваться при входе в систему." type="text" id="name" name="name" value="<?=$this->input->post('name',TRUE);?>"><br>
			
			<label class="span2" for="pass">Пароль:<span style="color:red">*</span></label>
			<input class="span6" type="password" title="Введите пароль. Не менее 6 букв и цифр." id="pass" name="pass"><br>

			<label class="span2" for="pass2">Повторите пароль:<span style="color:red">*</span></label>
			<input class="span6" type="password" title="Повторите пароль" id="pass2" name="pass2"><br>

			<label class="span2" for="email">Адрес e-mail:<span style="color:red">*</span></label>
			<input class="span6" title="Введите адрес электронной почты, куда будет направлено письмо для завершения регистрации" type="text" id="email" name="email" value="<?=$this->input->post('email',1);?>"><br>

			<label class="span2">Введите символы с картинки:<span style="color:red">*</span></label>
			<input class="span6" title="Всего лишь одна маленькая проверка на человечность - картинка ниже." type="text" id="cpt" name="cpt"><br><br><br>
			
			<label class="span2">Картинка:<span style="color:red">*</span></label>
			<img src="/<?=$captcha;?>" class="well" title="Введите с клавиатуры наиболее похожие английские буквы и/или цифры. Регистр неважен." alt=""><br>
			
			<button class="btn span2" title="Не туда попал" onclick="window.location = '<?=base_url();?>'">Возврат на главную страницу</button>
			<button type="submit" class="btn btn-primary btn-large span4 offset2">Регистрация</button>
		</form>
	</div>

	<div id="tabr3" class="tab-pane">
		<h1 style="margin-bottom:24px;">Восстановление пароля. <small>Мы ценим Вашу целеустремлённость</small></h1>
		<form method=post action="/login/rpass/run">
			<label class="span2" title="введите адрес электронной почты, куда будет направлено письмо для восстановления пароля">Адрес e-mail:</label>
			<input class="span6" type="text" id="email" name="email" value="<?=$this->input->post('email',TRUE);?>"><br>

			<label class="span2" title="всего лишь одна маленькая проверка на человечность">Введите символы с картинки:<span style="color:red">*</span></label>
			<input class="span6" type="text" id="cpt" name="cpt"><br><br><br>
			
			<label class="span2">Картинка:<span style="color:red">*</span></label>
			<img src="/<?=$captcha;?>" class="well" title="Введите с клавиатуры наиболее похожие английские буквы и/или цифры. Регистр неважен." alt="captcha"><br>
			
			<button class="btn span2" title="Не туда попал" onclick="window.location = '<?=base_url();?>'">Возврат на главную страницу</button>
			<button type="submit" class="btn btn-primary btn-large pull-right span4 offset2">Выслать новый код авторизации!</button>
		</form>
	</div>
</div>

<div id="reg_errors">
	<?=$errorlist;?>
</div>

<?=$this->config->item("site_reg_hello");?>
<div id="announcer"></div>
</body>
</html>