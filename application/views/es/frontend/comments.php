<div class="well well-small" style="width:400px;">
<h4>Comentarios</h4>
<?=$comments;?>
<hr>
<h5 title="ir a la moderación">Añadir comentario</h5>
<form method=post id="c_form" action="/page/addcomment/<?=$location_id;?>" class="form-inline" style="font-size:10px;">
	<div>
		<label for="name">El nombre del remitente:</label>
		<input type="text" class="input-small" name="name" id="name" title="Escriba el nombre que desea presentarse.">
	</div>
	<div>
	<label for="about" >De dónde Eres o e-mail:</label>
		<input type="text" class="input-small" name="about" id="about" title="Especifique la información de contacto">
	</div>
	<div>
	<label for="send_text" >Un mensaje o pregunta:</label>
		<textarea name="send_text" style="width:200px;" id="send_text" rows="3" cols="30" class="textarea" title="Su pregunta o comentario"></textarea>
	</div>
	<div>
	<label >&nbsp;</label><span class="span4"><small>No más de 1000 caracteres. Queda:<span id="counter">1000</span></small></span>
		<input type="hidden" id="random" name="random" value="<?=$this->session->userdata("common_user");?>"><br><br>
	<label title="Sólo una pequeña comprobación de la humanidad" style="clear:left;">En la imagen de la letra:</label>
		<input type="text"  class="input-small" id="cpt" name="cpt"><br><br>
	<div class="span12">
		<label style="margin-left:0px;">Imagen</LABEL>
		<img sRC="/<?=$captcha;?>" style="width:100px;height:50px;border:1px solid black;" alt="captcha" title="Escriba las letras con esta imagen">
	</div>
	<input type="button" id="form_submit" class="btn btn-primary btn-mini btn-block" onclick="send_comment();" value="Enviar un mensaje">
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
		$("#cpt").val("El código incorrecto!");
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
				$("#stat" + lid).html('<span class="label label-success">Se muestra</span>');
				$("#s" + lid).css('display','none');
				$("#h" + lid).css('display','inline');
			}
			if(data=='hide'){
				$("#comm" + lid).removeClass("enabled").addClass("disabled");
				$("#stat" + lid).html('<span class="label label-warning">No se muestra</span>');
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
