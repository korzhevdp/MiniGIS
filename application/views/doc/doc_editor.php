<STYLE TYPE="text/css">
	#sheets_tree {
		vertical-align: top;
		width:150px;
		padding:2px;
	}
	#sheets_tree_header {
		background-color: #666666;
		color:#FFFF99;
		width:150px;
		border-right: #D6D6D6;
		font-size: 10pt;
	}
	#tree_container {
		background-color:#F0F0F0;
		overflow:auto;
		width:90%;
		height:150px;
		margin-bottom:20px;
		border:1px solid #D6D6D6;
		padding:5px;
		font-size: 8pt;
	}
	.menu_item {
		padding-top: 2px;
		padding-bottom: 2px;
		margin-bottom: 2px;
		border-bottom: 1px solid #DDDDDD;
		font-size: 8pt;
		cursor:pointer;
	}
	.menu_item_container {
		margin-left: 10px;
		background-color: #FFFFFF;
		font-size: 8pt;
	}
	#cke_contents_editor {
		padding-left:6px;
	}
</style>

	<h3>Cтраницы сайта <small>Дерево документов</small></h3>
	<div id="tree_container"><?=$doc_tree;?></div>

	<form method=post action="/admin/doc_save/<?=$doc_id;?>" class="form-horizontal" style="margin-bottom:60px;">
	<fieldset>
		<legend><?=$header?> <small>- Редактирование страницы</small></legend>
		<div class="control-group" style="margin-bottom:3px;">
			<label class="control-label span2" for="sheet_header">Заголовок:</label>
			<div class="controls">
				<input type="text" name="sheet_header" id="sheet_header" class="span6" value='<?=$header?>'>
			</div>
		</div>

		<div class="control-group" style="margin-bottom:3px;">
			<label for="save_new" class="control-label span2">&nbsp;</label>
			<div class="controls">
				<label for="save_new" class="checkbox"><input type="checkbox" name="save_new" id="save_new">Сохранить как новый документ в текущем разделе</label>
			</div>
		</div>

		<div class="control-group" style="margin-bottom:3px;">
			<label for="sheet_redirect" class="control-label span2">Перенаправление:</label>
			<div class="controls">
				<select name="sheet_redirect" class="span6">
					<?=$redirect;?>
				</select>
			</div>
		</div>

		<div class="control-group" style="margin-bottom:3px;">
			<label for="sheet_root" class="control-label span2">Раздел:</label>
			<div class="controls">
				<select name="sheet_root" id="sheet_root" class="span6">
					<option value="1" <?=($root == 1) ? 'selected="selected"': "";?>>Статьи</option>
					<option value="2" <?=($root == 2) ? 'selected="selected"': "";?>>Помощь</option>
				</select>
			</div>
		</div>

		<div class="control-group" style="margin-bottom:3px;">
			<label for="sheet_redirect" class="control-label span2">Комментарий:</label>
			<div class="controls">
				<input type="text" name="sheet_comment" id="sheet_comment" class="span6" value='<?=$comment?>'>
			</div>
		</div>
	
	</fieldset>

	<textarea name="sheet_text" id="editor" style="" rows="1" cols="1"><?=$sheet_text;?></textarea>

	<div class="control-group" style="margin-bottom:3px;">
		<label for="is_active">&nbsp;</label>
		<div class="controls">
			<label for="is_active" class="checkbox">
				<input type="checkbox" name="is_active" id="is_active" value="1" <?=$is_active;?>>&nbsp;&nbsp;Опубликована
			</label>
		</div>
	</div>

	<div class="control-group" style="margin-bottom:3px;">
		<label for="sheet_redirect" class="control-label span2">В разделе:</label>
		<div class="controls">
			<input type="text" class="span6" name="sheet_parent" id="sheet_parent" value="<?=$parent;?>">
		</div>
	</div>
	
	<div class="control-group" style="margin-bottom:3px;">
		<label class="control-label span2">Порядок страницы:</label>
		<div class="controls">
			<input type="text" class="span6" name="pageorder" value="<?=$pageorder;?>">
		</div>
	</div>

	<small class="muted pull-right">Дата публикации: <?=$date;?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Последняя правка: <?=$ts;?></small>
</form>

<script type="text/javascript" src="<?=$this->config->item('api');?>/ckeditor/ckeditor.js"></script>
<script type="text/javascript">
	<!--
	CKEDITOR.replace( 'editor',{
		contentsCss: '<?=$this->config->item('api');?>/bootstrap/css/bootstrap.css',
		skin : 'v2',
		filebrowserBrowseUrl : '/dop/browser/images',
		filebrowserUploadUrl : '/dop/uploader/files',
		format_tags: 'p;h1;h2;h3;h4;h5;h6;pre;address;div;well',
		format_well: { name: 'well', element : 'div', attributes : { 'class' : 'well well-small' } }
	});
	//-->
</script>