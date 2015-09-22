<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Менеджер геоточек</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">
<meta name="keywords" content="<?=$keywords;?>">
	<script type="text/javascript" src="/jscript/jquery.js"></script>
	<script type="text/javascript" src="/jscript/jquery-ui.js"></script>
	<script type="text/javascript" src="/jscript/mechanization.js"></script>
	<script type="text/javascript" src="http://api-maps.yandex.ru/1.1/index.xml?key=<?=$yandex_key;?>"></script>
	<script type="text/javascript" src="/jscript/map_styles.js"></script>
	<script type="text/javascript" src="/jscript/maps_frontend2.js"></script>
	<link href="/css/frontstyle.css" rel="stylesheet" media="screen" type="text/css">

	<script type="text/javascript" src="/js/prettify.js"></script>                                   <!-- PRETTIFY -->
	<script type="text/javascript" src="/js/kickstart.js"></script>                                  <!-- KICKSTART -->
	<link rel="stylesheet" type="text/css" href="/css/kickstart.css" media="all">                  <!-- KICKSTART -->
	<link rel="stylesheet" type="text/css" href="/css/style.css" media="all">                          <!-- CUSTOM STYLES -->
	<SCRIPT TYPE="text/javascript">
	</SCRIPT>
		<STYLE TYPE="text/css">
		#SearchTableS{ /*вторая колонка*/
			padding:0px;
			vertical-align:top;
			width:200px;
			height:600px;
		}
		#SearchTable{ /*сводка по условиям и результатам поиска*/
			vertical-align:top;
			width:100%;
			
		}
		#img1, #img2{
			border:none;
			display:none;
			margin-top:3px;
		}
		#map_sw{
			width:27px;
			height:27px; 
			border:none;
			position:absolute;
			top:5px;
			left:100px;
			z-index:4;
			cursor:pointer;
			display:none;
		}
		#map_head, #map_load{
			border:none;
			position:absolute;
			top:5px;
			left:390px;
			z-index:4;
			cursor:default;
			font-size: 10pt;
			color: #FFFFCC;
		}
		#geoQuery{
			z-index:4;
			top:5px;
			left:130px;
			position:absolute;
			width: 200px;
		}
		#gQInit{
			z-index:4;
			top:5px;
			left:333px;
			position:absolute;
		}
		#map_load{
			top:5px;
			left:570px;
			display:none;
		}
	</STYLE>
</head>
<body bgcolor="#FFFFFF" onload="display_prime();">

<?=$menu;?>
<table class="main_page_body" id="main_table">
	<tr>
		<td id="SearchTableS">
			<table id="SearchTable">
				<tr>
					<td class="SearchColHeader" ID="SearchColHead">&laquo;&nbsp;Свернуть</td>
				</tr>
				<tr>
					<td class="SearchHeader" ID="CondHead">
						<SPAN ID="CondHead1">Условия поиска - </SPAN>
						<SPAN ID="CondHead2">+</SPAN>
						<IMG SRC="/images/scond.png" WIDTH="8" HEIGHT="90" ALT="" id="img1">
					</td>
				</tr>
				<tr>
					<td ID="CondBody">Список условий поиска</td>
				</tr>
				<tr>
					<td class="SearchHeader" ID="ResultHead">
						<SPAN ID="ResultHead1">Результаты поиска - </SPAN>
						<SPAN ID="ResultHead2">+</SPAN>
						<IMG SRC="/images/sresult.png" WIDTH="10" HEIGHT="114" ALT="" id="img2">
					</td>
				</tr>
				<tr>
					<td style="padding:0px;">
					<div id="ResultBody"></div>
					</td>
				</tr>
			</table>

		</td>
		<td id="monitor"><!-- сам текст -->
			<ul class="tabs left" style="margin-top:5px;">
				<li><a href="#text">Описание</a></li>
				<li><a href="#YMapsID">На карте</a></li>
				<li><a href="#text" onclick="window.location = '/page/to_notes/<?=$location_id;?>';">Добавить в блокнот</a></li>
			</ul>

			<DIV ID="text" class="tab-content">
				<?=$content;?>
			</DIV>
			<DIV ID="YMapsID" class="tab-content">
				<button class="small green" id="map_sw" title="Скрыть / показать селектор объектов"><span class="icon small" data-icon="v"></span></button>
				<button class="small green" id="map_head"><span class="icon small" data-icon="I"></span><?=$map_header;?></button>
				<input type="text" id="geoQuery" value="<?=$this->config->item("maps_def_loc");?> ">
				<button type="button" id="gQInit" class="small orange"  title="Поиск по адресу">Найти</button>
				<DIV id="map_load"><img src="/images/loading.gif" width="16" height="16" alt="загрузка">Идёт загрузка данных</DIV>
				<?=$selector;?>
			</DIV>
		</td>
	</tr>
	<tr style="height:70px;">
		<td>
		<form method=post action="">
			<input type="hidden" name="map_center" id="map_center" value="<?=$maps_center;?>">
			<input type="hidden" name="current_zoom" id="current_zoom" value=15>
			<input type="hidden" name="current_type" id="current_type" value="Схема">
			<input type="hidden" name="mapset" id="mapset" value=<?=$mapset;?>>
			<input type="hidden" name="type_strict" id="type_strict" value=<?=$type_strict;?>>
			<input type="hidden" name="location_id" id="location_id" value=<?=$location_id;?>>
			<input type="hidden" name="lastsearch" id="lastsearch" value="<?=$lastsearch;?>">
		</form>
		</td>
	</tr>
