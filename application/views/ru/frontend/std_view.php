<!-- <script type="text/javascript" src="<?=$this->config->item('api');?>/jscript/map_styles2.js"></script>
<script type="text/javascript" src="<?=$this->config->item('api');?>/jscript/jquery.js"></script>
<script type="text/javascript" src="<?=$this->config->item('api');?>/bootstrap/js/bootstrap.js"></script>
<link href="<?=$this->config->item('api');?>/bootstrap/css/bootstrap.css" rel="stylesheet">
<link href="<?=$this->config->item('api');?>/css/frontend.css" rel="stylesheet" media="screen" type="text/css"> -->


<h3  class="stdView"><?=$location_name;?><small><i class="icon-tags"></i><?=$name;?></small></h3>
<div class="stdView">
	<img src="<?=$statmap;?>" alt="minimap">
	<?=(isset($all_images) && strlen($all_images)) ? $all_images : "" ;?>
	<div class="address"><i class="icon-home"></i><?=$address;?></div>
	<div class="contacts"><i class="icon-envelope"></i><?=$contact;?></div>
	<div class="coordinates"><i class="icon-map-marker"></i><span class="coord1"><?=((strlen($lat) > 68) ? substr($lat, 0, 62)."..." : $lat);?></span><br>
	<span class="coord2"><?=((strlen($lon) > 68) ? substr($lon, 0, 62)."..." : $lon);?></span></div>
</div>
<div class="stdViewContent">
	<?=$content;?>
</div>

