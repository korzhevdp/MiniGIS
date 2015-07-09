	<li class="nav-header">Редакторы</li>
	<li <?=($_SERVER["REQUEST_URI"]=="/usermodules/library") ? 'class="active"': "";?>>
		<a href="/usermodules/library" title="Редактор для объектов доступных пользователю. С контролем подлежащих объектов UM"><i class="icon-map-marker"></i>&nbsp;Моих размещений</a>
	</li>
	<li <?=($_SERVER["REQUEST_URI"]=="/usermodules/photomanager") ? 'class="active"': "";?>>
		<a href="/usermodules/photomanager" title="Редактор параметров и отображения фотографий UM"><i class="icon-picture"></i>&nbsp;Моих фотографий</a>
	</li>
	<li <?=($_SERVER["REQUEST_URI"]=="/usermodules/commentmanager") ? 'class="active"': "";?>>
		<a href="/usermodules/commentmanager" title="Потоковый редактор комментариев UM"><i class="icon-comment"></i>&nbsp;Моих Комментариев</a>
	</li>
	<li <?=($_SERVER["REQUEST_URI"]=="/usermodules/user") ? 'class="active"': "";?>>
		<a href="/usermodules/user" title="Редактор собственного профиля UM/AM"><i class="icon-user"></i>&nbsp;Моего профиля</a>
	</li>
	<li class="divider"></li>
	<li class="nav-header">Информация</li>
	<li <?=($_SERVER["REQUEST_URI"]=="/usermodules/help") ? 'class="active"': "";?>>
		<a href="/usermodules/help" title="Просмотр справки UM/AM"><i class="icon-question-sign"></i>&nbsp;К справке</a>
	</li>