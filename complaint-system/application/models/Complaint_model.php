<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Complaint_model extends CI_Model {

    // Fungsi untuk login (contoh sederhana)
    public function login($username, $password) {
        $this->db->where('username', $username);
        $query = $this->db->get('users');
        if ($query->num_rows() == 1) {
            $user = $query->row();
            if (password_verify($password, $user->password)) {  // Menggunakan password_verify untuk verifikasi
                return TRUE;
            }
        }
        return FALSE;
    }

    // Fungsi untuk menambahkan pengaduan
    public function add_complaint($complaint) {
        $data = ['complaint_text' => $complaint];
        $this->db->insert('complaints', $data);
    }

    // Fungsi untuk mengambil semua pengaduan
    public function get_complaints() {
        return $this->db->get('complaints')->result();  // Mengambil semua data pengaduan
    }
}
