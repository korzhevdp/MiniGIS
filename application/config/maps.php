<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	//$config['yandex_key'] = 'AMlX8E0BAAAATVJwJQIAIQ3Yo0QK3NhIpRyNnSGB3xK7xIwAAAAAAAAAAADW9Oc-YbFGxHoVSUtmJw1QT0IJQg==';//minigis.ru
	//$config['yandex_key'] = 'AMfdck8BAAAAlshdHAQAP8OitEOHmK-NMufHwrCm4jj-epUAAAAAAAAAAACIPlXbUs8nO2NoQ1AjRXUdRgmArQ==';//minigis.meria.arhcity.ru
	//$config['yandex_key'] = 'ABDqck8BAAAAzWVSKgMAXOMQXWKnTbmG4BzGbW63YKdIAgQAAAAAAAAAAAC_9mousHmL3VCtMCm2F9rz0l7FJw==';//minigis.arhcity.ru
	//$config['yandex_key'] = 'AONf_U8BAAAAy5n2YwIATgnGKkZ6_shb8AcuARd6Q61KJI0AAAAAAAAAAAAn0R7oCiwW45OosAcrgfXfm0f01A==';//minigis.net
	$config['map_center']   = '40.51748,64.537797';
	$config['map_zoom']     = 12;
	$config['map_type']     = 1;
	$config['map_keywords'] = "Менеджер геообъектов, ГИС, ГИС-платформа, нарисовать карту, редактор карты, редактор Яндекс-карты, нарисовать карту, карта на сайт";
	$config['map_def_loc']  = "Архангельск";	#Умолчательное описание расположения
	$config['map_label']    = "Объекты ";		# Ярлык карты начинается с:
	$config['api']          = "http://api.korzhevdp.com";
	$config['native_lang']  = 'ru';
	$config['lang']         = array(
		"ru" => 'Русский',
		"en" => 'English',
		"de" => 'Deutsch',
		"es" => 'Español'
	);
	$config['brand'] = array(
		'ru' =>'<a class="brand" href="/">ПРОЕКТ&nbsp;&nbsp;Minigis.NET <img src="'.$config['api'].'/images/minigis24.png" alt="MiniGIS" title="MiniGIS Project"></a>',
		'en' =>'<a class="brand" href="/">The PROJECT&nbsp;&nbsp;<img src="'.$config['api'].'/images/minigis24.png" alt="MiniGIS" title="MiniGIS Project"></a>',
		'de' =>'<a class="brand" href="/">die Entwicklung&nbsp;&nbsp;Minigis.NET&nbsp;&nbsp;<img src="'.$config['api'].'/images/minigis24.png" alt="MiniGIS" title="MiniGis Project"></a>',
		'es' =>'<a class="brand" href="/">El PROYECTO&nbsp;&nbsp;Minigis.NET <img src="'.$config['api'].'/images/minigis24.png" alt="MiniGIS" title="MiniGIS Project"></a>'
	);
	$config['image_limit'] = 3;
	$config['image_paid_limit'] = 7;
	$config['upload_dir'] = './uploads/';
	$config['img_sizes'] = array(
		'small' => array('max_dim' => 32,  'quality' => 85, 'dir' => $config['upload_dir'].'small/'),
		'mid'   => array('max_dim' => 128, 'quality' => 50, 'dir' => $config['upload_dir'].'mid/'),
		'full'  => array('max_dim' => 400, 'quality' => 50, 'dir' => $config['upload_dir'].'full/')
	);
	$config['rise_correctness_alert'] = false;
	$config['admin_can_edit_user_locations'] = true;
############
############ параметры вызовов модулей
############

	## группа объектов "жильё" //врем
	$config['mod_housing']  = 4;
	$config['mod_gis']      = 1;
	##############
	##############
	# номер страницы, к которой прикреплены дочерние объекты - данные структуры модуля "Жильё"
	$config['mod_housing_root']  = 2;
	
	############################################################################################################################
	############################################################################################################################
	$config['site_friendly_url'] = "mapS.KorzhevDP.COM"; // имя сайта для отправки почты. URL некритично. Это просто имя.
	$config['site_reg_email']    = "korzhevdp@gmail.com"; // адрес отправки почты. Корректность критична.
	$config['site_reg_hello']    = "<!-- <P>Зарегистрировавшись на сайте как владельцы жилья, Вы получаете возможность разместить Ваше предложение на карте-схеме города. К Вашим услугам будет полностью автоматизированный поиск, удобные средства для размещения и подробного описания вашего предложения, комплекс средств для управления предложениями для крупных предприятий и равные возможности для частных владельцев. </P>
	<P>Использование интерактивной карты, постоянное пополнение информации об объектах инфраструктуры отдыха, развлекательных и оздоровительных центрах, малых предприятиях города сферы услуг позволит максимально раскрыть выгоды именно Вашего предложения. Чем больше информации о вас мы сможем сообщить нашим гостям, тем скорее они выберут именно Вас.</P>
	<P>Спрос есть всегда, помогите ему встретить своё Предложение!</P> --> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<small>Регистрация? Всегда пожалуйста. Мы ценим ваше участие.</small>"; // приветственное слово при регистрации.
	$config['support_email'] = 'register@korzhevdp.com';
	$config['reg_active']    = TRUE;

/* End of file maps.php */
/* Location: ./application/config/maps.php */