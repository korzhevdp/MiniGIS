<DIV class="<?=$div_class;?>" id="info_table_<?=$table_id;?>">
	<TABLE style="border-collapse: collapse; width:670px;" border=0>
	<TR>
		<TD style="font-size:9pt;padding-left:5px;height:130;vertical-align:top;width:100%">
			<DIV style="font-size:16pt;padding:5px;vertical-align:top;width:100%">
				<SPAN style="color:#7474C4;font-weight:bold;"><?=$location_name;?></SPAN>&nbsp;
				<SPAN style="color:#000000;"><?=$name;?></SPAN>
			</DIV>
			<TABLE style="border-collapse: collapse; width:100%;" border=0>
				<TR>
					<TD colspan=2 class="prop_table_cell_caption">Sinopsis</TD>
				</TR>
				<TR>
					<TD class="prop_table_cell" colspan=2>
						<DIV style="float:left;width:395px;">
						<B>Dirección:</B>&nbsp;&nbsp;<?=$address;?><br>
						<B>Información de contacto:</B>&nbsp;&nbsp;<?=$contact_info;?>
						<script type="text/javascript" src="//yandex.st/share/share.js" charset="utf-8"></script>
						<DIV id="ya_share1" style="margin-top:5px;"></DIV>
						<SCRIPT TYPE="text/javascript">
						<!--
						new Ya.share({
							element: 'ya_share1',
								elementStyle: {
								'type': 'button',
								'border': true,
								'quickServices': ['vkontakte','facebook','twitter','odnoklassniki','lj']
							},
							title: 'MiniGIS- <?=$name;?> <?=$location_name;?>',
							popupStyle: {
								blocks: {
									'Dile A Un Amigo': ['vkontakte','facebook','twitter','odnoklassniki','lj']
								},
								copyPasteField: true
							}
						});
						//-->
						</SCRIPT>
						
						</DIV>
						<IMG SRC="http://static-maps.yandex.ru/1.x/?ll=<?=$coord_y;?>&amp;size=128,128&amp;z=13&amp;l=map&amp;pt=<?=$coord_y;?>,pmwts&amp;key=<?=$yandex_key;?>" ALT="" width=128 height=128 style="float:right;border:0px;">
					</TD>
				</TR>
				<?=$rooms;?>
				<TBODY>
				<TR>
					<TD colspan=2 class="prop_table_cell_caption" id="guest_offer">La propuesta</TD>
				</TR>
				</TBODY>
				<TBODY id="guest_offer_body">
					<TR>
						<TD class="prop_table_cell" style="width:150px;"><DIV class="prop_table_cell_caption">Distancia hasta el mar:</DIV></TD>
						<TD class="prop_table_cell" ><?=$searange;?></TD>
					</TR>
					<TR>
						<TD class="prop_table_cell"><DIV class="prop_table_cell_caption">Еncuentro/despedida:</DIV></TD>
						<TD class="prop_table_cell" ><?=$meeting;?></TD>
					</TR>
					<TR>
						<TD class="prop_table_cell"><DIV class="prop_table_cell_caption">En los alrededores se encuentran:</DIV></TD>
						<TD class="prop_table_cell" ><?=$sights;?></TD>
					</TR>
					<TR>
						<TD class="prop_table_cell"><DIV class="prop_table_cell_caption">Servicios:</DIV></TD>
						<TD class="prop_table_cell" ><?=$services;?></TD>
					</TR>
				</TBODY>
				<TBODY>
					<TR>
						<TD colspan=2 class="prop_table_cell_caption" id="guest_info">Información para huéspedes</TD>
					</TR>
				</TBODY>
				<TBODY id="guest_info_body">
					<TR>
						<TD class="prop_table_cell" style="width:150px;"><DIV class="prop_table_cell_caption">El contenido de habitaciones:</DIV></TD>
						<TD class="prop_table_cell" ><?=$roomwealth;?></TD>
					</TR>
<!--				<TR>
						<TD class="prop_table_cell"><DIV class="prop_table_cell_caption">El número de habitaciones:</DIV></TD>
						<TD class="prop_table_cell" ><?=$num_num;?></TD>
					</TR> -->
					<TR>
						<TD class="prop_table_cell"><DIV class="prop_table_cell_caption">Descripción:</DIV></TD>
						<TD class="prop_table_cell" ><?=$roomdesc;?></TD>
					</TR>
				</TBODY>
			</TABLE>
		</TD>
		<TD style="width:132px;vertical-align:top;padding-top:0px;border-left:1px solid #D6D6D6;">
			<?=$image1;?>
			<? if(strlen($all_images)>0){print $all_images;}?>
		</TD>
	</TR>
	</TABLE>
</DIV>
<SCRIPT TYPE="text/javascript">
<!--
	$('#guest_info').click(function() {
		$('#guest_info_body').slideToggle('slow', function() {});
	});
	$('#guest_offer').click(function() {
		$('#guest_offer_body').slideToggle('slow', function() {});
	});
	$('#room_offer').click(function() {
		$('#room_offer_body').slideToggle('slow', function() {});
	});
//-->
</SCRIPT>