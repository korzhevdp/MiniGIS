	<li class="nav-header">Редакторы</li>
	<li <?=($_SERVER["REQUEST_URI"]=="/user/library") ? 'class="active"': "";?>>
		<a href="/user/library" title="Редактор для объектов доступных пользователю. С контролем подлежащих объектов UM"><i class="icon-map-marker"></i>&nbsp;Моих размещений</a>
	</li>
	<li <?=($_SERVER["REQUEST_URI"]=="/user/photomanager") ? 'class="active"': "";?>>
		<a href="/user/photomanager" title="Редактор параметров и отображения фотографий UM"><i class="icon-picture"></i>&nbsp;Моих фотографий</a>
	</li>
	<li <?=($_SERVER["REQUEST_URI"]=="/user/commentmanager") ? 'class="active"': "";?>>
		<a href="/user/commentmanager" title="Потоковый редактор комментариев UM"><i class="icon-comment"></i>&nbsp;Моих Комментариев</a>
	</li>
	<li <?=($_SERVER["REQUEST_URI"]=="/user/profile") ? 'class="active"': "";?>>
		<a href="/user/profile" title="Редактор собственного профиля UM/AM"><i class="icon-user"></i>&nbsp;Моего профиля</a>
	</li>
	<li class="divider"></li>
	<li class="nav-header">Информация</li>
	<li <?=($_SERVER["REQUEST_URI"]=="/user/help") ? 'class="active"': "";?>>
		<a href="/user/help" title="Просмотр справки UM/AM"><i class="icon-question-sign"></i>&nbsp;К справке</a>
	</li>