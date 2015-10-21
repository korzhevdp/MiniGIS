<div class="modal hide fade" id="modal_pics" style="width:640px;">
	<div class="modal-header" style="cursor:move;background-color: #d6d6d6">
		<button type="button" class="close" data-dismiss="modal"><i class="icon-remove"></i></button>
		<h4>Bild des Objekts</h4>
	</div>
	<div class="modal-body" style="height:500px;overflow:hidden;">
	<div id="car_0" class="carousel slide" >
		<!-- Carousel items -->
		<div class="carousel-inner" id="pic_collection"></div>
		<!-- Carousel nav -->
	</div>
	</div>
	<div class="modal-footer">
		<form method="post" action="/upload/do_upload/frontend" enctype="multipart/form-data" class="form-inline row-fluid">

			<input type="file" placeholder="Datei..." class="span8" size="46" name="userfile" id="userfile" />
			<input type="text" name="comment" placeholder="Die Signatur auf dem Bild..." class="span12" id="upload_cmnt" maxlength="200" title="Bildtitel. Bearbeitet unter Fotos" />
			<button type="submit" class="btn btn-primary span12" style="margin-left:0px;margin-top:10px;">Entladen</button>
			
			<input type="hidden" name="upload_user" value="frontend_user" />
			<input type="hidden" name="upload_location" id="upload_location" value="" />
		</form>
	</div>

</div>
