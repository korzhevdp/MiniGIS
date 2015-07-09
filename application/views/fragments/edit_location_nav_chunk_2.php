<ul class="nav nav-tabs span12" style="margin-left:0px;">
	<li title="Вернуться к списку объектов" class="span3">
		<a href="/admin/index/<?=$obj_group;?>">назад к списку</a>
	</li>
	<li title="Основные данные объекта и контактная информация" class="span3 <?=($current_page == 1) ? 'active' : '';?>">
		<a href="/admin/pages/<?=$current_location;?>/1/<?=$obj_group;?>"><i class="icon-home"></i>&nbsp;Адрес и контакты</a>
	</li>
	<li title="Размещение объекта на карте" class="span2 <?=($current_page == 5) ? 'active' : '';?>">
		<a href="/admin/pages/<?=$current_location;?>/5/<?=$obj_group;?>"><i class="icon-globe"></i>&nbsp;Карта</a>
	</li>
	<li title="Дополнительное описание объекта" class="span3 <?=($current_page == 2) ? 'active' : '';?> offset1">
		<a href="/admin/pages/<?=$current_location;?>/2/<?=$obj_group;?>"><i class="icon-edit"></i>&nbsp;Описание</a>
	</li>
</ul>