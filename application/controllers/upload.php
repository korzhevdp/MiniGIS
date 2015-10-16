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
			'upload_path'		=> './uploads/',
			'allowed_types'		=> 'gif|jpg|png|jpeg',
			'max_size'			=> '10000',
			'max_width'			=> '0',
			'max_height'		=> '0',
			'encrypt_name'		=> true
		);
		
		$this->load->library('upload', $upconfig);
		if ( $this->upload->do_upload()) {
			$data = $this->upload->data();
			$hash = $this->uploadmodel->get_image_hash((string) $data['raw_name']);
			$image = array(
				$lid,
				$data['raw_name'].".jpg",
				$data['orig_name'],
				(($this->session->userdata("user_id")) ? $this->session->userdata("user_id") : $this->input->post('upload_user', true) ),
				(strlen($this->input->post('comment', true))) ? substr($this->input->post('comment', true), 0, 200) : "",
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
			$imgid = $this->db->insert_id();
			if(!file_exists('./uploads/small')){
				mkdir('./uploads/small', 0775);
			}
			if(!file_exists('./uploads/mid')){
				mkdir('./uploads/mid', 0775);
			}
			if(!file_exists('./uploads/full')){
				mkdir('./uploads/full', 0775);
			}
			if(!file_exists('./uploads/small/'.$lid)){
				mkdir('./uploads/small/'.$lid, 0775);
			}
			if(!file_exists('./uploads/mid/'.$lid)){
				mkdir('./uploads/mid/'.$lid, 0775);
			}
			if(!file_exists('./uploads/full/'.$lid)){
				mkdir('./uploads/full/'.$lid, 0775);
			}
			$this->uploadmodel->resize_image('./uploads/', $data['raw_name'], 32,  50, './uploads/small/'.$lid.'/', $data['file_ext'], "small", $imgid);
			$this->uploadmodel->resize_image('./uploads/', $data['raw_name'], 128, 50, './uploads/mid/'.$lid.'/'  , $data['file_ext'], "mid",   $imgid);
			$this->uploadmodel->resize_image('./uploads/', $data['raw_name'], 400, 50, './uploads/full/'.$lid.'/' , $data['file_ext'], "full",  $imgid);
		}

		print "data = { 
			image   : '<li class=\"locationImg\" ref=\"".$hash."\"><img src=\"/uploads/small/".$lid."/".$data['raw_name'].".jpg\"><i class=\"icon-remove icon-white\"></i></li>',
			message : 'OK',
			hash    : '".$hash."'
		}";
	}
}
/* End of file upload.php */
/* Location: ./application/controllers/upload.php */