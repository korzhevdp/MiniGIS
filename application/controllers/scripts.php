<?php
class Scripts extends CI_Controller{
	function __construct(){
		parent::__construct();
	}

	public function frontend () {
		$this->load->view("scripts/frontendjs");
	}

	public function mapui () {
		$this->load->view("scripts/mapuijs");
	}

	public function mapcontrols () {
		$this->load->view("scripts/mapcontrols");
	}

	public function dragndrop () {
		$this->load->view("scripts/dragndropjs");
	}

	public function editorui () {
		$this->load->view("scripts/editoruijs");
	}

	public function mapeditor () {
		$this->load->view("scripts/editorjs");
	}

	public function nodal () {
		$this->load->view("scripts/nodaljs");
	}

	public function shedule () {
		$this->load->view("scripts/shedulejs");
	}





}

/* End of file scripts.php */
/* Location: ./system/application/controllers/scripts.php */