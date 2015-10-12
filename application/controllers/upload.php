<?php
class Upload extends CI_Controller{

	public function __construct(){
		parent::__construct();
		//$this->load->helper('url');
		//$this->output->enable_profiler(TRUE);
	}

	public function resize_image($path, $file, $m_dim, $quality, $newpath, $type, $sizedef, $imgid){
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
			$h_new    = round($m_dim * $size['1']/$size['0']);
			$measures = $m_dim.",".$h_new;
			$new      = ImageCreateTrueColor ($m_dim, $h_new);
			ImageCopyResampled($new, $image, 0, 0, 0, 0, $m_dim, $h_new, $size['0'], $size['1']);
		}
		if($size['1'] >= $size['0']){
			$h_new    = round($m_dim * $size['0']/$size['1']);
			$measures = $h_new.",".$m_dim;
			$new      = ImageCreateTrueColor ($h_new, $m_dim);
			ImageCopyResampled($new, $image, 0, 0, 0, 0, $h_new, $m_dim, $size['0'], $size['1']);
		}
		imageJpeg($new, $newpath.$file.'.jpg', $quality);
		//header("content-type: image/jpeg");// активировать для отладки
		//imageJpeg($new, "", 100);//активировать для отладки
		$this->db->query("UPDATE `images` SET `images`.`".$sizedef."` = ? WHERE `images`.`id` = ?", array($measures, $imgid));
		imageDestroy($new);
	}

	public function loadimage(){
		$config['upload_path']		= './uploads/';
		$config['allowed_types']	= 'gif|jpg|png|jpeg';
		$config['max_size']			= '10000';
		$config['max_width']		= '0';
		$config['max_height']		= '0';
		$config['encrypt_name']		= true;
		$this->load->library('upload', $config);
		if ( ! $this->upload->do_upload()) {
			//$error = array('error' => $this->upload->display_errors());
			//$this->load->view('upload_form', $error);
			//redirect($this->input->post('upload_from', true));
			//print "error<br><br>".$this->upload->display_errors();
			//print_r($_FILES);
		} else {
			$data = $this->upload->data();
			$image = array(
				$this->input->post('lid', true),
				$data['raw_name'].".jpg",
				$data['orig_name'],
				(($this->session->userdata("user_id")) ? $this->session->userdata("user_id") : $this->input->post('upload_user', true) ),
				(strlen($this->input->post('comment'))) ? substr($this->input->post('comment'),0,200) : "",
				1 //($this->input->post('upload_from') == 'frontend') ? 0 : 1
			);

			$result = $this->db->query("INSERT INTO `images` (
			`images`.`location_id`,
			`images`.`filename`,
			`images`.`orig_filename`,
			`images`.`owner_id`,
			`images`.`comment`,
			`images`.`active`
			) VALUES ( ?, ?, ?, ?, ?, ? )", $image);
			$imgid = $this->db->insert_id();
			$this->resize_image('./uploads/', $data['raw_name'], 32,  50, './uploads/small/', $data['file_ext'], "small", $imgid);
			$this->resize_image('./uploads/', $data['raw_name'], 128, 50, './uploads/mid/'  , $data['file_ext'], "mid",   $imgid);
			$this->resize_image('./uploads/', $data['raw_name'], 400, 50, './uploads/full/' , $data['file_ext'], "full",  $imgid);
			//print_r($image) ;
		}
		print '<li class="locationImg"><img src="/uploads/small/'.$data['raw_name'].'.jpg"></li>';
	}
}
/* End of file upload.php */
/* Location: ./application/controllers/upload.php */