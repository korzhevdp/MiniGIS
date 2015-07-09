<div class="span9" style="margin-left:0px;">
	<h4>Комментарий к <small><?=$location_name?></small></h4>
</div>

<div class="well span9" id="comm<?=$id;?>" style="margin-left:0px;">
	<span class="label label-success">Автор:</span>&nbsp;&nbsp;
	<span><?=$auth_name;?></span>
	<address><?=$contact_info;?></address>
	<blockquote>
		<?=$text?>
		<small>Написано: <?=$date?></small>
	</blockquote>
<?=$control;?>
</div>