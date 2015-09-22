<div class="well well-small">
	<h3><?=$location_name;?>&nbsp;&nbsp;&nbsp;&nbsp;<small><?=$name;?></small></h3>


	<table>
	<tr>
		<td>
			<img src="<?=$statmap;?>" width="128" height="128" border="0" alt="">
			<?=(isset($all_images) && strlen($all_images)) ? $all_images : "" ;?>
		</td>
		<td>
			<dl class="dl-horizontal">
				<dt>Адрес</dt>
				<dd><address><?=$address;?><address></dd>
				<dt>Контакты</dt>
				<dd><?=$contact;?></dd>
			</dl>
		</td>
	</tr>
	</table>

	<div>
		<?=$content;?>
	</div>

</div>

<?=$this->load->view('frontend/frontend_modal_pic',array(),true);?>