</table>
<DIV style="display:none;"><?=$links_heap;?></DIV>
<SCRIPT TYPE="text/javascript">
	<!--
	var height = $(window).height();
	$("#YMapsID").css("height",(height - 80) + "px");

	$('#text_tab').css('background-color','white');
	$('#text_tab').click(function() {
		$('#YMapsID').fadeOut('fast', function() {
			$('#map_tab').css('background-color','#F2F2F2');
			$('#text_tab').css('background-color','white');
			$('#text').fadeIn('fast', function() {});
		});
	});
	$('#map_tab').click(function() {
		$('#text').fadeOut('slow', function() {
			$('#text_tab').css('background-color','#F2F2F2');
			$('#map_tab').css('background-color','white');
			$('#YMapsID').fadeIn('fast', function() {});
			$('#SearchTableS').fadeIn('fast', function() {});
		});
	});


	$(window).load(function () {
		$('#SContainer').delay(2000).fadeIn(1500, function() {});
		$('#map_sw').delay(2000).fadeIn(1500, function() {});
	});

	$('.grouplabel').click(function() {
		var id = this.id.split('_')[1];
		$('#gc_' + id).slideToggle('slow', function() {});
	});
	$('.grouplabel').mouseenter(function() {
		$(this).css('background-color', '#993333');
		$(this).css('color', '#FFFFFF');
	});
	$('.grouplabel').mouseleave(function() {
		$(this).css('background-color', '#DDDDDD');
		$(this).css('color', 'black');
	});
	$('#notes_count').mouseenter(function() {
		$(this).css('text-decoration', 'underline');
		$(this).css('color', '#FF0000');
	});
	$('#notes_count').mouseleave(function() {
		$(this).css('text-decoration', 'underline');
		$(this).css('color', '000099');
	});

	$('.locations_numbers td').mouseenter(function() {
		$(this).css('background-color', '#FFFFFF');
	});
	$('.locations_numbers td').mouseleave(function() {
		$(this).css('background-color', '#F4F4F4');
	});


	$('.itemcontainer').mouseenter(function() {
		$(this).css('background-color', '#993333');
		$(this).css('color', '#FFFFFF');
	});
	$('.itemcontainer').mouseleave(function() {
		$(this).css('background-color', '#D0D0D0');
		$(this).css('color', 'black');
	});
	$('.SearchHeader').mouseenter(function() {
		$(this).css('background-color', '#E9E9E9');
	});
	$('.SearchHeader').mouseleave(function() {
		$(this).css('background-color', '#E0E0E0');
	});

	$('#SearchColHead').click(function() {
		if($('#SearchTable').innerWidth() > 30){
			$('#CondBody, #ResultBody, #CondHead1, #ResultHead1').fadeOut(100, function() {
				document.getElementById('SearchColHead').innerHTML = "&raquo;";
				$('#SearchTable').animate({width: 25}, 100, function() {
					$('#SearchTableS').animate({width: 25}, 100, function() {
						$('#CondHead, #ResultHead').animate({height: 150}, 100, function() {
							$('#img1, #img2').fadeIn(200, function() {});
						});
					});
				});
			});
		}else{
			$('#img1, #img2').fadeOut('slow', function() {
				$('#CondHead, #ResultHead').animate({height: 25}, 100, function(){
					$('#SearchTableS').animate({width: 170}, 100, function() {
						$('#SearchTable').animate({width: 170}, 100, function(){
							document.getElementById('SearchColHead').innerHTML = "&laquo; Свернуть";
							$('#CondHead1, #ResultHead1').fadeIn(100, function() {
								$('#CondBody, #ResultBody').fadeIn(100, function() {});
							});
						});
					});
				});
			});
		}
	});

	$(function() {
		$( "#SContainer" ).draggable({containment: "#YMapsID", scroll: false, handle: "#YMHead" });
	});

	$('#YMHead').dblclick(function() {
		if($('#mainselector').css('display') == 'block'){
			$('#mainselector').css('display', 'none');
			$('#SContainer').css('height', '20');
		}else{
			$('#mainselector').css('display', 'block');
			$('#SContainer').css('height', '270');
		}
	});
	$('#map_sw').click(function() {
		if($('#SContainer').css('display') == 'block'){
			$('#SContainer').fadeOut('slow', function() {
				$('#map_sw').attr("src",'/images/searchbar.png');
			});
			
		}else{
			$('#SContainer').fadeIn('slow', function() {
				$('#map_sw').attr("src",'/images/searchbar_a.png');
			});
		}
	});
	$('#notepad,#notepad_text,#notepad_text a').mouseenter(function() {
		$(this).css('color', '#FF0000');
	});
	$('#notepad,#notepad_text,#notepad_text a').mouseleave(function() {
		$(this).css('color', '000099');
	});
	//-->
</SCRIPT>

<?=$footer;?>
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