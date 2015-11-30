<?php
class Sitedown extends CI_Controller {
	function __construct(){
		parent::__construct();
		
	}
	
	public function index(){
		print "<strong>Site is closed for a while due to maintenance reasons</strong>.<br>This would take a time. Please wait.<br><br>We are doing our best in:
		<ul>
			<li>Optimizing database structure and performance;</li>
			<li>Adding new features and fixing errors;</li>
			<li>Creating enchanting UIs.</li>
		</ul>
		And so on...";
	}
}

/* End of file gis.php */
/* Location: ./system/application/controllers/gis.php */