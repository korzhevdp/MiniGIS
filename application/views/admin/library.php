<div id="library">
	<h4><a href="/<?=$controller;?>/library">Группы объектов:</a>
	<? if ($obj_group) {?>
		<? if($loc_type) { ?>
			 <a href="/<?=$controller;?>/library/<?=$obj_group;?>"><?=$name;?></a> 
			&mdash; <a href="/<?=$controller;?>/library/<?=$obj_group;?>/<?=$loc_type;?>"><?=$type_name;?></a>
		<? }else{ ?>
			 <a href="/<?=$controller;?>/library/<?=$obj_group;?>"><?=$name;?></a>
		<? }
	} ?>
	</h4>
	<ul>
		<?=$library;?>
	</ul>
</div>
