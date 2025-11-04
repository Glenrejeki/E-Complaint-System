<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Complaint extends CI_Controller {

    public function __construct() {
        parent::__construct();
        // Memuat model dan library
        $this->load->model('Complaint_model');
        $this->load->library(['encryption', 'input']);  // Memuat library encryption dan input
    }

    public function index() {
        // Mengambil daftar pengaduan
        $data['complaints'] = $this->Complaint_model->get_complaints();
        // Menampilkan view admin
        $this->load->view('admin_view', $data);
    }

    public function add_complaint() {
        // Validasi dan enkripsi data pengaduan
        $complaint_text = $this->input->post('complaint_text');  // Menggunakan $this->input
        $encrypted_complaint = $this->encryption->encrypt($complaint_text);  // Menggunakan $this->encryption

        // Menyimpan pengaduan ke database
        $this->Complaint_model->add_complaint($encrypted_complaint);
        redirect('complaint');
    }
}
