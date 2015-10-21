
<!-- API 2.0 -->
	<script src="http://api-maps.yandex.ru/2.0/?coordorder=longlat&load=package.full&mode=debug&lang=ru-RU" type="text/javascript"></script>
	<script src="/jscript/map_calc.js" type="text/javascript"></script>
<!-- EOT API 2.0 -->
<DIV style="width:740px;">
	<!-- admin/locations_container -->
	<?=$location_summary;?>
	<?=$navigation;?>
	<?=$fullcontent;?>
	<?=$summary_table;?>
</DIV>

<DIV style="width:100px;height:100px;display:none" id ="fakemap">

</DIV>
<input type="hidden" id="param">
<input type="hidden" id="lid" value=<?=$lid;?>>

<script src="/jscript/map_styles2.js" type="text/javascript"></script>
<script type="text/javascript">
<!--
//наполнение pr_type будет производиться из подгруженного скрипта стилей
function makeSelect(src, namenode){
	for (a in src){
		$("#style_override").append('<option value="' + src[a][2] + '">' + src[a][namenode] + '</option>');
	}
	$('#style_override [value="' + $("#c_style_override").val() + '"]').attr('selected', 'selected');
}
switch ($("#pr_type").val()){
	case "1" :
		makeSelect(style_src, 3);
	break;
	case "2" :
		makeSelect(style_paths, 4);
	break;
	case "3" :
		makeSelect(style_polygons, 7);
	break;
	case "4" :
		makeSelect(style_circles, 7);
	break;
	case "5" :
		makeSelect(style_rectangles, 7);
	break;
}
//-->
</script>