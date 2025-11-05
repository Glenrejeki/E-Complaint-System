<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Base Controller untuk CodeIgniter 3
 * Sudah ditambah deklarasi properti supaya PHP 8.2 tidak komplain
 */
class CI_Controller
{
    /**
     * Instance global CI
     * @var CI_Controller
     */
    private static $instance;

    // deklarasi semua properti yang biasa ditempel CI
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
    public $log;       // <- ditambah
    public $utf8;      // <- ditambah
    public $session;   // <- ditambah
    public $db;        // kadang dipakai juga

    public function __construct()
    {
        // simpan instance
        self::$instance = $this;

        // tempel semua core class yang sudah di-load
        foreach (is_loaded() as $var => $class)
        {
            // contoh: $this->config =& load_class('Config', 'core');
            $this->$var =& load_class($class);
        }

        // pastikan loader ada
        $this->load =& load_class('Loader', 'core');

        // kalau loader punya initialize, panggil
        if (method_exists($this->load, 'initialize')) {
            $this->load->initialize();
        }

        log_message('info', 'CI_Controller Class Initialized');
    }

    /**
     * global accessor
     */
    public static function &get_instance()
    {
        return self::$instance;
    }
}
