<DIV class="info_table_innerdiv2" id="info_table">
	<TABLE class="gis_info_table">
	<TR>
		<TD class="body">
			<TABLE class="gis_info_inner_table">
				<TR>
					<TD colspan=2 class="prop_table_cell_caption"><?=$location_name;?>&nbsp;<?=$name;?></TD>
				</TR>
				<TR>
					<TD class="prop_table_cell" colspan=2>
						<DIV class="gis_sum">
						<B>Address:</B> <address><?=$address;?></address><br>
						<B>Contact information:</B> <?=$contact_info;?>
						</DIV>
						<IMG SRC="http://static-maps.yandex.ru/1.x/?ll=<?=$coord_y;?>&amp;size=128,128&amp;z=13&amp;l=map&amp;pt=<?=$coord_y;?>,pmwts&amp;key=<?=$yandex_key;?>" ALT="map" width=128 height=128 style="float:right;border:0px;">
					</TD>
				</TR>
				<TR>
					<TD class="prop_table_cell_caption" colspan=2 id="guest_desc">Description:</TD>
				</TR>
				<TR>
					<TD class="prop_table_cell" colspan=2 id="guest_desc_body" style="height:200px;"><?=$description;?></TD>
				</TR>
			</TABLE>

		</TD>
		<TD class="images gallery">
			<?=$images;?>
		</TD>
	</TR>
	</TABLE>
</DIV>
<SCRIPT TYPE="text/javascript">
<!--
	$('#guest_desc').click(function() {
		$('#guest_desc_body').slideToggle('slow', function() {});
	});
	$('#guest_offer').click(function() {
		$('#guest_offer_body').slideToggle('slow', function() {});
	});
//-->
</SCRIPT>