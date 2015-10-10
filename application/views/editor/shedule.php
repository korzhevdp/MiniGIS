<table class="table table-condensed table-bordered table-striped" id="sheduleTable">
<tr>
	<th class="lc">День недели</th>
	<th class="rc">Режим работы</th>
</tr>
<tr class="info">
<tr>
	<th></th>
	<th><label><input type="checkbox" id="h24">Круглосуточно</label></th>
</tr>
<tr class="info">
	<th>Вс.</th>
	<td>
		<span title="Рабочие часы">
			<i class="icon-time"></i>
			<input type="text" day="0" id="ds0" class="time" placeholder="00:00" maxlength=5>-<input type="text" day="0" id="de0" class="time" placeholder="00:00" maxlength=5>
		</span>&nbsp;&nbsp;&nbsp;&nbsp;
		<span title="Перерыв">
			<i class="icon-glass"></i>
			<input type="text" day="0" id="bs0" class="time" placeholder="00:00" maxlength=5>-<input type="text" day="0" id="be0" class="time" placeholder="00:00" maxlength=5>
		</span>
		<span class="btn btn-mini btn-info copyToAll" ref="0" title="Копировать во все дни"><i class="icon-arrow-down icon-white"></i></span>
	</td>
</tr>
<tr>
	<th>Пн.</th>
	<td>
		<span title="Рабочие часы">
			<i class="icon-time"></i>
			<input type="text" day="1" id="ds1" class="time" placeholder="00:00" maxlength=5>-<input type="text" day="1" id="de1" class="time" placeholder="00:00" maxlength=5>
		</span>&nbsp;&nbsp;&nbsp;&nbsp;
		<span title="Перерыв">
			<i class="icon-glass"></i>
			<input type="text" day="1" id="bs1" class="time" placeholder="00:00" maxlength=5>-<input type="text" day="1" id="be1" class="time" placeholder="00:00" maxlength=5>
		</span>
	</td>
</tr>
<tr>
	<th>Вт.</th>
	<td>
		<span title="Рабочие часы">
			<i class="icon-time"></i>
			<input type="text" day="2" id="ds2" class="time" placeholder="00:00" maxlength=5>-<input type="text" day="2" id="de2" class="time" placeholder="00:00" maxlength=5>
		</span>&nbsp;&nbsp;&nbsp;&nbsp;
		<span title="Перерыв">
			<i class="icon-glass"></i>
			<input type="text" day="2" id="bs2" class="time" placeholder="00:00" maxlength=5>-<input type="text" day="2" id="be2" class="time" placeholder="00:00" maxlength=5>
		</span>
	</td>
</tr>
<tr>
	<th>Ср.</th>
	<td>
		<span title="Рабочие часы">
			<i class="icon-time"></i>
			<input type="text" day="3" id="ds3" class="time" placeholder="00:00" maxlength=5>-<input type="text" day="3" id="de3" class="time" placeholder="00:00" maxlength=5>
		</span>&nbsp;&nbsp;&nbsp;&nbsp;
		<span title="Перерыв">
			<i class="icon-glass"></i>
			<input type="text" day="3" id="bs3" class="time" placeholder="00:00" maxlength=5>-<input type="text" day="3" id="be3" class="time" placeholder="00:00" maxlength=5>
		</span>
	</td>
</tr>
<tr>
	<th>Чт.</th>
	<td>
		<span title="Рабочие часы">
			<i class="icon-time"></i>
			<input type="text" day="4" id="ds4" class="time" placeholder="00:00" maxlength=5>-<input type="text" day="4" id="de4" class="time" placeholder="00:00" maxlength=5>
		</span>&nbsp;&nbsp;&nbsp;&nbsp;
		<span title="Перерыв">
			<i class="icon-glass"></i>
			<input type="text" day="4" id="bs4" class="time" placeholder="00:00" maxlength=5>-<input type="text" day="4" id="be4" class="time" placeholder="00:00" maxlength=5>
		</span>
	</td>
</tr>
<tr>
	<th>Пт.</th>
	<td>
		<span title="Рабочие часы">
			<i class="icon-time"></i>
			<input type="text" day="5" id="ds5" class="time" placeholder="00:00" maxlength=5>-<input type="text" day="5" id="de5" class="time" placeholder="00:00" maxlength=5>
		</span>&nbsp;&nbsp;&nbsp;&nbsp;
		<span title="Перерыв">
			<i class="icon-glass"></i>
			<input type="text" day="5" id="bs5" class="time" placeholder="00:00" maxlength=5>-<input type="text" day="5" id="be5" class="time" placeholder="00:00" maxlength=5>
		</span>
	</td>
</tr>
<tr class="info">
	<th>Сб.</th>
	<td>
		<span title="Рабочие часы">
			<i class="icon-time"></i>
			<input type="text" day="6" id="ds6" class="time" placeholder="00:00" maxlength=5>-<input type="text" day="6" id="de6" class="time" placeholder="00:00" maxlength=5>
		</span>&nbsp;&nbsp;&nbsp;&nbsp;
		<span title="Перерыв">
			<i class="icon-glass"></i>
			<input type="text" day="6" id="bs6" class="time" placeholder="00:00" maxlength=5>-<input type="text" day="6" id="be6" class="time" placeholder="00:00" maxlength=5>
		</span>
	</td>
</tr>
</table>

<script type="text/javascript">
<!--
	$("input.time").keyup(function(){
		var string  = "0000",
			src_str = string.substr(0, (string.length - $(this).val().length)),
			mins,
			time;
		$(this).val($(this).val().replace(/[^0-9:]/, ""));
		if(parseInt($(this).val().replace(/:/, "") + src_str, 10) > 2359){
			$(this).val("23:59");
		}
		if ($(this).val().length > 2 && $(this).val().indexOf(":") === (-1)) {
			$(this).val($(this).val().substr(0, 2) + ":" + $(this).val().substr(2));
		}
		mins = $(this).val().split(":")[1];
		if ( mins !== undefined && mins.length ){
			if (parseInt(mins + src_str, 10) > 59){
				$(this).val($(this).val().split(":")[0] + ":59");

			}
		}
		//alert($(this).val() + string.substr(0, (string.length - $(this).val().length)))
	});

	$(".copyToAll").click(function(){
		var a,
			ref = $(this).attr('ref'),
			data = [ $("#ds" + ref).val(), $("#de" + ref).val(), $("#bs" + ref).val(), $("#be" + ref).val(), ];
		for (a = 0; a <= 6; a++) {
			$("#ds" + a).val(data[0]);
			$("#de" + a).val(data[1]);
			$("#bs" + a).val(data[2]);
			$("#be" + a).val(data[3]);
		}
	});
//-->
</script>