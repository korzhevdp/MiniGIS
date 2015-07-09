<TABLE class="nav_table" id="nav_table">
	<TR style="height:20px;">
		<TD id="plc_return" class="nav_menu_item_inactive" style="width:175px;text-align:left;padding-left:10px;" onclick="window.location = '<?=site_url("admin/places/".$parent);?>'"> назад к списку</TD>
		<TD style="text-align:right;"></TD>
	</TR>
</TABLE>
<SCRIPT TYPE="text/javascript">
<!--
$(".nav_menu_item_inactive").mouseenter(function() {
	$(this).removeClass("nav_menu_item_inactive").addClass("nav_menu_item_active");
});
$(".nav_menu_item_inactive").mouseleave(function() {
	$(this).removeClass("nav_menu_item_active").addClass("nav_menu_item_inactive");
});
$("#plc_return").mouseenter(function() {
	$(this).removeClass("nav_menu_item_inactive").addClass("nav_menu_item_return");
});
$("#plc_return").mouseleave(function() {
	$(this).removeClass("nav_menu_item_return").addClass("nav_menu_item_inactive");
});
//-->
</SCRIPT>