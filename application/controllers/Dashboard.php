<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller{
	function __construct()
	{
		parent::__construct();
		date_default_timezone_set('Asia/Jakarta');
		$this->load->model('m_data');

		if($this->session->userdata('status')!="telah_login"){
			redirect(base_url().'login?alert=belum_login');
		}
	}

	public function keluar()
	{
		$this->session->sess_destroy();
		redirect('login?alert=logout');
	}

	public function index()
	{
		// hitung jumlah artikel
		$data['jumlah_artikel'] = $this->m_data->get_data('artikel')->num_rows();
		// hitung jumlah kategori
		$data['jumlah_kategori'] = $this->m_data->get_data('kategori')->num_rows();
		// hitung jumlah pengguna
		$data['jumlah_pengguna'] = $this->m_data->get_data('pengguna')->num_rows();
		// hitung jumlah halaman
		$data['jumlah_halaman'] = $this->m_data->get_data('halaman')->num_rows();
		$this->load->view('dashboard/v_header');
		$this->load->view('dashboard/v_index',$data);
		$this->load->view('dashboard/v_footer');
	}


	// CRUD KATEGORI
	public function kategori()
	{
		$data['kategori'] = $this->m_data->get_data('kategori')->result();
		$this->load->view('dashboard/v_header');
		$this->load->view('dashboard/v_kategori',$data);
		$this->load->view('dashboard/v_footer');
	}

	public function kategori_tambah()
	{
		$this->load->view('dashboard/v_header');
		$this->load->view('dashboard/v_kategori_tambah');
		$this->load->view('dashboard/v_footer');
	}

	public function kategori_aksi()
	{
		$this->form_validation->set_rules('kategori','Kategori','required');

		if($this->form_validation->run() != false){

			$kategori = $this->input->post('kategori');

			$data = array(
				'kategori_nama' => $kategori,
				'kategori_slug' => strtolower(url_title($kategori))
			);

			$this->m_data->insert_data($data,'kategori');

			redirect(base_url().'dashboard/kategori');
			
		}else{
			$this->load->view('dashboard/v_header');
			$this->load->view('dashboard/v_kategori_tambah');
			$this->load->view('dashboard/v_footer');
		}
	}

	public function kategori_edit($id)
	{
		$where = array(
			'kategori_id' => $id
		);
		$data['kategori'] = $this->m_data->edit_data($where,'kategori')->result();
		$this->load->view('dashboard/v_header');
		$this->load->view('dashboard/v_kategori_edit',$data);
		$this->load->view('dashboard/v_footer');
	}

	public function kategori_update()
	{
		$this->form_validation->set_rules('kategori','Kategori','required');

		if($this->form_validation->run() != false){

			$id = $this->input->post('id');
			$kategori = $this->input->post('kategori');

			$where = array(
				'kategori_id' => $id
			);

			$data = array(
				'kategori_nama' => $kategori,
				'kategori_slug' => strtolower(url_title($kategori))
			);

			$this->m_data->update_data($where, $data,'kategori');

			redirect(base_url().'dashboard/kategori');
			
		}else{

			$id = $this->input->post('id');
			$where = array(
				'kategori_id' => $id
			);
			$data['kategori'] = $this->m_data->edit_data($where,'kategori')->result();
			$this->load->view('dashboard/v_header');
			$this->load->view('dashboard/v_kategori_edit',$data);
			$this->load->view('dashboard/v_footer');
		}
	}


	public function kategori_hapus($id)
	{
		$where = array(
			'kategori_id' => $id
		);

		$this->m_data->delete_data($where,'kategori');

		redirect(base_url().'dashboard/kategori');
	}
	// END CRUD KATEGORI

	// CRUD Artikel
	public function artikel()
	{
		$data['artikel'] = $this->db->query("SELECT * FROM artikel,kategori,pengguna WHERE artikel_kategori=kategori_id and artikel_author=pengguna_id order by artikel_id desc")->result();
		$this->load->view('dashboard/v_header');
		$this->load->view('dashboard/v_artikel',$data);
		$this->load->view('dashboard/v_footer');
	}

	public function artikel_tambah()
	{
		$data['kategori']=$this->m_data->get_data('kategori')->result();
		$this->load->view('dashboard/v_header');
		$this->load->view('dashboard/v_artikel_tambah',$data);
		$this->load->view('dashboard/v_footer');
	}

	public function artikel_aksi()
	{
		// Wajib isi judul,konten dan kategori
		$this->form_validation->set_rules('judul','Judul','required|is_unique[artikel.artikel_judul]');
		$this->form_validation->set_rules('konten','Konten','required');
		$this->form_validation->set_rules('kategori','Kategori','required');


		// Membuat gambar wajib di isi
		if (empty($_FILES['sampul']['name'])){
			$this->form_validation->set_rules('sampul', 'Gambar Sampul', 'required');
		}

		if($this->form_validation->run() != false){

			$config['upload_path']   = './gambar/artikel/';
			$config['allowed_types'] = 'gif|jpg|png';

			$this->load->library('upload', $config);

			if ($this->upload->do_upload('sampul')) {

				// mengambil data tentang gambar
				$gambar = $this->upload->data();

				$tanggal = date('Y-m-d H:i:s');
				$judul = $this->input->post('judul');
				$slug = strtolower(url_title($judul));
				$konten = $this->input->post('konten');
				$sampul = $gambar['file_name'];
				$author = $this->session->userdata('id');
				$kategori = $this->input->post('kategori');
				$status = $this->input->post('status');

				$data = array(
					'artikel_tanggal' => $tanggal,
					'artikel_judul' => $judul,
					'artikel_slug' => $slug,
					'artikel_konten' => $konten,
					'artikel_sampul' => $sampul,
					'artikel_author' => $author,
					'artikel_kategori' => $kategori,
					'artikel_status' => $status,
				);

				$this->m_data->insert_data($data,'artikel');

				redirect(base_url().'dashboard/artikel');	
				
			} else {

				$this->form_validation->set_message('sampul', $data['gambar_error'] = $this->upload->display_errors());

				$data['kategori'] = $this->m_data->get_data('kategori')->result();
				$this->load->view('dashboard/v_header');
				$this->load->view('dashboard/v_artikel_tambah',$data);
				$this->load->view('dashboard/v_footer');
			}

		}else{
			$data['kategori'] = $this->m_data->get_data('kategori')->result();
			$this->load->view('dashboard/v_header');
			$this->load->view('dashboard/v_artikel_tambah',$data);
			$this->load->view('dashboard/v_footer');
		}
	}


	public function artikel_edit($id)
	{
		$where = array(
			'artikel_id' => $id
		);
		$data['artikel'] = $this->m_data->edit_data($where,'artikel')->result();
		$data['kategori']=$this->m_data->get_data('kategori')->result();
		$this->load->view('dashboard/v_header');
		$this->load->view('dashboard/v_artikel_edit',$data);
		$this->load->view('dashboard/v_footer');
	}

	public function artikel_update()
	{
		$this->form_validation->set_rules('judul','Judul','required');
		$this->form_validation->set_rules('konten','Konten','required');
		$this->form_validation->set_rules('kategori','Kategori','required');
		
		if($this->form_validation->run() != false)
		{
			$id = $this->input->post('id');

			$judul = $this->input->post('judul');
			$slug = strtolower(url_title($judul));
			$konten = $this->input->post('konten');
			$kategori = $this->input->post('kategori');
			$status = $this->input->post('status');
			
			$where = array(
				'artikel_id'=>$id 
			);

			$data = array(
				'artikel_judul'=>$judul,
				'artikel_slug'=>$slug,
				'artikel_konten'=>$konten,
				'artikel_kategori'=>$kategori,
				'artikel_status'=>$status,
			);

			$this->m_data->update_data($where,$data,'artikel');

			if(!empty($_FILES['sampul']['nama'])){
				$config['upload_path']='./gambar/artikel/';
				$config['allowed_type']='gif|jpg|png';

				$this->load->library('upload',$config);

				if ($this->upload->do_upload('sampul')) {
					// mengambil data tentang gambar
					$gambar=$this->upload->data();

					$data=array(
						'artikel_sampul'=>$gambar['file_name'],
				);
					$this->m_data->update_data($where,$data,'artikel');
					redirect(base_url().'dashboard/artikel');
				}else {
					$this->form_validation->set_message('sampul',$data['gambar_error'] = $this->upload->display_error());
					
					$where = array(
						'artikel_id'=>$id
					);

					$data['artikel'] = $this->m_data->edit_data($where,'artikel')->result();
					$data['kategori']=$this->m_data->get_data('kategori')->result();
					$this->load->view('dashboard/v_header');
					$this->load->view('dashboard/v_artikel_edit',$data);
					$this->load->view('dashboard/v_footer');
				}

			}else{
				redirect(base_url().'dashboard/artikel');
			}
		}else {
			$id = $this->input->post('id');
			
			$where = array(
				'artikel_id'=>$id 
			);
		
			$data['artikel'] = $this->m_data->edit_data($where,'artikel')->result();
			$data['kategori'] = $this->m_data->get_data('kategori')->result();
			$this->load->view('dashboard/v_header');
			$this->load->view('dashboard/v_artikel_edit',$data);
			$this->load->view('dashboard/v_footer');
		}
	}


	public function artikel_hapus($id)
	{
		$where = array(
			'artikel_id' => $id
		);

		$this->m_data->delete_data($where,'artikel');

		redirect(base_url().'dashboard/artikel');
	}
	// END CRUD ARTIKEL


	//CRUD PAGES
	public function pages(){
		$data['halaman'] = $this->m_data->get_data('halaman')->result();
		$this->load->view('dashboard/v_header');
		$this->load->view('dashboard/v_pages', $data);
		$this->load->view('dashboard/v_footer');
	}

	public function pages_tambah(){
		$this->load->view('dashboard/v_header');
		$this->load->view('dashboard/v_pages_tambah');
		$this->load->view('dashboard/v_footer');
	}

	public function pages_aksi()
	{
		$this->form_validation->set_rules('judul','Judul','required|is_unique[halaman.halaman_judul]');
		$this->form_validation->set_rules('konten','Konten','required');
		if($this->form_validation->run() != false)
		{
			
			$judul = $this->input->post('judul');
			$slug = strtolower(url_title($judul));
			$konten = $this->input->post('konten');

			$data = array(
				'halaman_judul'=>$judul,
				'halaman_slug'=>$slug,
				'halaman_konten'=>$konten
			);
			$this->m_data->insert_data($data, 'halaman');
			//alihkan kembali ke method pages
			redirect(base_url().'dashboard/pages');
		}else{
			$this->load->view('dashboard/v_header');
			$this->load->view('dashboard/v_pages_tambah');
			$this->load->view('dashboard/v_footer');
		}
	}
	public function pages_edit($id)
	{
		$where = array(
			'halaman_id '=> $id
		);
		$data['halaman'] = $this->m_data->edit_data($where,'halaman')->result();
		
		$this->load->view('dashboard/v_header');
		$this->load->view('dashboard/v_pages_edit',$data);
		$this->load->view('dashboard/v_footer');
	}
	 public function pages_update()
	 {
	 	$this->form_validation->set_rules('judul','Judul','required');
		$this->form_validation->set_rules('konten','Konten','required');
		if($this->form_validation->run() != false)
		{
			$id = $this->input->post('id');
			$judul = $this->input->post('judul');
			$slug = strtolower(url_title($judul));
			$konten = $this->input->post('konten');

			$where = array(
				'halaman_id'=>$id
			);

			$data = array(
				'halaman_judul'=>$judul,
				'halaman_slug'=>$slug,
				'halaman_konten'=>$konten
			);

			$this->m_data->update_data($where,$data,'halaman');
			redirect(base_url().'dashboard/pages');
		}else{
			$id = $this->input->post('id');
			$where = array(
				'halaman_id'=>$id
			);
			$data['halaman']=$this->m_data->edit_data($where,'halaman')->result();
			$this->load->view('dashboard/v_header');
			$this->load->view('dashboard/v_pages_edit',$data);
			$this->load->view('dashboard/v_footer');
		}
	 }
	 public function pages_hapus($id)
	{
		$where = array(
			'halaman_id' => $id
		);

		$this->m_data->delete_data($where,'halaman');

		redirect(base_url().'dashboard/pages');
	}



	public function profile(){
		//id pengguna yang sedang login
		$id_pengguna=$this->session->userdata('id');

		$where = array(
			'pengguna_id' => $id_pengguna
		);

		$data['profile'] = $this->m_data->edit_data($where,'pengguna')->result();
		$this->load->view('dashboard/v_header');
		$this->load->view('dashboard/v_profile',$data);
		$this->load->view('dashboard/v_footer');
	}

	public function profile_update(){
		//wajib isi nama dan email
		$this->form_validation->set_rules('nama','Nama','required');
		$this->form_validation->set_rules('email','Email','required');

		if ($this->form_validation->run() !=false){
			$id = $this->session->userdata('id');
			$nama = $this->input->post('nama');
			$email = $this->input->post('email');

			$where = array(
			'pengguna_id' => $id
		);
		
		$data = array(
			'pengguna_nama'=>$nama,
			'pengguna_email'=>$email
		);
		$this->m_data->update_data($where,$data,'pengguna');
		redirect(base_url().'dashboard/profile/?alert=sukses');
	}else{
		//id pengguna yang sedang login
		$id_pengguna = $this->session->userdata('id');
		$where = array(
			'pengguna_id'=> $id_pengguna
		);
	

		$data['profile']= $this->m_data->edit_data($where,'pengguna')->result();

		$this->load->view('dashboard/v_header');
		$this->load->view('dashboard/v_profile',$data);
		$this->load->view('dashboard/v_footer');
		}
	}

	// PENGATURAN
	public function pengaturan()
	{
		$data['pengaturan'] = $this->m_data->get_data('pengaturan')-> result();

		$this->load->view('dashboard/v_header');
		$this->load->view('dashboard/v_pengaturan',$data);
		$this->load->view('dashboard/v_footer');
	}

	public function pengaturan_update(){
		//wajib isi nama dan deskripsi website
		$this->form_validation->set_rules('nama','Nama Website','required');
		$this->form_validation->set_rules('deskripsi','Deskripsi Website','required');

		if($this->form_validation->run()!=false){
			$nama = $this->input->post('nama');
			$deskripsi = $this->input->post('deskripsi');
			$link_facebook = $this->input->post('link_facebook');
			$link_twitter = $this->input->post('link_twitter');
			$link_instagram= $this->input->post('link_instagram');
			$link_github = $this->input->post('link_github');

			$where = array(
			);

			$data = array(
				'nama'=>$nama,
				'deskripsi'=>$deskripsi,
				'link_facebook'=>$link_facebook,
				'link_twitter'=>$link_twitter,
				'link_instagram'=>$link_instagram,
				'link_github'=>$link_github
				);

			//update pengaturan
			$this->m_data->update_data($where,$data,'pengaturan');

			//periksa apakah ada gambar logo yang diupload
			if(!empty   ($_FILES['logo']['name'])){
				$config['upload_path'] = '.gambar/website/';
				$config['allowed_types'] = 'jpg|png';

				$this->load->library('upload',$config);

				if($this->upload->do_upload('logo')){
					// mengambil data tentang gambar logo yang diupload
				$gambar=$this->upload->data();
				$logo = $gambar['file_name'];

				$this->db->query("UPDATE pengaturan set logo='$logo'");

			}

		}
		redirect(base_url().'dashboard/pengaturan/?alert=sukses');
		}else{
			$data['pengaturan']=$this->m_data->get_data('pengaturan')->result();
			$this->load->view('dashboard/v_header');
			$this->load->view('dashboard/v_pengaturan',$data);
			$this->load->view('dashboard/v_footer');
		}
	}

}