<ul class="nav nav-tabs span12">
	<?if(!$parent): ?>
	<li title="��������� � ������ �����" class="span2">
		<a href="/admin/index/<?=$obj_group;?>">����� � ������</a>
	</li>
	<? else: ?>
	<li title="��������� � ������ �������" class="span2">
		<a href="/admin/pages/<?=$parent;?>/6">����� � ������</a>
	</li>
	<? endif ?>
	<li title="����� �������� ����(��������) � ���������� ����������" class="span2 <?=($current_page == 1) ? 'active' : '';?>">
		<a href="/admin/pages/<?=$current_location;?>/1/<?=$obj_group;?>">����� � ��������</a>
	</li>
	<li title="���������� ������� �� �����" class="span2 <?=($current_page == 5) ? 'active' : '';?>">
		<a href="/admin/pages/<?=$current_location;?>/5/<?=$obj_group;?>">�����</a>
	</li>
	<?if(!$parent): ?>
	<li title="��� ���� �����" class="span2 <?=($current_page == 2) ? 'active' : '';?>">
		<a href="/admin/pages/<?=$current_location;?>/2/<?=$obj_group;?>">������������</a>
	</li>
	<? endif ?>
	<?if($has_child): ?>
	<li title="������������ ������" class="span2 <?=($current_page == 3) ? 'active' : '';?>">
		<a href="/admin/pages/<?=$current_location;?>/3/<?=$obj_group;?>">������ � �������</a>
	</li>
	<li title="����������� �����������, ���������� �������, ���������� �������" class="span2 <?=($current_page == 4) ? 'active' : '';?>">
		<a href="/admin/pages/<?=$current_location;?>/4/<?=$obj_group;?>">����������</a>
	</li>
	<li title="������, �������: ��������, ���������, ����������" class="span2 <?=($current_page == 6) ? 'active' : '';?>">
		<a href="/admin/pages/<?=$current_location;?>/6/<?=$obj_group;?>">��� ������?</a>
	</li>
	<? endif ?>
	<?if(!$has_child): ?>
	<li title="�������� � ��������� ������" class="span2 <?=($current_page == 7) ? 'active' : '';?>">
		<a href="/admin/pages/<?=$current_location;?>/7/<?=$obj_group;?>">��������� ������</a>
	</li>
	<li title="���� � ������ �������" class="span2 <?=($current_page == 8) ? 'active' : '';?>">
		<a href="/admin/pages/<?=$current_location;?>/8/<?=$obj_group;?>">��������� ������</a>
	</li>
	<? endif ?>

</ul>