<table id="section0_1" style="margin-top:10px;">
	<tr style="height: 20px;">
		<td class="adm_cell_1st"><label for="frm_location_active" title="Доступно для поиска и просмотра посетителями сайта" style="margin:3px;font-weight:bold;">Опубликовать</label></td>
		<td class="adm_cell_2nd">
		<?=form_checkbox(array('name'=> 'frm_location_active','id'=>'frm_location_active','checked'=>$active,'value'=>'on','style'=>'vertical-align:middle;'));?>
		&nbsp;&nbsp;
		<label for="frm_location_active">Отметьте здесь и Ваше предложение найдут!</label>
		</td>
	</tr>
	<tr style="height: 20px;">
		<td class="adm_cell_1st"><label for="frm_location_active" title="Включить возможность оставлять комментарии и вопросы" style="margin:3px;font-weight:bold;">Включить комментарии</label></td>
		<td class="adm_cell_2nd">
		<?=form_checkbox(array('name'=> 'frm_location_comments','id'=>'frm_location_comments','checked'=>$comments,'value'=>'on','style'=>'vertical-align:middle;'));?>
		&nbsp;&nbsp;
		<label for="frm_location_active">Оставляйте лучшие комментарии Ваших посетителей.</label>
		</td>
	</tr>
</table>