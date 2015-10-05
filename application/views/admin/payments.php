<h4>Состояние платежей</h4>
<table id="payTable" class="table table-bordered table-condensed table-hover table-striped">
<tr>
	<th class="name">Название</th>
	<th class="type">Тип</th>
	<th>Адрес</th>
	<th class="author">Автор</th>
	<th>Контакты</th>
	<th class="paidtill">Оплачено до</th>
	<th class="last">Save</th>
</tr>
<tr>
	<form method=post action="/user/paydata">
		<td>&nbsp;</td>
		<td>
			<select name="byType">
			<option value="0">Все типы объектов</option>
			<?=$types;?>
			</select>
		</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td><label class="checkbox"><input type="checkbox" name="paid" title="Только оплаченные"<?=$checked;?>>Оплаченные</label></td>
		<td><button type="submit">Показать</button></td>
	</form>
</tr>
<?=$table;?>
</table>
<script type="text/javascript" src="<?=$this->config->item('api');?>/jqueryui/js/jqueryui.js"></script>
<script type="text/javascript" src="<?=$this->config->item('api');?>/jscript/datepicker.js"></script>
<script>
$(function() {
	$( ".datepicker" ).datepicker();
});
$(".savePaidStatus").click(function(){
	var ref  = $(this).attr('ref')
		date = $('#d' + ref).val();
	$.ajax({
		url: "/user/set_payment",
		data: {
			location : ref,
			paidtill : date
		},
		type: "POST",
		dataType: 'script',
		success: function () {
			$(".savePaidStatus[ref=" + ref + "]").addClass("btn-success");
		},
		error: function (data, stat, err) {
			console.log([ data, stat, err].join("\n"));
		}
	});
});
</script>
