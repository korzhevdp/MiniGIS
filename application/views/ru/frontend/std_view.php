<script type="text/javascript" src="<?=$this->config->item('api');?>/jscript/map_styles2.js"></script>
<script type="text/javascript" src="<?=$this->config->item('api');?>/jscript/jquery.js"></script>
<script type="text/javascript" src="<?=$this->config->item('api');?>/bootstrap/js/bootstrap.js"></script>
<link href="<?=$this->config->item('api');?>/bootstrap/css/bootstrap.css" rel="stylesheet">
<link href="<?=$this->config->item('api');?>/css/frontend.css" rel="stylesheet" media="screen" type="text/css">


<h3  class="stdView"><?=$location_name;?><small><i class="icon-tags"></i><?=$name;?></small></h3>
<div class="stdView">
	<img src="<?=$statmap;?>" alt="миникарта">
	<?=(isset($all_images) && strlen($all_images)) ? $all_images : "" ;?>
	<span class="address"><i class="icon-home"></i><?=$address;?></span><br>
	<span class="contacts"><i class="icon-envelope"></i><?=$contact;?></span><br>
	<span class="coordinates"><i class="icon-map-marker"></i><span class="coord1"><?=$lat.'</span><br><span class="coord2">'.$lon;?></span></span>
</div>

<div class="stdViewContent">
	<?=$content;?>
</div>