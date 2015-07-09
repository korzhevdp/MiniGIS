<ul class="nav nav-tabs span12">
	<?if(!$parent): ?>
	<li title="Вернуться к списку домов" class="span2">
		<a href="/admin/index/<?=$obj_group;?>">назад к списку</a>
	</li>
	<? else: ?>
	<li title="Вернуться к списку номеров" class="span2">
		<a href="/admin/pages/<?=$parent;?>/6">назад к списку</a>
	</li>
	<? endif ?>
	<li title="Общее описание дома(квартиры) и контактная информация" class="span2 <?=($current_page == 1) ? 'active' : '';?>">
		<a href="/admin/pages/<?=$current_location;?>/1/<?=$obj_group;?>">Адрес и контакты</a>
	</li>
	<li title="Размещение объекта на карте" class="span2 <?=($current_page == 5) ? 'active' : '';?>">
		<a href="/admin/pages/<?=$current_location;?>/5/<?=$obj_group;?>">Карта</a>
	</li>
	<?if(!$parent): ?>
	<li title="Что есть рядом" class="span2 <?=($current_page == 2) ? 'active' : '';?>">
		<a href="/admin/pages/<?=$current_location;?>/2/<?=$obj_group;?>">Расположение</a>
	</li>
	<? endif ?>
	<?if($has_child): ?>
	<li title="Предлагаемые услуги" class="span2 <?=($current_page == 3) ? 'active' : '';?>">
		<a href="/admin/pages/<?=$current_location;?>/3/<?=$obj_group;?>">Услуги и сервисы</a>
	</li>
	<li title="Особенности архитектуры, содержания номеров, количество номеров" class="span2 <?=($current_page == 4) ? 'active' : '';?>">
		<a href="/admin/pages/<?=$current_location;?>/4/<?=$obj_group;?>">Оформление</a>
	</li>
	<li title="Номера, комнаты: описания, оснащение, фотографии" class="span2 <?=($current_page == 6) ? 'active' : '';?>">
		<a href="/admin/pages/<?=$current_location;?>/6/<?=$obj_group;?>">Что сдаётся?</a>
	</li>
	<? endif ?>
	<?if(!$has_child): ?>
	<li title="Описание и оснащение комнат" class="span2 <?=($current_page == 7) ? 'active' : '';?>">
		<a href="/admin/pages/<?=$current_location;?>/7/<?=$obj_group;?>">Оснащение комнат</a>
	</li>
	<li title="Цены и ценвые периоды" class="span2 <?=($current_page == 8) ? 'active' : '';?>">
		<a href="/admin/pages/<?=$current_location;?>/8/<?=$obj_group;?>">Стоимость номера</a>
	</li>
	<? endif ?>

</ul>