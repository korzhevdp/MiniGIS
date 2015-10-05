<!DOCTYPE html>
<html>
<head>
	<title>Manager GeoPunkte: <?=$title;?></title>
	<meta name="keywords" content="<?=$keywords;?>">
	<meta name='yandex-verification' content='74872298f6a53977' />
	<meta name='loginza-verification' content='ecdaef934bf45473c2c6402eed886170' />
	<script src="<?=$this->config->item("api");?>/jscript/jquery.js" type="text/javascript"></script>
	<script type="text/javascript" src="<?=$this->config->item("api");?>/bootstrap/js/bootstrap.js"></script>
	<link href="<?=$this->config->item("api");?>/bootstrap/css/bootstrap.css" rel="stylesheet">
	<link href="<?=$this->config->item("api");?>/css/frontend.css" rel="stylesheet" media="screen" type="text/css">
	<script type="text/javascript">

	var _gaq = _gaq || [];
	_gaq.push(['_setAccount', 'UA-22629206-1']);
	_gaq.push(['_trackPageview']);

	(function() {
	var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
	ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
	var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	})();
	</script>
</head>

<body>
	<!-- menu -->
	<div class="navbar navbar-inverse">
		<div class="navbar-inner">
			<a class="brand" href="http://maps.korzhevdp.com">Das PROJEKT&nbsp;&nbsp;<small>Minigis.NET</small>&nbsp;&nbsp;<img src="<?=$this->config->item('api')?>/images/minigis24.png" style="width:24px;height:24px;border:none;margin-top:-6px;" alt=""></a>
			<?=$menu;?>
		</div>
	</div>
	<!-- menu -->

	<!-- content -->
		<?=$content;?>
	<!-- content -->
		<?=$comment;?>

	<?=$this->load->view($this->session->userdata('lang')."/frontend/modals");?>

	<div style="display:none;"><?=$links_heap;?></div>
	<?=$footer;?>

	<script type="text/javascript" src="<?=$this->config->item("api");?>/jscript/mapUI.js"></script>
</body>
</html>