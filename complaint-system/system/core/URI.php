<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class CI_URI {

    public $uri_string = '';

    public function __construct() {
        // Memastikan uri_string terinisialisasi dengan benar
        if (isset($_SERVER['REQUEST_URI'])) {
            $this->uri_string = $_SERVER['REQUEST_URI'];
        }
    }

    // Fungsi lainnya untuk menangani URI
}
