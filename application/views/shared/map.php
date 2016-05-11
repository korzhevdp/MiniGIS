<!DOCTYPE html>
<head>
<title> <?=$title;?></title>
<meta name='yandex-verification' content='74872298f6a53977' />
<?=$this->load->view("shared/shared_js_css");?>
</head>
<body>
<!-- навигацыя -->
	<div class="navbar navbar-inverse">
		<div class="navbar-inner">
			<?=$brand.$menu;?>
		</div>
	</div>
	<div class="well span4 map_name"><?=$map_header;?></div>
<!-- навигацыя -->
	<table class="main_page_body" id="main_table">
		<tr>
			<td id="YMapsID"><!-- сам текст -->
				<?=$this->load->view("shared/navigator");?>
			</td>
		</tr>
	</table>

	<!-- Modal -->
	<?=$this->load->view($this->session->userdata('lang')."/frontend/modals");?>
	<!-- Modal -->

<?=$mapconfig;?>

<script type="text/javascript" src="/scripts/frontend"></script>
<script type="text/javascript" src="/scripts/mapui"></script>
<?=$footer;?>

</body>
</html>