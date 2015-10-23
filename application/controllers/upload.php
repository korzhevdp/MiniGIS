<?php
class Upload extends CI_Controller{
	public function __construct(){
		parent::__construct();
		$this->load->model("uploadmodel");
	}

	public function loadimage(){
		$lid = $this->input->post('lid', true);
		$freespace = $this->uploadmodel->get_available_images_number($lid);
		if( $freespace['limit'] <= 0){
			print "data = { image : '', message : '".$freespace['message']."' }";
			return false;
		}

		$upconfig = array(
			'upload_path'		=> $this->config->item("upload_dir"),
			'allowed_types'		=> 'gif|jpg|png|jpeg',
			'max_size'			=> '10000',
			'max_width'			=> '4500',
			'max_height'		=> '3900',
			'encrypt_name'		=> true
		);
		
		$this->load->library('upload', $upconfig);
		if ( $this->upload->do_upload() ) {
			$data  = $this->upload->data();
			$imgX  = $this->config->item("img_sizes");
			$imgid = $this->uploadmodel->insert_image_into_database($lid, $data);
			$this->uploadmodel->check_directories($lid);
			$this->uploadmodel->resize_image($data, $imgX, "small", $lid, $imgid);
			$this->uploadmodel->resize_image($data, $imgX, "mid",   $lid, $imgid);
			$this->uploadmodel->resize_image($data, $imgX, "full",  $lid, $imgid);
		}
		$hash = $this->uploadmodel->get_image_hash((string) $data['raw_name']);
		print "data = { 
			image   : '<li class=\"locationImg\" ref=\"".$hash."\"><img src=\"/uploads/small/".$lid."/".$data['raw_name'].".jpg\"><i class=\"icon-remove icon-white\"></i></li>',
			message : 'OK',
			hash    : '".$hash."'
		}";
	}
}
/* End of file upload.php */
/* Location: ./application/controllers/upload.php */