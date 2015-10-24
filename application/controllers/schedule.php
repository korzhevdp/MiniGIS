<?php
class Schedule extends CI_Controller {
	function __construct() {
		parent::__construct();
	}

	### Daily Routine
	public function get_schedule() {
		$input = array();
		$schedule =	array();
		$result	= $this->db->query("SELECT
		DATE_FORMAT(`timers_week`.`start`, '%H:%i')	as start,
		DATE_FORMAT(`timers_week`.`end`, '%H:%i') as end,
		`timers_week`.`location_id`,
		`timers_week`.`day`
		FROM
		`timers_week`
		WHERE `timers_week`.`location_id` =	?
		ORDER BY `timers_week`.`day`, `timers_week`.`start`", array($this->input->post('lid')));
		if ($result->num_rows()) {
			foreach($result->result() as $row) {
				if (!isset($input[$row->day])) {
					$input[$row->day] =	array();
				}
				array_push($input[$row->day], "'".$row->start."'");
				array_push($input[$row->day], "'".$row->end."'");
			}
			foreach	($input	as $key=>$val) {
				array_push($schedule, "\t".$key." :	[ ".implode($val, ", ")." ]");
			}
		}
		print "shedule = {\n".implode($schedule, ",\n")."\n}";
	}

	public function save_sñhedule() {
		$output = array();
		foreach ($this->input->post('shedule', true) as $key => $val) {
			if ($this->input->post("h24")) {
				$val = array( '00:00:00','12:00:00','12:00:00','23:59:59' );
			}
			$string = "('".$this->db->escape_str($val[0])."', '".$this->db->escape_str($val[1])."', '".$this->db->escape_str($this->input->post("lid", true))."', ".$this->db->escape_str($key)."),\n('".$this->db->escape_str($val[2])."', '".$this->db->escape_str($val[3])."', '".$this->db->escape_str($this->input->post("lid",	true))."', ".$this->db->escape_str($key).")";
			array_push($output,	$string);
		}
		$this->db->query("DELETE FROM timers_week WHERE timers_week.location_id = ?", array($this->input->post("lid", true)));
		$result = $this->db->query("INSERT INTO
		`timers_week`(
			`timers_week`.`start`,
			`timers_week`.`end`,
			`timers_week`.`location_id`,
			`timers_week`.`day`
		)
		VALUES ".implode($output, ",\n"));
	}
	### Daily Routine

	
}

/* End of file schedule.php */
/* Location: ./system/application/controllers/schedule.php */