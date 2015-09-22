<ul class="nav pull-right">
	<li><img src="http://api.korzhevdp.com/images/flag_<?=$this->session->userdata('lang');?>.png" class="langMark" alt=""></li>
	<li class="divider-vertical"></li>
	<li class="dropdown">
		<a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="icon-user icon-white"></i>&nbsp;<?=$user;?>&nbsp;<b class="caret"></b></a>
		<ul class="dropdown-menu">
			<li><a href="/admin"><i class="icon-cog"></i>&nbsp;Управление системой</a></li>
			<li><a href="/user/user_exit"><i class="icon-ban-circle"></i>&nbsp;Завершить сессию</a></li>
		</ul>
	</li>
</ul>