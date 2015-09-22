<div class="modal hide fade" id="modal_pics" style="width:440px;">
	<div class="modal-header" style="cursor:move;background-color: #d6d6d6">
		<button type="button" class="close" data-dismiss="modal"><i class="icon-remove"></i></button>
		<h4>Изображения объекта</h4>
	</div>
	<div class="modal-body" style="height:300px;overflow:hidden;vertical-align:middle">
		<div id="car_0" class="carousel slide" data-interval=5000 data-pause="hover">
			<!-- Carousel items -->
			<div class="carousel-inner" id="p_coll" style="text-align:center;vertical-align:middle;"></div>
			<!-- Carousel nav -->
			<!-- Carousel controls -->
			<a class="carousel-control left" href="#car_0" data-slide="prev">&lsaquo;</a>
			<a class="carousel-control right" href="#car_0" data-slide="next">&rsaquo;</a>
		</div>
	</div>
	<div class="modal-footer">
		<form method="post" action="/upload/loadimage" enctype="multipart/form-data" class="form-inline row-fluid">
			<input type="file" placeholder="Файл..." class="span8" size="46" name="userfile" id="userfile" />
			<input type="text" name="comment" placeholder="Подпись к картинке..." class="span12" id="upload_cmnt" maxlength="200" title="Подпись к фотографии. Может быть отредактирована в разделе Фотографии" />
			<button type="submit" class="btn btn-primary span12" style="margin-left:0px;margin-top:10px;">Загрузить</button>
			<input type="hidden" name="upload_user" value="frontend_user" />
			<input type="hidden" name="upload_from" value="page/map/<?=$mapset?>" />
			<input type="hidden" name="upload_to_location" id="upl_loc" value="" />
		</form>
	</div>
</div>

<div class="modal hide fade" id="langSelector" style="width:440px;">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal"><i class="icon-remove"></i></button>
		Выбор языка
	</div>
	<div class="modal-body">
		<form method=post action="/map/set_language">
			<button type="submit" class="btn btn-large langSubmitter" value="ru" title="Русский язык"><img src="http://api.korzhevdp.com/images/flag_ru.png" width="32" height="32" border="0" alt=""></button>
			<button type="submit" class="btn btn-large langSubmitter" value="en" title="Английский язык"><img src="http://api.korzhevdp.com/images/flag_en.png" width="32" height="32" border="0" alt=""></button>
			<button type="submit" class="btn btn-large langSubmitter" value="de" title="Немецкий язык"><img src="http://api.korzhevdp.com/images/flag_de.png" width="32" height="32" border="0" alt=""></button>
			<button type="submit" class="btn btn-large langSubmitter" value="es" title="Испанский язык"><img src="http://api.korzhevdp.com/images/flag_es.png" width="32" height="32" border="0" alt=""></button>
			<input type="hidden" name="redirect" value="<?=$this->uri->uri_string()?>">
		</form>
	</div>
	<div class="modal-footer"></div>
</div>