<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class CI_Controller {

    public function __construct() {
        // Memuat library yang diperlukan menggunakan $this->load
        $this->load->library(['input', 'session', 'uri']);
    }

    // Fungsi-fungsi lainnya untuk controller dapat ditambahkan di sini
}
