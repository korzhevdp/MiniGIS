<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">
	<?=$graphs;?>
	google.load("visualization", "1", {packages:["corechart"]});
	google.setOnLoadCallback(drawChart);
	function drawChart() {
		for (a in mySrc){
			var data = google.visualization.arrayToDataTable(mySrc[a]);
			var options = {
				title: 'cost',
				chartArea: {
					left: 10,
					width: '95%'
				},
				curveType: 'function',
				height: 200,
				legend: {
					position: 'in'
				},
				backgroundColor: {
					fill: '#F0F0F0',
					stroke: '#404040'
				},
				lineWidth : 4,
				vAxis: {
					count: 6,
					textPosition : 'in'
				}
			};
			var chart = new google.visualization.LineChart(document.getElementById('chart_div' + a));
			chart.draw(data, options);
		}
	}

	$(".imgstripe").click(function(){
		$('#modal_pics').modal({show:0});
		$('#modal_pic').attr('src',$(this).attr('src').replace('/128/','/800/'));
	});
</script>

<div class="well span10">
	<h1><?=$name;?> <small><?=$location_name;?></small></h1>
	<dl class="dl-horizontal">
		<dt>Address</dt>
			<dd><address><?=$address;?><address></dd>
		<dt>Contacts</dt>
			<dd><?=$contact;?></dd>
	</dl>
<br><br>
	<div>
		<?=$content;?>
	</div>
	<div>
		<?=$rooms;?>
	</div>
</div>

<div class="well span2" style="float:left;clear:left;text-align:center">
		<img src="http://static-maps.yandex.ru/1.x/?ll=<?=$coord_y;?>&amp;size=128,128&amp;z=13&amp;l=map&amp;pt=<?=$coord_y;?>,pmwts&amp;key=<?=$yandex_key;?>" alt="" width=128 height=128 id="minimap">
		<?=(strlen($all_images)) ? $all_images :"";?>
</div>



<?=$this->load->view('frontend/frontend_modal_pic',array(),true);?>