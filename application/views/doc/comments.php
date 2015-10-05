<div class="commentForm">
<h4>Комментарии</h4>
<?=$comments;?>
<hr>
<h5 title="к полной премодерации высказываний собравшихся">Добавить правды</h5>
<form method=post id="c_form" action="/page/addcomment/<?=$location_id;?>" class="form-inline" style="font-size:10px;">
	<div class="input-prepend control-group">
		<span class="add-on pre-label">Имя отправителя</span><input type="text" class="input-small" name="name" id="name" title="Введите имя, которым вы хотите представиться." placeholder="Имя">
	</div>
	<div class="input-prepend control-group">
		<span class="add-on pre-label">О себе</span><input type="text" class="input-small" name="about" id="about" title="Укажите, как с вами связаться" placeholder="Откуда Вы или хотя бы e-mail">
	</div>
	<div class="input-prepend control-group">
		<span class="add-on pre-label">Сообщение или вопрос</span><textarea name="send_text" id="send_text" rows="3" cols="30" class="textarea" title="Ваш вопрос или комментарий" placeholder="Ваш вопрос или комментарий"></textarea>
		<small>Не более 1000 символов. Осталось: <span id="counter">1000</span></small>
	</div>
	<div class="input-prepend control-group">
		<span class="add-on pre-label" title="Всего лишь одна маленькая проверка на человечность">Картинка</span><img src="/<?=$captcha;?>" style="width:100px;height:50px;border:1px solid black;" alt="captcha" title="Введите буквы с этой картинки">
	</div>
	<div class="input-prepend control-group">
		<span class="add-on pre-label" title="Всего лишь одна маленькая проверка на человечность">На картинке буквы</span><input type="text" class="input-small" id="cpt" name="cpt">
	</div>
	<input type="hidden" name="location_id" value="<?=$location_id;?>">
	<input type="hidden" id="random" name="random" value="<?=$this->session->userdata("common_user");?>">
	<input type="button" id="form_submit" class="btn btn-primary pull-right;" value="Отправить сообщение">
</form>
</div>
<script type="text/javascript">
<!--
$("#form_submit").click(function () {
	$('#send_text').val($('#send_text').val().substr(0, 1000));
	$('#about').val($('#about').val().substr(0, 250));
	$('#name').val($('#name').val().substr(0, 200));
	$.ajax({
		url: "/dop/testcaptcha",
		data : {
			captcha : $('#cpt').val()
		},
		type: "POST",
		dataType: "text",
		success: function (data) {
			if(data === "OK"){
				$('#c_form').submit();
			}else{
				$("#cpt").val("Неверный код!");
			}
		},
		error: function (data, stat, err) {
			console.log([ data, stat, err ].join("\n"));
		}
	});
});

$("#send_text").keyup(function() {
	textlength = $('#send_text').val().length;
	$("#counter").html(1000 - $('#send_text').val().length);
	if (textlength >= 1000){
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
				$("#stat" + lid).html('<span class="label label-success">Показывается</span>');
				$("#s" + lid).css('display','none');
				$("#h" + lid).css('display','inline');
			}
			if(data=='hide'){
				$("#comm" + lid).removeClass("enabled").addClass("disabled");
				$("#stat" + lid).html('<span class="label label-warning">Не показывается</span>');
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
