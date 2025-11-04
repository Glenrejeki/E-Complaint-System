<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

    public function __construct() {
        parent::__construct();

        // Memuat library session dengan benar
        $this->load->library('session');
    }

    public function index() {
        // Fungsi untuk menampilkan halaman utama
        $this->load->view('welcome_message');
    }
}
