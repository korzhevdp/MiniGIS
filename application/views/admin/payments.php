<h4>Состояние платежей</h4>
<table id="payTable" class="table table-bordered table-condensed table-hover table-striped">
<tr>
	<th class="name">Название</th>
	<th class="type">Тип</th>
	<th>Адрес</th>
	<th class="author">Автор</th>
	<th>Контакты</th>
	<th title="Комментарии включены"><i class="icon-comment"></i></th>
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
		<td><input type="checkbox" name="comments" title="Только с включенными комментариями"<?=$commchecked;?>></td>
		<td><label><input type="checkbox" name="paid" title="Только оплаченные"<?=$paidchecked;?>></label></td>
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
	var ref  = $(this).attr('ref'),
		date = $('#d' + ref).val(),
		comm = $('#d' + ref).prop('checked');
	$.ajax({
		url: "/user/set_payment",
		data: {
			location : ref,
			paidtill : date,
			comments : comm
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
