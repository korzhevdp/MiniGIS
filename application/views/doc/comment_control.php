<div class="control">
<span class="span12" id="stat<?=$id;?>"><? if ($status == "A") { ?> <span class="label label-success">Показывается</span> <? }else{ ?> <span class="label label-warning">Не показывается</span><? } ?></span>
<button class="btn btn-info" onclick="swc('<?=$id?>','show')" id="s<?=$id;?>" <? if ($status == "A") { ?> style="display:none;" <? } ?>><i class="icon-eye-open icon-white"></i>&nbsp;Показывать</button>

<button class="btn btn-success" onclick="swc('<?=$id?>','hide')" id="h<?=$id;?>" <? if ($status == "N") { ?> style="display:none;" <? } ?>><i class="icon-eye-close icon-white"></i>&nbsp;Скрыть</button>

<button onclick="swc('<?=$id?>','del')" class="btn btn-inverse"><i class="icon-trash icon-white"></i>&nbsp;Удалить</button>
</div>