<h1>����������� <small>� �������</small></h1>
<?=$comments;?>

<SCRIPT TYPE="text/javascript">
<!--
function swc(lid,mod){
	$.ajax({
		url: "/dop/comment_control/" + lid + "/" + mod + "/" + Math.random(1,99999),
		type: "GET",
		success: function(data){
			if(data=='show'){
				$("#comm" + lid).removeClass("disabled").addClass("enabled");
				$("#stat" + lid).html('<span class="label label-success">������������</span>');
				$("#s" + lid).css('display','none');
				$("#h" + lid).css('display','inline');
			}
			if(data=='hide'){
				$("#comm" + lid).removeClass("enabled").addClass("disabled");
				$("#stat" + lid).html('<span class="label label-warning">�� ������������</span>');
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
