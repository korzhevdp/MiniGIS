<div id="locmodal1" class="modal">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal"><i class="icon-remove"></i></button>
		<h4>Изображения объекта</h4>
	</div>

	<div class="modal-body" style="height:600px;width:600px;">
		<div id="car_0" class="carousel slide">
			<!-- Carousel items -->
			<div class="carousel-inner" id="pic_collection"></div>
			<!-- Carousel nav -->
		</div>
	</div>
	<div class="modal-footer">
		<form method="post" action="/upload/do_upload/frontend" enctype="multipart/form-data">
			<input type="file" class="btn btn-small" name="userfile" id="userfile">
			<input type="text" name="comment" placeholder="Подпись к картинке..." id="upload_cmnt" maxlength="200" title="Подпись к фотографии. Может быть отредактирована в разделе Фотографии" />
			<button type="submit" class="btn btn-primary btn-block" style="margin-left:0px;margin-top:10px;">Загрузить</button>
			
			<input type="hidden" name="upload_user" value="frontend_user" />
			<input type="hidden" name="upload_location" id="upload_location" value="">
		</form>
	</div>
</div>

