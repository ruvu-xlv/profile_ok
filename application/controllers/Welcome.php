<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		date_default_timezone_set('Asia/Jakarta');

	// 	$this->load->model('m_data');
	// }

	// public function index()
	// {
	// // 	//3 artikel terbaru
	 $data['artikel']= $this->db->query("select * from artikel, pengguna, kategori where artikel_status='publish' and artikel_author=pengguna_id and artikel_kategori=kategori_id order by artikel_id desc limit 3")->result();

	 //data pengaturan website
		$data['pengaturan']= $this->m_data->get_data('pengaturan')->row();

	//SEO meta
		$data['meta_keywoard']=$data['pengaturan']->nama;
		$data['meta_description']=$data['pengaturan']->deskripsi;

		 $this->load->view('frontend/v_header', $data);
		 $this->load->view('frontend/v_homepage', $data);
		 $this->load->view('frontend/v_footer', $data);
	}
}
