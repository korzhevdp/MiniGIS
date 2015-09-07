<!doctype html>
<html lang="en">
<head>
	<title>Административная консоль сайта</title>
	<meta http-equiv="content-type" content="text/html; charset=utf-8">
	<script type="text/javascript" src="<?=$this->config->item('api');?>/jscript/jquery.js"></script>
	<script type="text/javascript" src="<?=$this->config->item('api');?>/bootstrap/js/bootstrap.js"></script>
	<link href="<?=$this->config->item('api');?>/bootstrap/css/bootstrap.css" rel="stylesheet">
	<link href="<?=$this->config->item('api');?>/jqueryui/css/jqueryui.css" rel="stylesheet">
	<link href="<?=$this->config->item('api');?>/css/admin.css" rel="stylesheet">
</head>

<body>
<table class="navTable">
	<tr>
		<td colspan=2 class="navbar navbar-inverse">
			<div class="navbar-inner">
				<div class="container">
					<a class="brand" href="/"><img src="<?=$this->config->item('api');?>/images/minigis24.png" alt="">Home</a>
					<?=$this->load->view('cache/menus/menu',array(),TRUE).$this->usefulmodel->rent_menu().$this->usefulmodel->admin_menu();?>
				</div>
			</div>
		</td>
	</tr>
	<tr>
		<td class="well well-small menu_col">
			<ul class="nav nav-list" id="operations_menu">
				<?=$menu;?>
			</ul>
			<!--Sidebar content-->
		</td>
		<td class="content"><?=$content;?></td>
	</tr>
</table>

<div id="announcer"></div>
<script type="text/javascript">
<!--
	$("#operations_menu").height($(window).height() - 70 + 'px').css("margin-left","0px");
//-->
</script>
<!-- 
<SCRIPT TYPE="text/javascript">
	$(".info_table_innerdiv").css("width", "708px");
	$(".selector").datepicker($.datepicker.regional['ru']);
	$(".selector").datepicker( "option", "showWeek", true );
	$(".selector").datepicker( "option", "minDate", new Date());
	$(".selector").datepicker( "option", "gotoCurrent", true );

</SCRIPT> -->
</body>
</html>