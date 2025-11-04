<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Base Controller untuk CodeIgniter 3
 * Sudah ditambah deklarasi properti supaya PHP 8.2 tidak mengeluh soal
 * "Creation of dynamic property ..."
 */
class CI_Controller
{
    /**
     * Menyimpan instance CI yang sedang jalan
     * supaya bisa diambil dari mana saja.
     *
     * @var CI_Controller
     */
    private static $instance;

    // deklarasi properti umum yang biasanya diisi CI
    /** @var CI_Loader */
    public $load;
    public $config;
    public $input;
    public $output;
    public $uri;
    public $router;
    public $benchmark;
    public $hooks;
    public $security;
    public $lang;

    public function __construct()
    {
        // simpan diri sendiri di instance statis
        self::$instance = $this;

        /**
         * Di CI3 asli, di sini dia ambil semua class yang sudah di-load
         * lalu assign ke controller, misal:
         *   $this->config =& load_class('Config', 'core');
         *   dst...
         * fungsi is_loaded() dan load_class() disediakan di system/core/Common.php
         */
        foreach (is_loaded() as $var => $class)
        {
            // contoh: $this->config =& load_class('Config', 'core');
            $this->$var =& load_class($class);
        }

        // pastikan loader ada
        $this->load =& load_class('Loader', 'core');

        // beberapa loader (termasuk punya kita tadi) belum tentu punya initialize(),
        // jadi kita cek dulu
        if (method_exists($this->load, 'initialize')) {
            $this->load->initialize();
        }

        log_message('info', 'CI_Controller Class Initialized');
    }

    /**
     * Dipakai di banyak tempat: get_instance()
     * supaya dari mana saja kita bisa:
     *   $CI =& get_instance();
     */
    public static function &get_instance()
    {
        return self::$instance;
    }
}
