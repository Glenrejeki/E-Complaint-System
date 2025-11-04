<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

    public function __construct() {
        parent::__construct();
        // Memuat model dan library
        $this->load->model('Complaint_model');
        $this->load->library(['session', 'input']);  // Memuat library session dan input
    }

    public function index() {
        $this->load->view('login_view');
    }

    public function login() {
        // Validasi login
        $username = $this->input->post('username');  // Menggunakan $this->input
        $password = $this->input->post('password');  // Menggunakan $this->input

        // Memeriksa apakah login berhasil
        if ($this->Complaint_model->login($username, $password)) {
            redirect('complaint');
        } else {
            // Menampilkan pesan error jika login gagal
            $this->load->view('login_view', ['error' => 'Invalid login']);
        }
    }

    public function logout() {
        // Menghancurkan sesi pengguna dan mengalihkan ke halaman login
        $this->session->sess_destroy();  // Menggunakan $this->session
        redirect('auth');
    }
}
