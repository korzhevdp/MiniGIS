<?php
class Uploadmodel extends CI_Model{
	function __construct(){
		parent::__construct();
	}

	public function get_available_images_number($location) {
		$limit   = 0;
		$message = "";
		$result  = $this->db->query("SELECT 
		COUNT(images.id) AS imgnumber,
		IFNULL((SELECT payments.paid FROM payments WHERE (payments.location_id = ?) AND (payments.`end` > NOW())), 0) AS paid
		FROM
		images
		WHERE
		(images.location_id = ?) 
		LIMIT 1", array($location, $location));
		if($result->num_rows()){
			$row = $result->row(0);
			$limit = ($row->paid) ? $this->config->item("image_paid_limit") : $this->config->item("image_limit");
			$rest  = ($limit - $row->imgnumber);
			if ($row->paid && $rest <= 0 ) {
				$message = "Topmost limit of images reached";
			}
			if (!$row->paid && $rest <= 0 ) {
				if ($row->imgnumber >= $this->config->item("image_paid_limit")) {
					$message = "No more images allowed to upload. Sorry...";
				}
			}
		}
		return array(
			'limit'   => $rest,
			'message' => ($rest > 0) ? "OK" : $message
		);
	}

	public function get_image_hash($imgname) {
		$hash = substr(base64_encode($imgname), 6, 16);
		return $hash;
	}

	private function create_image_container($file, $type) {
		$path = $this->config->item("upload_dir");
		if (strtolower($type) === ".jpg" || strtolower($type) === ".jpeg") {
			$image = ImageCreateFromJpeg($path.$file.$type);
		}
		elseif (strtolower($type) === ".png") {
			$image = ImageCreateFromPng($path.$file.$type);
		}
		elseif (strtolower($type) === ".gif") {
			$image = ImageCreateFromGif($path.$file.$type);
		} else {
			$image = false;
		}
		return $image;
	}

	public function resize_image($data, $imgX, $sizedef, $lid, $imgid) {
		$file    = $data["raw_name"];
		$type    = $data["file_ext"];
		$outfile = $imgX[$sizedef]['dir']."/".$lid."/".$file.'.jpg';
		$path    = $this->config->item("upload_dir");
		$image   = $this->create_image_container($file, $type);
		if ($image) {
			$size = GetImageSize($path.$file.$type);
			$old  = $image; // сей форк - не просто так. непонятно, правда, почему...
			if ($size['1'] < $size['0']) {
				$h_new    = round($imgX[$sizedef]['max_dim'] * ($size['1'] / $size['0']));
				$measures = array($imgX[$sizedef]['max_dim'], $h_new);
			}
			if ($size['1'] >= $size['0']) {
				$h_new    = round($imgX[$sizedef]['max_dim'] * ($size['0'] / $size['1']));
				$measures = array($h_new, $imgX[$sizedef]['max_dim']);
			}
			$new = ImageCreateTrueColor($measures[0], $measures[1]);
			ImageCopyResampled($new, $image, 0, 0, 0, 0, $measures[0], $measures[1], $size['0'], $size['1']);
			imageJpeg($new, $outfile, $imgX[$sizedef]['quality']);
			$this->db->query("UPDATE `images` SET `images`.`".$sizedef."` = ? WHERE `images`.`id` = ?", array(implode($measures, ","), $imgid));
			imageDestroy($new);
		}
	}

	public function check_directories($lid) {
		if(!file_exists('./uploads/small')) {
			mkdir('./uploads/small', 0775);
		}
		if(!file_exists('./uploads/mid')) {
			mkdir('./uploads/mid', 0775);
		}
		if(!file_exists('./uploads/full')) {
			mkdir('./uploads/full', 0775);
		}
		if(!file_exists('./uploads/small/'.$lid)) {
			mkdir('./uploads/small/'.$lid, 0775);
		}
		if(!file_exists('./uploads/mid/'.$lid)) {
			mkdir('./uploads/mid/'.$lid, 0775);
		}
		if(!file_exists('./uploads/full/'.$lid)) {
			mkdir('./uploads/full/'.$lid, 0775);
		}
	}

	public function insert_image_into_database($lid, $data){
		$hash = $this->uploadmodel->get_image_hash((string) $data['raw_name']);
		$image = array(
			$lid,
			$data['raw_name'].".jpg",
			$data['orig_name'],
			(($this->session->userdata("user_id"))			? $this->session->userdata("user_id")					: $this->input->post('upload_user', true) ),
			(strlen($this->input->post('comment', true)))	? substr($this->input->post('comment', true), 0, 200)	: "",
			1,
			$hash
		);
		$result = $this->db->query("INSERT INTO `images` (
		`images`.`location_id`,
		`images`.`filename`,
		`images`.`orig_filename`,
		`images`.`owner_id`,
		`images`.`comment`,
		`images`.`active`,
		`images`.`hash`
		) VALUES ( ?, ?, ?, ?, ?, ?, ? )", $image);
		return $this->db->insert_id();
	}
}
#
/* End of file uploadmodel.php */
/* Location: .application/models/uploadmodel.php */