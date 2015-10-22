<?php
class Uploadmodel extends CI_Model{
	function __construct(){
		parent::__construct();
	}

	
	public function get_available_images_number($location){
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
			
			$limit = ($row->paid) ? $this->config->item("image_paid_limit"): $this->config->item("image_limit");
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

	public function get_image_hash($imgname){
		$hash = substr(base64_encode($imgname), 6, 16);
		return $hash;
	}

	public function resize_image($path, $file, $max_dim, $quality = 75, $newpath, $type, $sizedef, $imgid){
		if(strtolower($type) === ".jpg" || strtolower($type) === ".jpeg"){
			$image=ImageCreateFromJpeg($path.$file.$type);
		}
		elseif(strtolower($type) === ".png"){
			$image=ImageCreateFromPng($path.$file.$type);
		}
		elseif(strtolower($type) === ".gif"){
			$image=ImageCreateFromGif($path.$file.$type);
		}else{
			return false;
		}
		$size = GetImageSize($path.$file.$type);
		$old = $image;//форк - не просто так.
		if($size['1'] < $size['0']){
			$h_new    = round($max_dim * $size['1']/$size['0']);
			$measures = $max_dim.",".$h_new;
			$new      = ImageCreateTrueColor ($max_dim, $h_new);
			ImageCopyResampled($new, $image, 0, 0, 0, 0, $max_dim, $h_new, $size['0'], $size['1']);
		}
		if($size['1'] >= $size['0']){
			$h_new    = round($max_dim * $size['0']/$size['1']);
			$measures = $h_new.",".$max_dim;
			$new      = ImageCreateTrueColor ($h_new, $max_dim);
			ImageCopyResampled($new, $image, 0, 0, 0, 0, $h_new, $max_dim, $size['0'], $size['1']);
		}
		imageJpeg($new, $newpath.$file.'.jpg', $quality);
		//header("content-type: image/jpeg");// активировать для отладки
		//imageJpeg($new, "", 100);//активировать для отладки
		$this->db->query("UPDATE `images` SET `images`.`".$sizedef."` = ? WHERE `images`.`id` = ?", array($measures, $imgid));
		imageDestroy($new);
	}
}
	#
/* End of file uploadmodel.php */
/* Location: .application/models/uploadmodel.php */