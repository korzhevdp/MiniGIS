<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>Лазаревское разработка</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">
	<SCRIPT type="text/javascript" SRC="<?=base_url();?>jscript/jquery.js"></SCRIPT>
	<SCRIPT type="text/javascript" SRC="<?=base_url();?>jscript/jquery-ui.js"></SCRIPT>
	<SCRIPT type="text/javascript" SRC="<?=base_url();?>jscript/mechanization.js"></SCRIPT>
	<SCRIPT type="text/javascript" src="http://api-maps.yandex.ru/1.1/index.xml?key=<?=$yandex_key;?>"></SCRIPT>
	<SCRIPT type="text/javascript" src="<?=base_url();?>jscript/maps_frontend.js"></SCRIPT>
	<LINK HREF="<?=base_url();?>css/frontstyle.css" REL="stylesheet" MEDIA="screen" TYPE="text/css">
	<SCRIPT TYPE="text/javascript">
	<!--
		var base_url = "<?=base_url();?>";
		var location_id = "<?=$location_id?>";
		var commonuser = "<?=$userid;?>";
		var lastsearch = "<?=$lastsearch;?>";
	//-->
	</SCRIPT>
</head>
<body bgcolor="#FFFFFF" onload="display_prime();">

<table id="Table_01" width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td><img src="<?=base_url();?>images/design1_01.gif" width="191" height="87" alt=""></td>
		<td><img src="<?=base_url();?>images/design1_02.gif" width="49" height="87" alt=""></td>
		<td><img src="<?=base_url();?>images/design1_03.gif" width="274" height="87" alt=""></td>
		<td colspan="5" width="100%" style="background-color: #EDEDED;">Адрес для писем:<BR>г. Москва, Староколпакский переулок, стр. 6 оф. 204</td>
	</tr>
</table>

<table id="Table_02" width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td style="height:80%;vertical-align:top;width:150px;"><!-- навигацыя -->
			<?=$menu;?>
			<DIV class="owner_entrance" onclick="window.location = '<?=site_url();?>/admin'">вход для владельцев</DIV>
		</td>

		<td style="vertical-align:top;width:170px;" id="SearchTableS">

			<table style="vertical-align:top;width:100%;" id="SearchTable">
				<tr>
					<td class="SearchColHeader" ID="SearchColHead">&laquo; Свернуть</td>
				</tr>
				<tr>
					<td class="SearchHeader" ID="CondHead">
						<SPAN ID="CondHead1">Условия поиска - </SPAN><SPAN ID="CondHead2">+</SPAN>
						<IMG SRC="<?=base_url();?>images/scond.png" WIDTH="8" HEIGHT="90" BORDER="0" ALT="" style="display:none;margin-top:3px;" id="img1">
					</td>
				</tr>
				<tr>
					<td class="SearchBody" ID="CondBody">Список условий поиска </td>
				</tr>
				<tr>
					<td class="SearchHeader" ID="ResultHead">
						<SPAN ID="ResultHead1">Результаты поиска - </SPAN><SPAN ID="ResultHead2">+</SPAN>
						<IMG SRC="<?=base_url();?>images/sresult.png" WIDTH="10" HEIGHT="114" BORDER="0" ALT="" style="display:none;margin-top:3px;" id="img2">
					</td>
				</tr>
				<tr>
					<td class="SearchBody" ID="ResultBody">Список результатов поиска</td>
				</tr>
			</table>

		</td>
		<td colspan="6" class="cell2" id="monitor" style="vertical-align: top;"><!-- сам текст -->
			<table width="100%">
				<tr>
					<td>&nbsp;</td>
					<td ID="map_tab" class="switchtabs" >К карте</td>
					<td ID="text_tab" class="switchtabs">К тексту</td>
				</tr>
			</table>
			<DIV ID="YMapsID"><?=$selector;?></DIV>
			<DIV ID="text"><?=$content;?></DIV>
		</td>
	</tr>
</table>
<SCRIPT TYPE="text/javascript">
	<!--
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
		if($('#SearchTable').css('width')== "170px"){
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
		if($('#SContainer').css('height') != '20px'){
			$('#mainselector').css('display', 'none');
			$('#SContainer').css('height', '20');
		}else{
			$('#mainselector').css('display', 'block');
			$('#SContainer').css('height', '270');
		}
	});

	//-->
</SCRIPT>

<div class="owner_entrance"><br />Создавалась: {elapsed_time} с.</div>
<form method=post action="">
	<input type="hidden" name="current_zoom" id="current_zoom" value=15>
	<input type="hidden" name="current_type" id="current_type" value="Схема">
</form>
</body>
</html>