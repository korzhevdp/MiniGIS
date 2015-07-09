<?=$text1;?>
<?=$special_offer;?>
<div style="margin-left:20px;">
	<h4>MiniGIS</h4>
	<p>Фреймворк для подготовки и визуализации информации с использованием картографических сервисов.</p>
	<p>Реализованы: <ul>
		<li>хранилище картографической информации с разветвлённой схемой классификации данных</li>
		<li>поддержка тайлов различных провайдеров картографических изображений, в том числе и собственных картографических данных</li>
		<li>интерфейс подключения дополняющих основной функционал обработчиков данных</li>
		<li>back-end система управления контентом позволяющая вносить и обрабатывать данные</li>
		<li>некоторое количество функций для обработки вводимой информации "на лету"</li>
		<li>формат транспортных файлов между компонентами системы</li>
		<li>интерфейс свободного ввода данных с фронтенда</li>
		<li>разнотипные механизмы экспорта данных в веб-сайты и веб-приложения</li>
	</ul></p>
</div>


<div class="well well-small" style="width:33%;margin-left:20px;">
	Сделано и загружено карт: <? require "./mpct.txt"; ?>
	<h3>Поддержать морально. <small>Щёлкните по кнопке.</small></h3>
	<button id="moraleup" style="margin-left:0px;" class="btn btn-primary btn-block"><i class="icon-thumbs-up icon-white"></i>&nbsp;&nbsp;Уже поддержали - <span id="moralecounter"><?require "./morale.txt";?></span></button><br><br<br>

	<!-- <h3>Поддержать проектно</h3>
	Напишите нам письмо, приблизительно такого содержания: "Парни, нам нужно вот такую штуку на карте, похожей на вашу /.../ Можете сделать?"<br><br> -->

	<h4>Поддержать стандартно</h4>
	Напишите координатору <a href="mailto:korzhevdp@gmail.com?subject=Хотим поддержать Вас стандартно:&Body=Уважаемый разработчик!">письмо с кратким описанием того, чем Вы хотите нас поддержать</a>.<br><br>

	<h4>Поддержать нестандартно</h4>
	Напишите координатору <a href="mailto:korzhevdp@gmail.com?subject=Хотим поддержать Вас нестандартно:&Body=Уважаемый разработчик!">письмо с кратким описанием того, как Вы хотите нас нестандартно поддержать</a>.<br><br>
	<!-- Place this tag in your head or just before your close body tag. -->
	<script type="text/javascript" src="https://apis.google.com/js/plusone.js">
	  {lang: 'ru'}
	</script>

	<!-- Place this tag where you want the +1 button to render. -->
	<div class="g-plusone" data-size="tall" data-annotation="none" style="clear:both"></div>
</div>


<script type="text/javascript">
<!--
	$('#moraleup').click(function () {
		$.ajax({
			url: '/ajaxutils/moraleup',
			type: "POST",
			dataType: "html",
			success: function(data){ // и если повезло и ответ получен вменяемый
				$("#moralecounter").empty().html(data);
			},
			error: function(a,b){
				alert("Ничего не найдено");
			}
		});
	});
//-->
</script>

