<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
	<TITLE> Административная консоль сайта </TITLE>
	<META NAME="Generator" CONTENT="EditPlus">
	<META NAME="Author" CONTENT="">
	<META NAME="Keywords" CONTENT="">
	<META NAME="Description" CONTENT="">
	<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">
	<SCRIPT type="text/javascript" SRC="/jscript/jquery.js"></SCRIPT>
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

<table style="width:100%;">
<tr>
	<td style="width:10%;height:400px;vertical-align:top;">&nbsp;</td>
	<td style="border:1px solid #000000;">

<DIV class="fcontainer">
<FORM METHOD=POST ACTION="/login/rpass/run">
	<FIELDSET id="form2">
		<LEGEND>Восстановление пароля</LEGEND>
		
		<LABEL TITLE="Введите адрес электронной почты, куда будет направлено письмо для восстановления пароля">Адрес e-mail:</LABEL>
		<INPUT TYPE="text" ID="email" NAME="email" value="<?=$this->input->post('email',TRUE);?>">

		<LABEL TITLE="Всего лишь одна маленькая проверка на человечность">Введите символы с картинки:<SPAN style="color:red">*</SPAN></LABEL>
		<INPUT TYPE="text" ID="cpt" NAME="cpt">
		
		<IMG SRC="/<?=$captcha;?>" width="100" height="50" border="1" alt="captcha" style="margin-left:150px;border:1px solid #000000;">
		
		<CENTER><INPUT TYPE="submit" style="width:250px;" value="Выслать новый код авторизации!"></CENTER>

		<DIV CLASS="relink">Хотите попробовать ввести пароль и продолжить работу?<br> 
			<SPAN CLASS="pseudolink" id="link2" onclick="window.location = '/login/index/auth'">Тогда вам сюда</SPAN>
		</DIV>
		<?=$errorlist;?>
	</FIELDSET>
</FORM>
</DIV>
<p>Введите адрес электронной почты, который был использован для регистрации. В отправленном письме Вам будет предложено зайти на сайт по одноразовой ссылке на страницу, где Вы сможете сменить пароль и продолжить работу.</p>

</td>
	<td style="width:10%;">&nbsp;</td>
</tr>
</table>

<DIV id="announcer"></DIV>
</BODY>
</HTML>