<div style="margin-left:20px;">
	<h4>MiniGIS</h4>
<p>ein Framework für die Erstellung und Visualisierung von Informationen mit Hilfe der Mapping-Dienste.</p>
<p>Implementiert: <ul>
<li>Speicher karteninformation mit verzweigten Schema der Klassifikation der Daten</li>
<li>Unterstützung für Fliesen von verschiedenen Anbietern Kartenbilder, einschließlich der eigenen Karten-Daten</li>
<li>Schnittstelle für die Verbindung ergänzenden grundlegende Funktionalität Handler Daten</li>
<li>back-end content-Management-System ermöglicht die vor-und aufbereiten von Daten</li>
<li>eine Anzahl von Funktionen für die Verarbeitung der Eingabedaten "on-the-Fly"</li>
<li>Format Transport von Dateien zwischen Komponenten-System</li>
<li>Schnittstelle freien Eingabe von Daten mit фронтенда</li>
<li>разнотипные Mechanismen der Export von Daten in Web-Sites und Web-Anwendungen</li>
	</ul></p>
</div>


<div class="well well-small" style="width:33%;margin-left:20px;">
	Gemacht und hochgeladen Karten: <? require "./mpct.txt"; ?>
	<h3>Moralisch unterstützen. <small>Klicken.</small></h3>
	<button id="moraleup" style="margin-left:0px;" class="btn btn-primary btn-block"><i class="icon-thumbs-up icon-white"></i>&nbsp;&nbsp;Bereits unterstützt - <span id="moralecounter"><?require "./morale.txt";?></span></button><br><br<br>

	<!-- <h3>Unterstützen Projekt</h3>
	Schreiben Sie uns eine E-Mail wie folgt: "Jungs, wir müssen uns hier ein Stück auf der Karte, wie Ihr/.../ Tun können?"<br><br> -->

	<h4>Standard unterstützen</h4>
	Schreiben Sie Koordinator<a href="mailto:korzhevdp@gmail.com?subject=Wir wollen Sie unterstützen Standard:&Body=Liebe Entwickler!">E-Mail mit einer kurzen Beschreibung dessen, was Sie möchten, uns zu unterstützen</a>.<br><br>

	<h4>Zu unterstützen, die über den Tellerrand hinaus</h4>
	Schreiben Sie Koordinator<a href="mailto:korzhevdp@gmail.com?subject=Wir wollen Sie unterstützen, über den Tellerrand hinaus:&Body=Liebe Entwickler!">E-Mail mit einer kurzen Beschreibung, wie Sie wollen uns über den Tellerrand hinaus zu unterstützen</a>.<br><br>
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
				alert("Nichts gefunden");
			}
		});
	});
//-->
</script>

