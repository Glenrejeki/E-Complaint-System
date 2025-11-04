<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class CI_Loader {

    public function __construct() {
        // Memuat library yang diperlukan
        $this->load->library('log');     // Memuat log
        $this->load->library('hooks');   // Memuat hooks
        $this->load->library('utf8');    // Memuat utf8
        // Pastikan tidak ada properti dinamis seperti $this->load yang didefinisikan di sini
    }

    // Fungsi lainnya untuk menangani pemuatan view dan library bisa ditambahkan di sini
}
