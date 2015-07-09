<!doctype html>
<html lang="en">
<head>
	<title>Административная консоль сайта</title>
	<meta http-equiv="content-type" content="text/html; charset=utf-8">
	<script type="text/javascript" src="<?=$this->config->item('api');?>/jscript/jquery.js"></script>
	<script type="text/javascript" src="<?=$this->config->item('api');?>/bootstrap/js/bootstrap.js"></script>
	<link href="<?=$this->config->item('api');?>/bootstrap/css/bootstrap.css" rel="stylesheet">
	<link href="<?=$this->config->item('api');?>/jqueryui/css/jqueryui.css" rel="stylesheet">
	<style type="text/css">
		body{
			padding:0px;
			margin:0px;
		}
		.navTable{
			border:none;
			width:100%
		}
		.navbar{
			vertical-align:top;
		}
		a.brand img{
			width:24px;
			height:24px;
			border:none;
		}
		td.menu_col{
			width:190px;
			vertical-align:top;
		}
		#operations_menu li{
			margin-left:0px;
			font-size:12px;
			line-height:16px;
		}
		td.content{
			vertical-align:top;
			padding-left:30px;
		}
		label.add-on{
			font-size:12px !important;
			display:inline-block !important;
			width:120px !important;
			text-align:left !important;
			text-indent:5px;
			margin-bottom:2px;

		}
		.input-prepend{
			margin-bottom:0px;
			line-height:16px;
		}
		.input-prepend input[type=checkbox]{
			margin-top: 9px;
			margin-left:20px;
		}
		.input-prepend input[type=text]{
			font-size:12px !important;
			height:26px;
			width: 345px;
			padding:1px 8px;
		}
		.input-prepend select{
			font-size:12px !important;
			line-height:16px;
			width: 364px;
			padding: 1px 8px;
		}
	</style>
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