<!doctype html>
<html lang="en">
<head>
	<title> Административная консоль сайта </title>

	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link href="http://api.korzhevdp.com/bootstrap/css/bootstrap.css" rel="stylesheet">
	<link href="http://api.korzhevdp.com/jqueryui/css/jqueryui.css" rel="stylesheet">
	<style>
	.navbar{
		margin-left:3px;
		margin-right:3px;
		margin-top:3px;
	}
	span.label {
		width:140px;
		display:block;
		float:left;
		margin-right: 10px;
		margin-bottom: 6px;
		height:24px;
		clear: left;
	}
	.input-prepend,
	.input-prepend select,
	.input-prepend input{
		margin-bottom:1px;
		width:408px;
	}
	.alert{
		margin-bottom:1px;
	}
	.input-prepend select{
		width:424px;
	}

	span.add-on{
		display:table-cell;
		width:120px !important;
	}
	input[!type="radio"], select{
		vertical-align:baseline;
		display:block;
		float:left;
		clear: right;
		width:270px;
	}
	#loclist{
		margin: 0px 10px; 
		overflow:auto;
		height:550px;
		width:225px;
		float:left
	}
	#loclist div, #loclist button{
		height:75px;
		margin: 5px;
	}
	.mg-input-form{
		float:left;
		margin-left:10px;
		margin-top:0px;
		width:560px;
		height:525px;
		border: 1px solid #D6D6D6;
		display: none;
	}
	.help-inline{
		display:none;
	}
	#label1, #label2{
		width:430px;
	}
	#label2{
		display: none;
	}
	#lep_tab2 input, label{
		cursor:pointer;
		margin-right:10px;
	}
	#b2, #b3{
		display:inline;
	}
	.YMaps{
		width:540px;
		height:300px;
		margin:5px;
		clear:both;
	}
	#locAdder{
		clear:right;
		display:block;
		margin-left:10px;
		margin-bottom:10px;
	}
	</style>
	<script src="http://api-maps.yandex.ru/2.0/?coordorder=longlat&load=package.full&mode=debug&lang=ru-RU" type="text/javascript"></script>
</head>

<body onload="get_list();">
<!-- заголовок страницы с панелью пользователя -->
<div class="navbar">
	<div class="navbar-inner">
		<div class="container">
			<a class="brand" href="#">Жильесдатчик 42</a>
			<ul class="nav">
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="icon-map-marker"></i>&nbsp;Карта <b class="caret"></b></a>
					<ul class="dropdown-menu">
						<li onclick="map_center_edit();"><a href="#"><i class="icon-screenshot"></i>&nbsp;Настройки центра</a></li>
						<li><a href="#"><i class="icon-exclamation-sign"></i>&nbsp;Мои предложения</a></li>
						<li><a href="/rent" target="_blank"><i class="icon-exclamation-sign"></i>&nbsp;На главную</a></li>
					</ul>
				</li>
			</ul>
			<ul class="nav pull-right">
				<li class="dropdown">
				<a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="icon-user"></i> <?=$this->session->userdata('user_name');?><b class="caret"></b></a>
				<ul class="dropdown-menu">
					<li onclick="profile_edit();"><a href="#"><i class="icon-edit"></i>&nbsp;Профиль</a></li>
					<li><a href="#"><i class="icon-shopping-cart"></i>&nbsp;Оплатить участие</a></li>
					<li><a href="/admin/user_exit"><i class="icon-remove-sign icon-red"></i>&nbsp;Выход</a></li>
				</ul>
				</li>
			</ul>
		</div>
	</div>
</div>
<!-- заголовок страницы с панелью пользователя -->
	<!-- place for menu -->
	<?//=$menu;?>
<button type="button" class="btn btn-primary" id="locAdder">Добавить ещё</button>

