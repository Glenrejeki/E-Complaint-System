<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class CI_URI {

    public $uri_string = '';  // URI yang diterima
    public $rsegments = [];   // Rsegments yang akan diparsing dan digunakan oleh router

    public function __construct() {
        // Pastikan uri_string terinisialisasi dengan benar
        if (isset($_SERVER['REQUEST_URI'])) {
            $this->uri_string = $_SERVER['REQUEST_URI'];
        }

        // Inisialisasi rsegments dengan array kosong
        $this->rsegments = [];

        // Pisahkan segmen URI untuk digunakan oleh router
        $this->rsegments = explode('/', trim($this->uri_string, '/'));
    }

    // Fungsi lainnya untuk menangani URI bisa ditambahkan di sini
}
