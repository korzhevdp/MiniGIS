<li class="divider"></li>

<li class="nav-header">Объекты ГИС</li>
<li<?=($_SERVER["REQUEST_URI"]=="/admin/groupmanager") ? ' class="active"': "";?>>
	<a href="/admin/groupmanager" title="Города, вершинные признаки группировки объектов AM"><i class="icon-book"></i>&nbsp;Группы объектов</a>
</li>
<li class="divider"></li>
<?=$gis_library;?>

<li class="divider"></li>

<li class="nav-header">Центр Управления</li>
<li>
	<a href="/admin/sheets/edit/1" title="Пресс-служба AM">Тексты страниц</a>
</li>
<li<?=($_SERVER["REQUEST_URI"]=="/admin/maps") ? ' class="active"': "";?>>
	<a href="/admin/maps" title="Наборы данных для карт AM">Содержимое карт</a>
</li>

<li<?=($_SERVER["REQUEST_URI"]=="/admin/translations") ? ' class="active"': "";?>>
<a href="/admin/translations" title="Переводы названий групп, категорий и параметров AM">Переводы</a></li>

<li<?=($_SERVER["REQUEST_URI"]=="/admin/gis") ? ' class="active"': "";?>>
<a href="/admin/gis" title="Справочник типов объектов по группам AM">Типы объектов</a></li>

<!-- <li<?=($_SERVER["REQUEST_URI"]=="/admin/gis") ? '/editor/geosemantics': "";?>><a href="/editor/geosemantics" title="Привязка геосемантики AM">Объекты геосемантики</a></li> -->

<li class="divider"></li>

<li class="nav-header">Пользователи</li>
<li <?=($_SERVER["REQUEST_URI"]=="/admin/usermanager") ? 'class="active"': "";?>>
<a href="/admin/usermanager">Управление</a>
</li>