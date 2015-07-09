<DIV class="headerrow">Список помещений, зарегистрированных для этого размещения.</DIV>
<?=$places_list;?>
<SCRIPT TYPE="text/javascript">
<!--
	function show_info_table(node){
		var list=document.getElementById('locations_ids').value.split(',');
		for(a in list){
			if(document.getElementById('info_table_' + list[a]).style.display=='block'){
				$('#info_table_' + list[a]).slideUp('slow', function(){});
				if(list[a]==node){
					return;
				}
			}
		}
		$('#info_table_' + node).slideDown('slow');
	}
//-->
</SCRIPT>