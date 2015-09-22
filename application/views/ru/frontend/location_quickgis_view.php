<DIV class="info_table_innerdiv2" id="info_table">
	<TABLE class="gis_info_table">
		<TR>
			<TD class="body">

				<form method=post action="/page/qgis/<?=$location;?>/save">
					<TABLE class="gis_info_inner_table">
						<TR>
							<TD colspan=2 class="prop_table_cell_caption"><?=$name;?>&nbsp;<input type="text" name="q_gis_locname" id="q_gis_locname" VALUE="<?=$location_name;?>"></TD>
						</TR>
						<TR>
							<TD class="prop_table_cell" colspan=2>
								<DIV class="gis_sum">
								<label for="q_gis_address">Адрес:</label><input type="text" name="q_gis_address" id="q_gis_address" value="<?=$address;?>"><br>
								<label for="q_gis_contact">Контактная информация:</label><input type="text" name="q_gis_contact" id="q_gis_contact" value="<?=$contact_info;?>">
								</DIV>
								<!-- <IMG SRC="http://static-maps.yandex.ru/1.x/?ll=<?=$coord_y;?>&amp;size=128,128&amp;z=13&amp;l=map&amp;pt=<?=$coord_y;?>,pmwts&amp;key=<?=$yandex_key;?>" ALT="map" width=128 height=128 style="float:right;border:0px;"> -->
							</TD>
						</TR>
						<TR>
							<TD class="prop_table_cell_caption" id="desc" colspan=2>Описание:</TD>
						</TR>
						<TR>

							<TD class="prop_table_cell" colspan=2>

							<TEXTAREA NAME="q_gis_editor" id="q_gis_editor" ROWS="2" COLS="2"><?=$description;?></TEXTAREA>

							</TD>
						</TR>
					</TABLE>
					<input type="hidden" id="frm_name">
					<input type="hidden" name="frm_img_order" id="frm_img_order" value="">
					<INPUT TYPE="hidden" ID="coords" name="yandex_coords" value="">
				</form>

				<SCRIPT TYPE="text/javascript" SRC="/jscript/jquery-ui.js"></SCRIPT>
				
				<table class="gis_info_inner_table">
				<tr>
					<td class="prop_table_cell_caption">Фотографии:</td>
				</tr>
				<tr>
					<td><?=$photo;?></td>
				</tr>
				</table>
				
				<table class="gis_info_inner_table">
				<tr>
					<td class="prop_table_cell_caption">Размещение на карте:</td>
				</tr>
				<tr>
					<td>
						<?=$map;?>
					</td>
				</tr>
				</table>
			</TD>
			<TD class="gallery" style="vertical-align:top;">
				<?=$images;?>
			</TD>
		</TR>
	</TABLE>
</DIV>

<SCRIPT type="text/javascript" src="/ckeditor/ckeditor.js"></SCRIPT>
<SCRIPT TYPE="text/javascript">
<!--
	CKEDITOR.replace('q_gis_editor',{skin : 'v2'});
	$('#guest_offer').click(function() {
		$('#guest_offer_body').slideToggle('slow', function() {});
	});
//-->
</SCRIPT>