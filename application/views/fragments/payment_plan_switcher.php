<table id="section6_3" style="margin-top:20px;">
	<tr style="height: 20px;">
		<td class="adm_cell_1st"><label for="payment_plan">÷еновые периоды дл€ нанимателей жиль€</label></td>
		<td class="adm_cell_2nd" style="vertical-align:middle;">
			<SELECT NAME="payment_plan" ID="payment_plan" onchange="show_payment_plan(this.value);" CLASS="doublewidth" title="¬ыберите сетку цен. ќна будет действовать дл€ всех подчинЄнных размещений">
				<OPTION VALUE=0 SELECTED>-- выберите сетку цен --</OPTION>
				<OPTION VALUE=1>по мес€цам</OPTION>
				<OPTION VALUE=2>по 2 недели</OPTION>
				<OPTION VALUE=3>декадный</OPTION>
				<!-- <OPTION VALUE=4>собственный</OPTION> -->
			</SELECT>
		</td>
	</tr>
	<?if($has_child) :?>
	<tr style="height: 20px;">
		<td class="adm_cell_1st"><label for="frm_pp_traverse">ѕрименить дл€ всех номеров/комнат</label></td>
		<td class="adm_cell_2nd" style="vertical-align:middle;">
			<input type="checkbox" class="checkbox" name="frm_pp_traverse" id="frm_pp_traverse" style="vertical-align: middle;">&nbsp;&nbsp;«аполн€ть таблицы цен в соответствии с используемой здесь
		</td>
	</tr>
	<? endif ?>
</table>
