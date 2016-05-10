<meta name='yandex-verification' content='74872298f6a53977' />
<meta name="keywords" content="<?=$keywords;?>">
<!-- API 2.0 -->
<script type="text/javascript" src="<?=$this->config->item('api');?>/jscript/jquery.js"></script>
<script src="http://api-maps.yandex.ru/2.0/?coordorder=longlat&amp;load=package.full&amp;mode=debug&amp;lang=<? if ($this->session->userdata("lang") === "ru") { ?>ru-RU<?} else { ?>en-US<? } ?>" type="text/javascript"></script>

<!-- <script type="text/javascript" src="<?=$this->config->item('api');?>/jscript/map_styles2.js"></script> -->
<script type="text/javascript" src="<?=$this->config->item('api');?>/jscript/styles2.js"></script>
<script type="text/javascript" src="<?=$this->config->item('api');?>/jqueryui/js/jqueryui.js"></script>
<script type="text/javascript" src="<?=$this->config->item('api');?>/bootstrap/js/bootstrap.js"></script>
<!-- EOT API 2.0 -->
<link href="<?=$this->config->item('api');?>/bootstrap/css/bootstrap.css" rel="stylesheet">
<link href="<?=$this->config->item('api');?>/css/frontend.css" rel="stylesheet" media="screen" type="text/css">