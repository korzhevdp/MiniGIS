<tr <?=$infoclass;?>>
	<td><?=$label;?></td>
	<td><?=$selfname;?></td>
	<td><?=$algoritm;?></td>
	<td><?=$property_group;?></td>
	<td><?=$cat;?></td>
	<td>
		<? if ($object_group) { ?>
		<a href="/admin/swpropsearch/<?=$object_group;?>/<?=$type_id;?>/<?=$id;?>/2" class="btn btn-mini" title="<?=$title1;?>"><img src="<?=$this->config->item('api');?>/images/<?=$pic1;?>" style="width:16px;height:16px;border:none;" alt="статус поиска" ></a>
		<? } ?>
		<a href="/admin/swpropactive/<?=$object_group;?>/<?=$type_id;?>/<?=$id;?>/2" class="btn btn-mini" title="<?=$title2;?>"><img src="<?=$this->config->item('api');?>/images/<?=$pic2;?>" style="width:16px;height:16px;border:none;" alt="статус работы" ></a>
	</td>
	<td>
		<a href="/admin/library/<?=$object_group;?>/<?=$type_id;?>/<?=$id;?>/2" class="btn btn-primary btn-mini">Редактировать</a>
	</td>
</tr>