<div class="well well-small" id="loclist"></div>
	
	<div class="well mg-input-form" id="loc_add_pane">
		<h4>Новый объект</h4>
		<div class="input-prepend">
			<span class="add-on">Вид жилья</span>
			<select name="" id="lap_ty">
				<?=$typeslist;?>
			</select>
		</div>
		<div class="input-prepend">
			<span class="add-on">Адрес</span>
			<input type="text" name="" id="lap_ad">
		</div>
		<div class="alert" id="label1">
			Щёлкните карту, чтобы установить метку
		</div>
		<div class="alert alert-success" id="label2">
			<button type="button" class="close" data-dismiss="alert">&times;</button>
			Метка установлена
		</div>
		<div id="YMapsID" class="YMaps"></div>
		<input type="hidden" name="" id="lap_coord">
		<div>
			<button type="button" class="btn btn-primary" onclick="location_insert();">Добавить</button>
			<button type="button" class="btn" onclick="cancel();">Закрыть</button>
		</div>
	</div>
	
	<div class="well well-small mg-input-form" id="map_center_pane">
		<h4>Установка центра карты</h4>
		<div class="input-prepend">
			<span class="add-on">Центр карты</span>
			<input type="text" id="a12" name="">
		</div>
		<div id="YMapsID2" class="YMaps"></div>
		<div>
			<button type="button" class="btn btn-primary" onclick="set_usercenter();">Сохранить</button>
			<button type="button" class="btn" onclick="cancel();">Закрыть</button>
		</div>
	</div>

	<div class="well well-small mg-input-form" id="loc_edit_pane">
		<h4>Редактирование объекта</h4>
		<ul class="nav nav-tabs">
			<li class="active" ><a href="#lep_tab1" id="lep_sw1" data-toggle="tab">Адрес/место</a></li>
			<li ><a href="#lep_tab2" id="lep_sw2" data-toggle="tab">Оснащение</a></li>
		</ul>
		<div class="tab-content" style="margin-bottom:5px;">
			<div class="tab-pane active" id="lep_tab1">
				<div class="input-prepend">
					<span class="add-on">Вид жилья</span>
					<select name="" id="lap_ty">
						<?=$typeslist;?>
					</select>
				</div>
				<div class="input-prepend">
					<span class="add-on">Адрес</span>
					<input type="text" name="" id="lep_ad" maxlength="300" pref_length="10">
				</div>
				<div id="YMapsID3" class="YMaps"></div>
				<input type="hidden" id="lep_coord" name="">
				<input type="hidden" id="lep_mod" name="" value="1">
			</div>
			<div class="tab-pane" id="lep_tab2">
				<p>Назначаемые свойства</p>
			</div>
		</div>
		<div>
			<button type="button" class="btn btn-primary" onclick="lpr_save();">Сохранить</button>
			<button type="button" class="btn" onclick="cancel();">Закрыть</button>
		</div>
	</div>

	<div class="well well-small mg-input-form" id="h_profile_edit">
		<h4>Наниматель</h4>
		<div class="input-prepend">
			<span class="add-on">Фамилия</span>
			<input type="text" name="" id="h1" prp="1" valid="rword" pref_length=1 maxlength=600 class="traceable">
		</div>

		<div class="input-prepend">
			<span class="add-on">Имя</span>
			<input type="text" name="" id="h2" prp="2" valid="rword" pref_length=1 maxlength=600 class="traceable">
		</div>

		<div class="input-prepend">
			<span class="add-on">Отчество</span>
			<input type="text" name="" id="h4" prp="3" valid="rword" pref_length=1 maxlength=600 class="traceable">
		</div>

		<div class="input-prepend">
			<span class="add-on">Паспорт</span>
			<select name="" id="h10" prp="10">
				<option value="ru" selected>Российская Федерация</option>
			</select>
		</div>
		
		<div class="input-prepend">
			<span class="add-on">Серия</span>
			<input type="text" maxlength=4 name="" id="h4" prp="4" valid="num" pref_length=4 class="traceable">
		</div>

		<div class="input-prepend">
			<span class="add-on">Номер</span>
			<input type="text" maxlength=6 name="" id="h5" prp="5" valid="num" pref_length=6 class="traceable">
		</div>

		<div class="input-prepend">
			<span class="add-on">Выдан (с датой)</span>
			<input type="text" name="" maxlength=600 id="h6" prp="6" valid="mtext" pref_length=30 class="traceable">
		</div>

		<div class="input-prepend">
			<span class="add-on">Код подразделения</span>
			<input type="text" name="" maxlength=10 id="h7" prp="7" valid="num" pref_length=7 class="traceable">
		</div>

		<div class="input-prepend">
			<span class="add-on">Зарегистрирован</span>
			<input type="text" name="" id="h8" prp="8" valid="mtext" maxlength=600 pref_length=30 class="traceable">
		</div>

		<span class="label label-info">Контакты</span>
		
		<div class="input-prepend">
			<span class="add-on">Контактный тел.</span>
			<input type="text" name="" id="h9" prp="9" valid="num" maxlength=60 pref_length=7 class="traceable">
		</div>
		<div>
			<button type="button" class="btn btn-primary" onclick="save_agent();">Сохранить</button>
			<button type="button" class="btn" onclick="cancel();">Закрыть</button>
		</div>
	</div>

	<div class="well well-small mg-input-form" id="price_edit">
		<h4>Цены</h4>
		<div class="control-group" id="dl1">
			<div class="controls">
				<span class="label" id="de1">1 день</span><div class="input-append"><input class="traceable" type="text" id="d1" valid='num' maxlength=6 pref_length=3 prp=1><span class="add-on">руб./день</span></div>
			</div>
		</div>
		<div class="control-group" id="dl2">
			<div class="controls">
				<span class="label" id="de2">2 дня</span><div class="input-append"><input class="traceable" type="text" id="d2" valid='num' maxlength=6 pref_length=3 prp=2><span class="add-on">руб./день</span></div>
			</div>
		</div>
		<div class="control-group" id="dl3">
			<div class="controls">
				<span class="label" id="de3">3 дня / 4 дня</span><div class="input-append"><input class="traceable" type="text" id="d3" valid='num' maxlength=6 pref_length=3 prp=3><span class="add-on">руб./день</span></div>
			</div>
		</div>
		<div class="control-group" id="dl5">
			<div class="controls">
				<span class="label" id="de5">от 5 дней до месяца</span><div class="input-append"><input  class="traceable" type="text" id="d5" valid='num' maxlength=6 pref_length=3 prp=5><span class="add-on">руб./день</span></div>
			</div>
		</div>
		<div class="control-group" id="dlm">
			<div class="controls">
				<span class="label" id="dem">За месяц</span><div class="input-append"><input class="traceable" type="text" id="dm" valid='num' maxlength=6 pref_length=3 prp="m"><span class="add-on">руб./мес.</span></div>
			</div>
		</div>
		<input type="hidden" name="" id="hash">
		<div>
			<button type="button" class="btn btn-primary" onclick="price_save();">Сохранить</button>
			<button type="button" class="btn" onclick="cancel();">Закрыть</button>
		</div>
	</div>

	<div class="well well-small mg-input-form" id="profile_edit">
		<h4>Профиль</h4>
		<ul class="nav nav-tabs">
			<li class="active"><a href="#pr_tab1" data-toggle="tab">Пользователь</a></li>
			<li><a href="#pr_tab2" data-toggle="tab">Наймодатель</a></li>
			<li><a href="#pr_tab3" data-toggle="tab">Внимание!</a></li>
		</ul>
		<div class="tab-content">
			<div class="tab-pane" id="pr_tab3">
			Операция по смене пароля может быть потенциально опасной. Если вы сменили пароль и забыли его, воспользуйтесь сервисом восстановления пароля. Но в этом случае, поскольку никто не имеет доступа к содержимому Вашего пароля, данные, необходимые для оформления документов, не могут быть восстановлены и должны быть введены повторно.
			</div>
			<div class="tab-pane active" id="pr_tab1">
				<div class="control-group" id="ul1">
					<div class="controls">
						<span class="label" id="ue1">Логин пользователя</span><input type="text" name="" id="u1" prp="1" valid="mtext" pref_length=6 maxlength=256 class="traceable">
					</div>
				</div>
				<div style="clear:both">
					<button type="button" class="btn btn-primary" onclick="save_login();">Сохранить</button>
					<button type="button" class="btn" onclick="cancel();">Закрыть</button>
				</div>
				<DIV id="passw_pane" style="display:block;clear:both;margin-top:15px;">
					<h4>Смена пароля.</h4>
					<div class="control-group">
						<div class="controls">
							<span class="label">Текущий пароль</span><input type="password" name="" maxlength=256 class="input-xlarge">
						</div>
					</div>
					<div class="control-group">
						<div class="controls">
							<span class="label">Новый пароль</span><input type="password" name="" maxlength=256 class="input-xlarge">
						</div>
					</div>
					<div class="control-group">
						<div class="controls">
							<span class="label">Повторите новый пароль</span><input type="password" name="" maxlength=256 class="input-xlarge">
						</div>
					</div>
					<div style="clear:both">
						<button type="button" class="btn btn-primary" onclick="save_password();">Сохранить</button>
						<button type="button" class="btn" onclick="cancel();">Закрыть</button>
					</div>
				</DIV>
			</div>
			<div class="tab-pane" id="pr_tab2">
				<div class="control-group" id="pl1">
					<div class="controls">
						<span class="label" id="pe1">Фамилия</span><input type="text" name="" id="p1" prp="1" valid="rword" pref_length=1 maxlength=600 class="traceable">
					</div>
				</div>
				<div class="control-group" id="pl2">
					<div class="controls">
						<span class="label" id="pe2">Имя</span><input type="text" name="" id="p2" prp="2" valid="rword" pref_length=1 maxlength=600 class="traceable">
					</div>
				</div>
				<div class="control-group" id="pl3">
					<div class="controls">
						<span class="label" id="pe3">Отчество</span><input type="text" name="" id="p3" prp="3" valid="rword" pref_length=1 maxlength=600 class="traceable">
					</div>
				</div>
				<span class="label label-info">Паспорт</span><select name="" id="p10" prp="10">
					<option value="ru" selected>Российская Федерация</option>
				</select>
				<div class="control-group" id="pl4">
					<div class="controls">
						<span class="label" id="pe4">Серия</span><input type="text" maxlength=4 name="" id="p4" prp="4" valid="num" pref_length=4 class="traceable">
					</div>
				</div>
				<div class="control-group" id="pl5">
					<div class="controls">
						<span class="label" id="pe5">Номер</span><input type="text" maxlength=6 name="" id="p5" prp="5" valid="num" pref_length=6 class="traceable">
					</div>
				</div>
				<div class="control-group" id="pl6">
					<div class="controls">
						<span class="label" id="pe6">Выдан (с датой)</span><input type="text" name="" maxlength=600 id="p6" prp="6" valid="mtext" pref_length=30 class="traceable">
					</div>
				</div>
				<div class="control-group" id="pl7">
					<div class="controls">
						<span class="label" id="pe7">Код подразделения</span><input type="text" name="" maxlength=10 id="p7" prp="7" valid="num" pref_length=7 class="traceable">
					</div>
				</div>
				<div class="control-group" id="pl8">
					<div class="controls">
						<span class="label" id="pe8">Зарегистрирован</span><input type="text" name="" id="p8" prp="8" valid="mtext" maxlength=600 pref_length=30 class="traceable">
					</div>
				</div>
				<span class="label label-info">Контакты</span>
				
				<div class="control-group" id="pl9">
					<div class="controls">
						<span class="label" id="pe9">Контактный тел.</span><input type="text" name="" id="p9" prp="9" valid="num" maxlength=60 pref_length=7 class="traceable">
					</div>
				</div>
				<div style="clear:both">
					<button type="button" class="btn btn-primary" onclick="save_agent();">Сохранить</button>
					<button type="button" class="btn" onclick="cancel();">Закрыть</button>
				</div>
			</div>
		</div>
	</div>

	<div class="well well-small mg-input-form" id="rent_edit">
		<h4>Оформление договора</h4>
		<ul class="nav nav-tabs">
			<li class="active"><a href="#rent_tab1" data-toggle="tab">Наниматель</a></li>
			<li><a href="#rent_tab2" data-toggle="tab">Детали договора</a></li>
			<li><a href="#rent_tab3" data-toggle="tab">Завершение</a></li>
		</ul>
		<div class="tab-content">
			<div class="tab-pane active" id="rent_tab1">

				<div class="input-prepend">
					<span class="add-on">Фамилия</span>
					<input type="text" name="" id="h1" prp="1" valid="rword" pref_length=1 maxlength=600 class="traceable">
				</div>

				<div class="input-prepend">
					<span class="add-on">Имя</span>
					<input type="text" name="" id="h2" prp="2" valid="rword" pref_length=1 maxlength=600 class="traceable">
				</div>

				<div class="input-prepend">
					<span class="add-on">Отчество</span>
					<input type="text" name="" id="h4" prp="3" valid="rword" pref_length=1 maxlength=600 class="traceable">
				</div>

				<div class="input-prepend">
					<span class="add-on">Паспорт</span>
					<select name="" id="h10" prp="10">
						<option value="ru" selected>Российская Федерация</option>
					</select>
				</div>
				
				<div class="input-prepend">
					<span class="add-on">Серия</span>
					<input type="text" maxlength=4 name="" id="h4" prp="4" valid="num" pref_length=4 class="traceable">
				</div>

				<div class="input-prepend">
					<span class="add-on">Номер</span>
					<input type="text" maxlength=6 name="" id="h5" prp="5" valid="num" pref_length=6 class="traceable">
				</div>

				<div class="input-prepend">
					<span class="add-on">Выдан (с датой)</span>
					<input type="text" name="" maxlength=600 id="h6" prp="6" valid="mtext" pref_length=30 class="traceable">
				</div>

				<div class="input-prepend">
					<span class="add-on">Код подразделения</span>
					<input type="text" name="" maxlength=10 id="h7" prp="7" valid="num" pref_length=7 class="traceable">
				</div>

				<div class="input-prepend">
					<span class="add-on">Зарегистрирован</span>
					<input type="text" name="" id="h8" prp="8" valid="mtext" maxlength=600 pref_length=30 class="traceable">
				</div>

				<span class="label label-info">Контакты</span>
				
				<div class="input-prepend">
					<span class="add-on">Контактный тел.</span>
					<input type="text" name="" id="h9" prp="9" valid="num" maxlength=60 pref_length=7 class="traceable">
				</div>

				<div style="clear:both">
					<button type="button" class="btn btn-primary" onclick="save_hirer();">Сохранить</button>
					<button type="button" class="btn" onclick="cancel();">Закрыть</button>
				</div>
			</div>
			<div class="tab-pane" id="rent_tab2">
				<div class="control-group" id="bl1">
					<div class="controls">
						<span class="label" id="be1">Начальный день</span><input type="text" name="" id="b1" prp="1" valid="date" maxlength=10 pref_length=7 class="traceable2">
					</div>
				</div>
				<div class="control-group">
					<div class="controls">
						<span class="label">Период найма</span>
						<label class="radio inline"><input type="radio" id="d1" name="t1">посуточно</label>
						<label class="radio inline"><input type="radio" id="d2" name="t1">на длительный срок</label>
					</div>
				</div>
				<div class="control-group" id="bl2">
					<div class="controls">
						<span class="label" id="be2">Конечный день</span><input type="text" name="" id="b2" prp="2" valid="date" maxlength=10 pref_length=7 class="traceable2">
					</div>
				</div>
				<div class="control-group" id="bl3">
					<div class="controls">
						<span class="label" id="be3">На период</span>
						<select name="" id="b3" prp=3 valid="nonzero" pref_length=1 class="traceable2">
							<option value="0" selected>Выберите</option>
							<option value="1">1 месяц</option>
							<option value="2">2 месяца</option>
							<option value="3">3 месяца</option>
							<option value="4">4 месяца</option>
							<option value="5">5 месяцев</option>
							<option value="6">6 месяцев</option>
							<option value="7">7 месяцев</option>
							<option value="8">8 месяцев</option>
							<option value="9">9 месяцев</option>
							<option value="10">10 месяцев</option>
							<option value="11">11 месяцев</option>
						</select>
					</div>
				</div>
			</div>
			<div class="tab-pane" id="rent_tab3"></div>
		</div>
	</div>

	<!-- консоль -->
	<div class="alert alert-error" style="width:200px;height:400px;margin:20px;float:right;">
		<a class="close" data-dismiss="alert" href="#">*</a>
		<span id="console"></span>
	</div>
	<!-- консоль -->


<input type="hidden" name="map_center" id="map_center" value="<?=$maps_center;?>">
<input type="hidden" name="current_zoom" id="current_zoom" value=15>

<div id="announcer"></div>

<script src="http://api.korzhevdp.com/jscript/jquery.js"></script>
<script src="http://api.korzhevdp.com/jqueryui/js/jqueryui.js"></script>
<script src="http://api.korzhevdp.com/bootstrap/js/bootstrap.js" ></script>
<script src="http://api.korzhevdp.com/jscript/maps_rent.js"></script>

</body>
</html>
