<h1>Комментарии <small>и вопросы</small></h1>
<?=$comments;?>

<SCRIPT TYPE="text/javascript">
<!--

$(".commentToggle").click(function() {
	ref = $(this).attr("ref");
	$.ajax({
		url      : "/page/comment_control",
		data     : {
			hash    : ref
		},
		type     : "POST",
		dataType : "text",
		success  : function(data){
			if (data === "N"){
				$("#cr" + ref).removeClass("icon-eye-open").addClass("icon-eye-close");
				$(".commentToggle[ref=" + ref + "]").attr('title', 'Опубликовать комментарий');
				$(".commentToggle[ref=" + ref + "] i").removeClass("icon-eye-open").addClass("icon-eye-close");
			}
			if (data === "A"){
				$("#cr" + ref).removeClass("icon-eye-close").addClass("icon-eye-open");
				$(".commentToggle[ref=" + ref + "]").attr('title', 'Скрыть комментарий');
				$(".commentToggle[ref=" + ref + "] i").removeClass("icon-eye-close").addClass("icon-eye-open");
			}
		}
	});
});

$(".commentDel").click(function() {
	ref = $(this).attr("ref");
	$.ajax({
		url      : "/page/comment_delete",
		data     : {
			hash    : ref
		},
		type     : "POST",
		dataType : "text",
		success  : function(data){
			if (data === "D"){
				$("#comm" + ref).remove();
			}
		}
	});
});

//-->
</SCRIPT>
