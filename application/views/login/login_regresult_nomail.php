<!doctype html public "-//w3c//dtd html 4.0 transitional//en">
<html>
<head>
	<title>Регистрация закончена</title>
	<link href="<?=$this->config->item('api');?>/bootstrap/css/bootstrap.css" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="/css/loginstyle.css">
</head>
<body>
<div style="margin:20px;width:800px;display:table-cell;padding-left:25px;">
	<h3>Регистрация закончена.</h3>
	Была успешно создана учётная запись пользователя с именем и паролем, которые были указаны форме регистрации.
	<br>
	<br>
	запомните ваше имя пользователя: <b><?=$username;?></b><br>
	и, особенно, пароль: <b>*********</b> (Вы должны были его запомнить) <br><br>

	Вы можете начать работу на сайте перейдя по этой ссылке: <a href="<?=$this->config->item("base_url")?>admin"><?=$this->config->item("base_url")?></a>
</div>
</body>
</html>