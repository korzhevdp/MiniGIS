<!DOCTYPE html>
<html>
<head>
	<title>Менеджер ГеоТочек: <?=$title;?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">
	<meta name="keywords" content="<?=$keywords;?>">
	<meta name='yandex-verification' content='74872298f6a53977' />
	<meta name='loginza-verification' content='ecdaef934bf45473c2c6402eed886170' />
	<script src="<?=$this->config->item("api");?>/jscript/jquery.js" type="text/javascript"></script>
	<script src="<?=$this->config->item("api");?>/jqueryui/js/jqueryui.js" type="text/javascript"></script>
	<script src="<?=$this->config->item("api");?>/jscript/mechanization.js" type="text/javascript"></script>
	<link href="<?=$this->config->item("api");?>/bootstrap/css/bootstrap.css" rel="stylesheet">
	<link href="<?=$this->config->item("api");?>/jqueryui/css/jqueryui.css" rel="stylesheet">
	<link href="<?=$this->config->item("api");?>/css/frontstyle.css" rel="stylesheet" media="screen" type="text/css">
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
	<style type="text/css">
		#c_form * {
			font-size: 10pt;
		}
		#c_form label {
			width: 120px;
		}
	</style>
</head>

<body>
	<div class="navbar">
		<div class="navbar-inner">
			<a class="brand" href="http://www.korzhevdp.com">KORZHEVDP.COM</a>
			<ul class="nav">
				<li><a href="http://www.korzhevdp.com">Дом</a></li>
				<li class="active"><a href="http://maps.korzhevdp.com">Проекты</a></li>
				<li><a href="http://works.korzhevdp.com">Работы</a></li>
				<li><a href="http://flood.korzhevdp.com">Проза</a></li>
				<li><a href="http://rock.korzhevdp.com">Музыка</a></li>
			</ul>
		</div>
	</div>
	<!-- menu -->
	<div class="navbar navbar-inverse">
		<div class="navbar-inner">
			<a class="brand" href="http://maps.korzhevdp.com">ПРОЕКТ&nbsp;&nbsp;<small>Minigis.NET</small>&nbsp;&nbsp;<img src="<?=$this->config->item('api')?>/images/minigis24.png" style="width:24px;height:24px;border:none;margin-top:-6px;" alt=""></a>
			<?=$menu;?>
		</div>
	</div>
	<!-- menu -->

	<!-- content -->
		<?=$content;?>
	<!-- content -->	

	<div style="display:none;"><?=$links_heap;?></div>
	<?=$footer;?>
	<script type="text/javascript" src="//yandex.st/share/share.js" charset="utf-8"></script>
	<script type="text/javascript" src="<?=$this->config->item("api");?>/bootstrap/js/bootstrap.js"></script>
	<!-- Yandex.Metrika counter -->
	<script type="text/javascript">
	(function (d, w, c) {
		(w[c] = w[c] || []).push(function() {
			try {
				w.yaCounter24426704 = new Ya.Metrika({id:24426704,
						clickmap:true,
						trackLinks:true,
						accurateTrackBounce:true});
			} catch(e) { }
		});

		var n = d.getElementsByTagName("script")[0],
			s = d.createElement("script"),
			f = function () { n.parentNode.insertBefore(s, n); };
		s.type = "text/javascript";
		s.async = true;
		s.src = (d.location.protocol == "https:" ? "https:" : "http:") + "//mc.yandex.ru/metrika/watch.js";

		if (w.opera == "[object Opera]") {
			d.addEventListener("DOMContentLoaded", f, false);
		} else { f(); }
	})(document, window, "yandex_metrika_callbacks");
	</script>
	<noscript><div><img src="//mc.yandex.ru/watch/24426704" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
	<!-- /Yandex.Metrika counter -->
</body>
</html>