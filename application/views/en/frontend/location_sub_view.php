<DIV class="<?=$div_class;?>" id="info_table_<?=$table_id;?>">
	<TABLE style="border-collapse: collapse; width:670px;" border=0>
	<TR>
		<TD style="font-size:9pt;padding-left:5px;height:130;vertical-align:top;width:100%">
					<TABLE style="border-collapse: collapse; width:100%;" border=0>
						<TR>
							<TD colspan=2 class="prop_table_cell_caption" style="width:100%"><?=$parent;?></TD>
						</TR>
						<TR>
							<TD class="prop_table_cell" colspan=2>
								<DIV style="float:left;border:0px;width:300px;">
								<B>Title:</B>&nbsp;<?=$name;?>&nbsp;<?=$location_name;?><br>
								<B>Address:</B> <?=$address;?><br>
								<B>Contact information:</B> <?=$contact_info;?>
								<script type="text/javascript" src="//yandex.st/share/share.js" charset="utf-8"></script>
								<DIV id="ya_share1" style="margin-top:5px;"></DIV>
								</DIV>
								<SCRIPT TYPE="text/javascript">
								<!--
								new Ya.share({
									element: 'ya_share1',
										elementStyle: {
										'type': 'button',
										'border': true,
										'quickServices': ['vkontakte','facebook','twitter','odnoklassniki','lj']
									},
									title: 'MiniGIS - <?=$name;?> <?=$location_name;?>',
									popupStyle: {
										blocks: {
											'Tell A Friend': ['vkontakte','facebook','twitter','odnoklassniki','lj']
										},
										copyPasteField: true
									}
								});
								//-->
								</SCRIPT>
								<IMG SRC="http://static-maps.yandex.ru/1.x/?ll=<?=$coord_y;?>&amp;size=128,128&amp;z=13&amp;l=map&amp;pt=<?=$coord_y;?>,pmwts&amp;key=<?=$yandex_key;?>" ALT="map" width=128 height=128 style="float:right;border:0px;">
							</TD>
						</TR>
						<TR>
							<TD class="prop_table_cell_caption" colspan=2 id="guest_desc">Description:</TD>
						</TR>
						<TBODY id="guest_desc_body">
						<TR>
							<TD class="prop_table_cell" style="width:150px;">
								<DIV class="prop_table_cell_caption">Room type / room:</DIV>
							</TD>
							<TD class="prop_table_cell" ><?=$type;?></TD>
						</TR>

					<TR>
						<TD class="prop_table_cell" style="width:150px;"><DIV class="prop_table_cell_caption">The contents of the rooms:</DIV></TD>
						<TD class="prop_table_cell" ><?=$roomwealth;?></TD>
					</TR>
					<TR>
						<TD class="prop_table_cell"><DIV class="prop_table_cell_caption">Description:</DIV></TD>
						<TD class="prop_table_cell" ><?=$roomdesc;?></TD>
					</TR>

						<TR>
							<TD class="prop_table_cell"><DIV class="prop_table_cell_caption">Area:</DIV></TD>
							<TD class="prop_table_cell" ><?=$square;?>&nbsp;ì.<SUP>2</SUP></TD>
						</TR>
						<TR>
							<TD class="prop_table_cell"><DIV class="prop_table_cell_caption">Places:</DIV></TD>
							<TD class="prop_table_cell" >Primary: <?=$bas_place;?><br>Additional: <?=$add_place;?></TD>
						</TR>
						<TR>
							<TD class="prop_table_cell"><DIV class="prop_table_cell_caption">The view from the window:</DIV></TD>
							<TD class="prop_table_cell" ><?=$view;?></TD>
							</TR>
						<TR>
							<TD class="prop_table_cell"><DIV class="prop_table_cell_caption">Room facilities:</DIV></TD>
							<TD class="prop_table_cell" ><?=$equipment;?></TD>
						</TR>
						</TBODY>

					</TABLE>



		</TD>
		<TD style="width:132px;vertical-align: top;padding-top:12px;border-left:1px solid #D6D6D6;">
			<?=$image1;?>
			<? if(strlen($all_images)>0){print $all_images;}?>
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