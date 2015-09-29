<div style="margin-left:20px;">
	<h4>MiniGIS</h4>
	<p>is the essential framework designed for data preparation, processing and visualization when using web-side map services.</p>
	<p>Yet implemented: <ul>
	<li>A storage for map contents with an extensive data classification and scheme</li>
	<li>A support for tiles from various providers of cartographic images</li>
	<li>A possibility of connecting modules which extend the main functionality</li>
	<li>A back-end CMS allowing you to set and process the data</li>
	<li>A numerous functions to handle input information on-the-fly</li>
	<li>A file format for transferring data between system components and side-projects</li>
	<li>An interface for the freehand map drawing from frontend, with storing intermediate anonimous data on server and multilevel access to it</li>
	<li>A bunch of mechanisms for exporting data to websites and web-applications</li>
	</ul></p>
	<br><br><br>Nile crocodile is sleeping for a while...<br><br>
</div>



<div class="well well-small" style="width:33%;margin-left:20px;">
	Maps drawed and downloaded:<? require "./mpct.txt"; ?>
	<h3>Cheer us up!&nbsp;&nbsp;&nbsp;&nbsp;<small>just do click.</small></h3>
	<button id="moraleup" style="margin-left:0px;" class="btn btn-primary btn-block"><i class="icon-thumbs-up icon-white"></i>&nbsp;&nbsp;Have already cheered for - <span id="moralecounter"><?require "./morale.txt";?></span>&nbsp;times</button><br>

	<!-- <h3>Поддержать проектно</h3>
	Напишите нам письмо, приблизительно такого содержания: "Парни, нам нужно вот такую штуку на карте, похожей на вашу /.../ Можете сделать?"<br><br> -->

	<h4>Way to support standard</h4>
	Email the coordinator with <a href="mailto:korzhevdp@gmail.com?subject=I wish to support you:&Body=Dear developer!">a letter and a brief description of how you able to support us</a>.

	<h4>An uncommon way to support</h4>
	Feel free to email the coordinator <a href="mailto:korzhevdp@gmail.com?subject=I wish to support you uncommonly:&Body=Dear developer!">a letter with a brief offering the uncommon aid in our hard work</a>.<br><br>
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
			success: function(data){ // and if you are lucky and sane response is received
				$("#moralecounter").empty().html(data);
			},
			error: function(a,b){
				alert("Nothing found");
			}
		});
	});
//-->
</script>

