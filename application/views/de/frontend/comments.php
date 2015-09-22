<div class="well well-small" style="width:400px;">
<h4>Kommentare</h4>
<?=$comments;?>
<hr>
<h5 title="die komplette Moderation der Kommentare">Kommentar hinzufügen</h5>
<form method=post id="c_form" action="/page/addcomment/<?=$location_id;?>" class="form-inline" style="font-size:10px;">
	<div>
		<label for="name">Name des Absenders:</label>
		<input type="text" class="input-small" name="name" id="name" title="Geben Sie den Namen, die Sie sich vorstellen.">
	</div>
	<div>
	<label for="about" >Woher Sie oder e-mail:</label>
		<input type="text" class="input-small" name="about" id="about" title="Geben Sie Ihre Informationen ein, wenn es notwendig ist">
	</div>
	<div>
	<label for="send_text" >Eine Nachricht oder Frage:</label>
		<textarea name="send_text" style="width:200px;" id="send_text" rows="3" cols="30" class="textarea" title="Ihre Frage oder einen Kommentar"></textarea>
	</div>
	<div>
	<label >&nbsp;</label><span class="span4"><small>Nicht mehr als 1000 Zeichen. Links: <span id="counter">1000</span></small></span>
		<input type="hidden" id="random" name="random" value="<?=$this->session->userdata("common_user");?>"><br><br>
	<label title="Nur ein kleiner Test auf Menschlichkeit" style="clear:left;">Auf dem Bild Buchstaben:</label>
		<input type="text"  class="input-small" id="cpt" name="cpt"><br><br>
	<div class="span12">
		<label style="margin-left:0px;">Bild</LABEL>
		<img sRC="/<?=$captcha;?>" style="width:100px;height:50px;border:1px solid black;" alt="captcha" title="Geben Sie die Buchstaben mit diesem Bild">
	</div>
	<input type="button" id="form_submit" class="btn btn-primary btn-mini btn-block" onclick="send_comment();" value="Senden Sie eine Nachricht">
</form>
</div>
<script type="text/javascript">
<!--
function send_comment(){
	$('#send_text').val($('#send_text').val().substr(0,1000));
	//alert(document.getElementById('send_text').value);
	$('#about').val($('#about').val().substr(0,250));
	$('#name').val($('#name').val().substr(0,200));
	var testresult = $.ajax({
		url: "/dop/testcaptcha/" + encodeURIComponent($('#cpt').val()),
		global: false,
		type: "GET",
		dataType: "text",
		async:false
	}).responseText;
	if(testresult == "OK"){
		$('#c_form').submit();
	}else{
		$("#cpt").val("Falscher Code!");
	}
}

$("#send_text").keyup(function() {
	$("#counter").html(1000 - $('#send_text').val().length);
	if((1000 - textlength) < 0){
		$('#send_text').val($('#send_text').val().substr(0, 1000));
		$("#counter").html('0');
	}
});


function swc(lid,mod){
	var m = Math.random(1,99999);
	var string = "/dop/comment_control/" + lid + "/" + mod + "/" + m;
	$.ajax({
		url: string,
		type: "GET",
		success: function(data){
			//alert(data);
			if(data=='show'){
				$("#comm" + lid).removeClass("disabled").addClass("enabled");
				$("#stat" + lid).html('<span class="label label-success">Wird</span>');
				$("#s" + lid).css('display','none');
				$("#h" + lid).css('display','inline');
			}
			if(data=='hide'){
				$("#comm" + lid).removeClass("enabled").addClass("disabled");
				$("#stat" + lid).html('<span class="label label-warning">Wird nicht angezeigt</span>');
				$("#s" + lid).css('display','inline');
				$("#h" + lid).css('display','none');
			}
			if(data=='del'){
				$("#comm" + lid).fadeOut(800,function(){});
			}
		}
	});
}
//-->
</SCRIPT>
