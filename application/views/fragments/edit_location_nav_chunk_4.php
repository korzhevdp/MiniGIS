<ul class="nav nav-tabs span12" style="margin-left:0px;">
	<li title="��������� � ������ ��������" class="span3">
		<a href="/admin/index/<?=$obj_group;?>">����� � ������</a>
	</li>
	<li title="�������� ������ ������� � ���������� ����������" class="span3 <?=($current_page == 1) ? 'active' : '';?>">
		<a href="/admin/pages/<?=$current_location;?>/1/<?=$obj_group;?>">����� � ��������</a>
	</li>
	<li title="�������������� �������� �������" class="span3 <?=($current_page == 2) ? 'active' : '';?>">
		<a href="/admin/pages/<?=$current_location;?>/2/<?=$obj_group;?>">��������</a>
	</li>
	<li title="���������� ������� �� �����" class="span3 <?=($current_page == 5) ? 'active' : '';?>">
		<a href="/admin/pages/<?=$current_location;?>/5/<?=$obj_group;?>">�����</a>
	</li>
</ul>