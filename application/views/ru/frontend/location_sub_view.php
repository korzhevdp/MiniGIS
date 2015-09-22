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
								<B>Название:</B>&nbsp;<?=$name;?>&nbsp;<?=$location_name;?><br>
								<B>Адрес:</B> <?=$address;?><br>
								<B>Контактная информация:</B> <?=$contact_info;?>
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
									title: 'Курорт Лазаревское - <?=$name;?> <?=$location_name;?>',
									popupStyle: {
										blocks: {
											'Расскажи Другу': ['vkontakte','facebook','twitter','odnoklassniki','lj']
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
							<TD class="prop_table_cell_caption" colspan=2 id="guest_desc">Описание:</TD>
						</TR>
						<TBODY id="guest_desc_body">
						<TR>
							<TD class="prop_table_cell" style="width:150px;">
								<DIV class="prop_table_cell_caption">Тип номера / комнаты:</DIV>
							</TD>
							<TD class="prop_table_cell" ><?=$type;?></TD>
						</TR>

					<TR>
						<TD class="prop_table_cell" style="width:150px;"><DIV class="prop_table_cell_caption">Содержание номеров:</DIV></TD>
						<TD class="prop_table_cell" ><?=$roomwealth;?></TD>
					</TR>
					<TR>
						<TD class="prop_table_cell"><DIV class="prop_table_cell_caption">Описание:</DIV></TD>
						<TD class="prop_table_cell" ><?=$roomdesc;?></TD>
					</TR>

						<TR>
							<TD class="prop_table_cell"><DIV class="prop_table_cell_caption">Площадь:</DIV></TD>
							<TD class="prop_table_cell" ><?=$square;?>&nbsp;м.<SUP>2</SUP></TD>
						</TR>
						<TR>
							<TD class="prop_table_cell"><DIV class="prop_table_cell_caption">Места:</DIV></TD>
							<TD class="prop_table_cell" >Основные: <?=$bas_place;?><br>Дополнительные: <?=$add_place;?></TD>
						</TR>
						<TR>
							<TD class="prop_table_cell"><DIV class="prop_table_cell_caption">Вид из окна:</DIV></TD>
							<TD class="prop_table_cell" ><?=$view;?></TD>
							</TR>
						<TR>
							<TD class="prop_table_cell"><DIV class="prop_table_cell_caption">Оснащение номеров:</DIV></TD>
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