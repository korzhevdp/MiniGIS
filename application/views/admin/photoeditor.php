<h1>Управление фотографиями. <small>non-tested Beta</small></h1>

<div class="well span9">
	<?=$locations;?>
</div>

<div class="btn-group span6">
	<button class="btn" onclick="rotate_cw()"><i class="icon-arrow-left"></i>&nbsp;Повернуть</button>
	<button class="btn" onclick="rotate_ccw()"><i class="icon-arrow-right"></i>&nbsp;Повернуть</button>
	<button class="btn" onclick=""><i class="icon-eye-open"></i>&nbsp;Показывать</button>
	<button class="btn" onclick=""><i class="icon-eye-close"></i>&nbsp;Не показывать</button>
</div>

<div class="well span9" id="sortable"></div>

<div class="well span9">
	<img src="/images/nophoto.jpg" id="current_picture" alt="">
</div>

<div class="well span9">
	Размещение: <span class="attribute" id="current_location">Не выбрано</span><br>
	Изображение: <span class="attribute" id="current_file">Нет</span><br>
	Размер: <span class="attribute" id="current_dimensions">0x0 px.</span>
</div>

<form method=post action="" id="exec_form">
	Комментарий к фотографии: <input type="text" id="pic_comment" name="pic_comment" value="">
	<input type="hidden" id="pic_name" name="pic_name" value="">
	<input type="hidden" name="frm_img_order" id="frm_img_order" value="">
	<button class="btn btn-primary btn-small span4" type="submit" id="submit_button">Сохранить порядок фотографий</button>
</form>

<script type="text/javascript" src="<?=$this->config->item("api");?>/jscript/photoeditor.js"></script>
<script type="text/javascript">
<!--
	<?=$list;?>;
	show_l_table('<?=$location_id;?>');
	prepare_pic_by_name('<?=$image_id;?>')
	
//-->
</script>