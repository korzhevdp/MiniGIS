<div id="YMapsID" style="width:200px;height:650px;margin:0px;border:1px solid #c6c6c6;"></div>
<script type="text/javascript" src="<?=$this->config->item('api');?>/jscript/geomaps.js"></script>
<form method=post id="target_form" class="hide" style="display:none;" action="/editor/saveobject">
	<input type="hidden" id="l_id" name="id" value="<?=$id;?>">
	<input type="hidden" id="l_name" name="name">
	<input type="hidden" id="l_type" name="type">
	<input type="hidden" id="l_address" name="address">
	<input type="hidden" id="l_active" name="active">
	<input type="hidden" id="l_contact" name="contact">
	<input type="hidden" id="l_coord_y" name="coord_y">
	<input type="hidden" id="l_coord_y_aux" name="coord_y_aux">
	<input type="hidden" id="l_style" name="style">
</form>