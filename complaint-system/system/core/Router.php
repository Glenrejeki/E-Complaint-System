<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class CI_Router {

    public $class = '';      // Menyimpan nama class controller
    public $method = '';     // Menyimpan nama method yang dipanggil
    public $directory = '';  // Menyimpan nama directory jika ada
    public $uri = '';        // Menyimpan URI yang diterima

    public function __construct() {
        // Menyimpan URI yang diterima dari server
        if (isset($_SERVER['REQUEST_URI'])) {
            $this->uri = $_SERVER['REQUEST_URI'];
        }

        // Inisialisasi default class dan method
        $this->class = 'Welcome'; // Class default
        $this->method = 'index';  // Method default
        $this->directory = '';    // Directory default, kosongkan jika tidak ada
    }

    // Fungsi untuk mengonfigurasi routing
    public function set_class($class) {
        $this->class = $class;
    }

    public function set_method($method) {
        $this->method = $method;
    }

    public function set_directory($directory) {
        $this->directory = $directory;
    }

    // Fungsi untuk mendapatkan class yang sedang aktif
    public function get_class() {
        return $this->class;
    }

    // Fungsi untuk mendapatkan method yang sedang aktif
    public function get_method() {
        return $this->method;
    }

    // Fungsi untuk mendapatkan directory yang sedang aktif
    public function get_directory() {
        return $this->directory;
    }

    // Fungsi untuk menangani routing dan pengecekan URI
    public function route($uri) {
        // Logika routing bisa ditambahkan di sini, misalnya parsing URI dan memanggil controller/method sesuai URI
        // Misalnya:
        // Jika URI adalah '/user/profile', kita bisa memanggil controller 'User' dan method 'profile'.
        $segments = explode('/', trim($uri, '/'));

        if (count($segments) > 0) {
            $this->class = ucfirst($segments[0]);
        }
        if (count($segments) > 1) {
            $this->method = $segments[1];
        }

        // Tambahkan logika routing lainnya sesuai kebutuhan
    }
}
