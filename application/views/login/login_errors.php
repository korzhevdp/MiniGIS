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
	<link rel="stylesheet" type="text/css" href="/css/datepicker.css">
	<STYLE TYPE="text/css">
		body{
			margin:0px;
			padding:0px;
			font-size: 9pt;
			font-family: Tahoma;
		}
		.pseudolink{
			color: #0000FF;
			text-decoration: underline;
			cursor: pointer;
		}
		p{
			margin:10px;
		}
		label{
			display:block;
			float:left;
			width:150px;
			text-align:left;
			font-size: 9pt;
			margin-bottom:5px;
		}
		input{
			display:block;
			clear:right;
			width:150px;
			font-size: 9pt;
			margin-bottom:5px;
		}
		.form_container{
			width:100%;
			vertical-align:middle;
			border: 1px solid #000000;
			text-align: center;
		}
		legend{
			padding:5px;
		}
		fieldset{
			margin-left: 50px;
			width:350px;
			padding:5px;
		}
		.relink{
			width:100%;
			height:100px;
			font-size: 9pt;
			font-family: Tahoma;
			margin-top:40px;
		}
		.errorlist_header{
			font-size: 9pt;
			font-family: Tahoma;
			color:black;
		}
		.errorlist{
			font-size: 9pt;
			font-family: Tahoma;
			color:red;
		}
	</STYLE>

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

<DIV style="width:600px;margin-left:50px;">
	<DIV class="errorlist_header">В процессе активации пароля произошли следующие ошибки:</DIV>
	<DIV class="errorlist"><?=$errors;?></DIV><br>
	<?=$return;?>
</DIV>

<DIV id="announcer"></DIV>
</BODY>
</HTML>