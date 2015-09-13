<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
	<TITLE> Административная консоль сайта </TITLE>
	<META NAME="Generator" CONTENT="EditPlus">
	<META NAME="Author" CONTENT="">
	<META NAME="Keywords" CONTENT="">
	<META NAME="Description" CONTENT="">
	<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">
	<SCRIPT type="text/javascript" SRC="/jscript/mechanization.js"></SCRIPT>
	<SCRIPT type="text/javascript" SRC="/jscript/jquery.js"></SCRIPT>
	<SCRIPT type="text/javascript" SRC="/jscript/jquery-ui.js"></SCRIPT>
	<SCRIPT type="text/javascript" SRC="/jscript/jquery.ui.datepicker-ru.js"></SCRIPT>
	<link rel="stylesheet" type="text/css" href="/css/backstyle.css">
	<link rel="stylesheet" type="text/css" href="/css/loginstyle.css">
</HEAD>
<BODY>
<TABLE style="height:70px;width:100%;border-spacing:0px;">
<TR style="background-color:#FFFFFF; height:40px;">
	<TD style="border:0px;"></TD>
	<TD style="width:200px;vertical-align:middle;text-align:left;border:0px;">
&nbsp;
	</TD>
</TR>
<TR style="background-color: #00CC00;height:30px;">
	<TD colspan=2>&nbsp;</TD>
	</TR>
</TABLE>

<DIV class="errorlist_header">Первый этап регистрации закончен. Создана учётная запись с именем и паролем, которые были указаны форме регистрации.<br>
<br>
Запомните ваше имя пользователя: <b><?=$username;?></b><br>и, особенно, пароль: <b><?=$pass;?></b><br><br>
<SPAN style="color:red;font-size:12px;">
<b>Через некоторое время проверьте Ваш почтовый ящик, который Вы указали при регистрации. В присланном письме Вы найдёте ссылку, по которой сможете активировать учётную запись и приступить к размещению Вашего предложения.</b>
</SPAN>
<br><br>
К сожалению, стандартная форма письма о регистрации на сайтах очень часто приводит к срабатыванию фильтров спама, поэтому проверьте также папку спама или нежелательной почты.<br><br>
Для восстановления пароля Вы всегда можете воспользоваться ссылкой на странице авторизации. Новый код активации будет выслан на адрес электронной почты использованный вами при регистрации.<br><br>
Спасибо за уделённое нам время.<br><br>При возникновении вопросов обращайтесь: <a href="mailto:support@lazarevka-otpusk.ru">support@lazarevka-otpusk.ru</a>
</DIV>
<DIV class="errorlist"><?=$errors;?></DIV>
<DIV id="announcer"></DIV>
</BODY>
</HTML>