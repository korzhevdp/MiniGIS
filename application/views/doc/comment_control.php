<div class="commentControl">
<i class="<?=$current;?>" id="cr<?=$id?>"></i>
<button class="btn btn-info btn-mini commentToggle pull-right" style="margin-left:10px;" ref="<?=$id?>" id="s<?=$id;?>" title="<?=$title?>"><i class="icon-eye-<? if ($status === "A") { ?>open<? } else { ?>close<? } ?> icon-white"></i></button>
<button class="btn btn-inverse btn-mini pull-right commentDel" ref="<?=$id?>"><i class="icon-trash icon-white"></i></button>
</div>