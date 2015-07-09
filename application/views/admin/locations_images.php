<div id="location_images_container" class="well span12">
	<ul style="list-style-type: none;" id="sortable">
			<li style="width:130px;height:150px;float:left;"><img src="/images/go-homebw.png" width="128" height="128" border="0" alt="" id="image_loader"></li>
			<?=$related_act.$related_inact;?>
	</ul>
</div>
<form method="post" enctype="multipart/form-data" class="form-inline row-fluid" action="upload/do_upload/<?=$src;?>">
	<input type="hidden" name="upload_user" value="<?=$user_id;?>">
	<input type="hidden" name="upload_location" value="<?=$location_id;?>">
	<label for="fileloader" class="span2">Загрузить:</label>
	<input id="fileloader" type="file" name="userfile" size="65"><br>
	<label for="comment"  class="span2">Подпись:</label>
	<input type="text" name="comment" id="comment"  class="span8" maxlength=200 title="Подпись к фотографии. Может быть отредактирована в разделе Фотографии"><br>
	<button type="submit" class="btn btn-primary span4">Загрузить</button>
</form>
<hr>
<SCRIPT TYPE="text/javascript">
<!--
$(function() {
	$( "#sortable" ).sortable({
		placeholder: "target-highlight",
		create: function(event, ui) {$('#frm_img_order').val($( "#sortable" ).sortable("toArray"));},
		stop: function(event, ui) {$('#frm_img_order').val($( "#sortable" ).sortable("toArray"));}
	});
	$( "#sortable" ).disableSelection();
});

//-->
</SCRIPT>