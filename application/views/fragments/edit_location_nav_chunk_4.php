<ul class="nav nav-tabs span12" style="margin-left:0px;">
	<li title="Вернуться к списку объектов" class="span3">
		<a href="/admin/index/<?=$obj_group;?>">назад к списку</a>
	</li>
	<li title="Основные данные объекта и контактная информация" class="span3 <?=($current_page == 1) ? 'active' : '';?>">
		<a href="/admin/pages/<?=$current_location;?>/1/<?=$obj_group;?>">Адрес и контакты</a>
	</li>
	<li title="Дополнительное описание объекта" class="span3 <?=($current_page == 2) ? 'active' : '';?>">
		<a href="/admin/pages/<?=$current_location;?>/2/<?=$obj_group;?>">Описание</a>
	</li>
	<li title="Размещение объекта на карте" class="span3 <?=($current_page == 5) ? 'active' : '';?>">
		<a href="/admin/pages/<?=$current_location;?>/5/<?=$obj_group;?>">Карта</a>
	</li>
</ul>