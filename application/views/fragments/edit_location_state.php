<table id="section0_1" style="margin-top:10px;">
	<tr style="height: 20px;">
		<td class="adm_cell_1st"><label for="frm_location_active" title="�������� ��� ������ � ��������� ������������ �����" style="margin:3px;font-weight:bold;">������������</label></td>
		<td class="adm_cell_2nd">
		<?=form_checkbox(array('name'=> 'frm_location_active','id'=>'frm_location_active','checked'=>$active,'value'=>'on','style'=>'vertical-align:middle;'));?>
		&nbsp;&nbsp;
		<label for="frm_location_active">�������� ����� � ���� ����������� ������!</label>
		</td>
	</tr>
	<tr style="height: 20px;">
		<td class="adm_cell_1st"><label for="frm_location_active" title="�������� ����������� ��������� ����������� � �������" style="margin:3px;font-weight:bold;">�������� �����������</label></td>
		<td class="adm_cell_2nd">
		<?=form_checkbox(array('name'=> 'frm_location_comments','id'=>'frm_location_comments','checked'=>$comments,'value'=>'on','style'=>'vertical-align:middle;'));?>
		&nbsp;&nbsp;
		<label for="frm_location_active">���������� ������ ����������� ����� �����������.</label>
		</td>
	</tr>
</table>