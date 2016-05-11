<strong>Точность в знаках после запятой</strong>
<br>
<input type="text" id="tolerance" maxlength=1 value="4">&nbsp;&nbsp;&nbsp;(~ <span id="sigma">X</span> метров на местности)<br>
<strong>Тип объекта для улавливания</strong>
<br>
<select id="typeToTrap">
	<option value="0">Выберите тип объекта</option>
	<?=$pointstypes?>
</select><br>
<button type="button" id="requestTrapping" class="btn btn-primary btn-small">Запустить улавливание</button>
<hr>
<strong>Найденные точки:</strong>
<ul id="trappedPoints">

</ul>
