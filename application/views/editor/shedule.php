<table class="table table-condensed table-bordered table-striped" id="sheduleTable">
<tr>
	<th class="lc">День недели</th>
	<th class="rc">Режим работы</th>
</tr>
<tr>
	<th>Вс.</th>
	<td>
		<span title="Рабочие часы">
			<i class="icon-time"></i>
			<input type="text" day="0" id="ds0" class="time" placeholder="00:00" maxlength=5>-<input type="text" day="0" id="bs0" class="time" placeholder="00:00" maxlength=5>
		</span>&nbsp;&nbsp;&nbsp;&nbsp;
		<span title="Перерыв">
			<i class="icon-glass"></i>
			<input type="text" day="0" id="be0" class="time" placeholder="00:00" maxlength=5>-<input type="text" day="0" id="de0" class="time" placeholder="00:00" maxlength=5>
		</span>
	</td>
</tr>
<tr>
	<th>Пн.</th>
	<td>
		<span title="Рабочие часы">
			<i class="icon-time"></i>
			<input type="text" day="1" id="ds1" class="time" placeholder="00:00" maxlength=5>-<input type="text" day="1" id="bs1" class="time" placeholder="00:00" maxlength=5>
		</span>&nbsp;&nbsp;&nbsp;&nbsp;
		<span title="Перерыв">
			<i class="icon-glass"></i>
			<input type="text" day="1" id="be1" class="time" placeholder="00:00" maxlength=5>-<input type="text" day="1" id="de1" class="time" placeholder="00:00" maxlength=5>
		</span>
	</td>
</tr>
<tr>
	<th>Вт.</th>
	<td>
		<span title="Рабочие часы">
			<i class="icon-time"></i>
			<input type="text" day="2" id="ds2" class="time" placeholder="00:00" maxlength=5>-<input type="text" day="2" id="bs2" class="time" placeholder="00:00" maxlength=5>
		</span>&nbsp;&nbsp;&nbsp;&nbsp;
		<span title="Перерыв">
			<i class="icon-glass"></i>
			<input type="text" day="2" id="be2" class="time" placeholder="00:00" maxlength=5>-<input type="text" day="2" id="de2" class="time" placeholder="00:00" maxlength=5>
		</span>
	</td>
</tr>
<tr>
	<th>Ср.</th>
	<td>
		<span title="Рабочие часы">
			<i class="icon-time"></i>
			<input type="text" day="3" id="ds3" class="time" placeholder="00:00" maxlength=5>-<input type="text" day="3" id="bs3" class="time" placeholder="00:00" maxlength=5>
		</span>&nbsp;&nbsp;&nbsp;&nbsp;
		<span title="Перерыв">
			<i class="icon-glass"></i>
			<input type="text" day="3" id="be3" class="time" placeholder="00:00" maxlength=5>-<input type="text" day="3" id="de3" class="time" placeholder="00:00" maxlength=5>
		</span>
	</td>
</tr>
<tr>
	<th>Чт.</th>
	<td>
		<span title="Рабочие часы">
			<i class="icon-time"></i>
			<input type="text" day="4" id="ds4" class="time" placeholder="00:00" maxlength=5>-<input type="text" day="4" id="bs4" class="time" placeholder="00:00" maxlength=5>
		</span>&nbsp;&nbsp;&nbsp;&nbsp;
		<span title="Перерыв">
			<i class="icon-glass"></i>
			<input type="text" day="4" id="be4" class="time" placeholder="00:00" maxlength=5>-<input type="text" day="4" id="de4" class="time" placeholder="00:00" maxlength=5>
		</span>
	</td>
</tr>
<tr>
	<th>Пт.</th>
	<td>
		<span title="Рабочие часы">
			<i class="icon-time"></i>
			<input type="text" day="5" id="ds5" class="time" placeholder="00:00" maxlength=5>-<input type="text" day="5" id="bs5" class="time" placeholder="00:00" maxlength=5>
		</span>&nbsp;&nbsp;&nbsp;&nbsp;
		<span title="Перерыв">
			<i class="icon-glass"></i>
			<input type="text" day="5" id="be5" class="time" placeholder="00:00" maxlength=5>-<input type="text" day="5" id="de5" class="time" placeholder="00:00" maxlength=5>
		</span>
	</td>
</tr>
<tr>
	<th>Сб.</th>
	<td>
		<span title="Рабочие часы">
			<i class="icon-time"></i>
			<input type="text" day="6" id="ds6" class="time" placeholder="00:00" maxlength=5>-<input type="text" day="6" id="bs6" class="time" placeholder="00:00" maxlength=5>
		</span>&nbsp;&nbsp;&nbsp;&nbsp;
		<span title="Перерыв">
			<i class="icon-glass"></i>
			<input type="text" day="6" id="be6" class="time" placeholder="00:00" maxlength=5>-<input type="text" day="6" id="de6" class="time" placeholder="00:00" maxlength=5>
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
		if ($(this).val().length === 2) {
			$(this).val($(this).val() + ":");
		}
		mins = $(this).val().split(":")[1];
		if ( mins !== undefined && mins.length ){
			if (parseInt(mins + srcstr, 10) > 59){
				$(this).val($(this).val().split(":")[0] + ":59");

			}
		}
		//alert($(this).val() + string.substr(0, (string.length - $(this).val().length)))
	});
//-->
</script>