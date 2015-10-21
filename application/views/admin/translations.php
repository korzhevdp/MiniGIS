<style type="text/css">
	th img{
		width:32px;
		height:32px;
		border: none;
		margin-right: 10px;
	}
	#importText {
		width        : 650px;
		height       : 250px;
		border       : 2px solid #ccc;
		margin-right : 10px;
	}
	td input[type=text] {
		margin-bottom :0px;
	}
</style>

<h3>Переводы названий&nbsp;&nbsp;&nbsp;&nbsp;<small>группы объектов, свойства, категории</small></h3>
<ul class="nav nav-pills" style="clear:both;">
	<li<? if ($mode === "groups")     { ?> class="active"<? } ?>><a href="/admin/translations/groups">Группы объектов</a></li>
	<li<? if ($mode === "categories") { ?> class="active"<? } ?>><a href="/admin/translations/categories">Категории</a></li>
	<li<? if ($mode === "properties") { ?> class="active"<? } ?>><a href="/admin/translations/properties">Свойства</a></li>
	<li<? if ($mode === "labels")     { ?> class="active"<? } ?>><a href="/admin/translations/labels">Метки</a></li>
	<li<? if ($mode === "articles")   { ?> class="active"<? } ?>><a href="/admin/translations/articles">Статьи</a></li>
	<li<? if ($mode === "maps")       { ?> class="active"<? } ?>><a href="/admin/translations/maps">Карты</a></li>
</ul>

	<form method="post" action="/admin/trans_save">
	<table class="table table-condensed table-bordered">
		<?=$table;?>
	</table>
	<input  type="hidden" name="type" value="<?=$mode?>">
	<button type="submit" class="btn btn-small">Упаковать</button>
	</form>
	
	Шаблон языков:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" id="langOrder" value="index,ru,en,de,es">
	<button type="button" id="import" class="btn btn-warning">Импорт</button>
	<button type="button" id="export" class="btn btn-primary">Экспорт</button><br>
	Файл с переводами: <textarea id="importText" rows="2" cols="2"></textarea>
<script type="text/javascript" charset="utf-8">
<!--
	/*
	Экспорт таблицы переводов
	*/
	$("#export").click(function() {
		var input = [],
			output = [],
			langs  = {},
			order  = $("#langOrder").val().split(","),
			i;
		for ( i in order){
			if(order.hasOwnProperty(i)){
				langs[order[i]] = parseInt(i, 10);
			}
		}

		$(".translation").each(function(){
			var ref = $(this).attr('ref'),
				element = langs[$(this).attr('lang')];
			if(input[ref] === undefined){
				input[ref] = [ ref ];
			}
			input[ref][element] = $(this).val();
		});

		for (i in input){
			if(input.hasOwnProperty(i)){
				output.push(input[i].join(","));
			}
		}
		$("#importText").val(output.join("\n"));
	});
	/*
	Импорт таблицы переводов
	*/
	$("#import").click(function() {
		var input  = $("#importText").val().replace(/\t/g, ",").split("\n"),
			order  = $("#langOrder").val().split(","),
			langs  = {},
			i,
			a,
			stream;
		//console.log(input)
		for (i in order){
			if(order.hasOwnProperty(i)){
				langs[parseInt(i, 10)] = order[i];
			}
		}
		for (a in input){
			groupID = input[a][0];
			stream  = input[a].split(",");
			for (i in stream) {
				if(stream.hasOwnProperty(i)){
					$(".translation[lang='" + langs[i] + "'][ref='" + groupID + "']").val(stream[i]);
				}
			}
		}
	});
//-->
</script>