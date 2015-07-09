<!-- начало формы - в $locations_contact_info -->
	<?//=$locations_summary;?>
<table style="height:500px;padding:0px;table-layout: fixed">
<tr>
	<td style="height:18px;vertical-align:top;"><?//=$navigation;?></td>
</tr>
<tr>
	<td style="vertical-align:top;">
	<?=$locations_images;?>
	<FORM METHOD=POST ACTION="<?=site_url()?>/admin/datatrap" id="frm_main_form">
	<?=$places;?>

	<INPUT TYPE="hidden" NAME="current_location" ID="current_location" value ="<?=$current_location;?>">
	<INPUT TYPE="hidden" NAME="current_page" ID="current_page" value ="<?=$current_page;?>">
	<INPUT TYPE="hidden" NAME="new_page" ID="new_page" value ="">
	</FORM>

</td>
</tr>
</table>
