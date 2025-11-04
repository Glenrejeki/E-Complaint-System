<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CI_Loader – versi disederhanakan dan ditambahi deklarasi properti
 * supaya aman di PHP 8.2 dan masih bisa load library penting (termasuk session).
 */
class CI_Loader
{
    // deklarasi properti biar nggak jadi dynamic property
    protected $_ci_ob_level;
    protected $_ci_view_paths = array();
    protected $_ci_library_paths = array();
    protected $_ci_model_paths = array();
    protected $_ci_helper_paths = array();
    protected $_ci_cached_vars = array();
    protected $_ci_classes = array();
    protected $_ci_loaded_files = array();
    protected $_ci_models = array();
    protected $_ci_helpers = array();
    protected $_ci_varmap = array('unit_test' => 'unit', 'user_agent' => 'agent');

    public function __construct()
    {
        $this->_ci_ob_level      = ob_get_level();
        $this->_ci_view_paths    = array(APPPATH.'views/' => TRUE);
        $this->_ci_library_paths = array(APPPATH, BASEPATH);
        $this->_ci_model_paths   = array(APPPATH);
        $this->_ci_helper_paths  = array(APPPATH, BASEPATH);

        log_message('info', 'Loader Class Initialized');
    }

    /* ===================== VIEW ===================== */
    public function view($view, $vars = array(), $return = FALSE)
    {
        return $this->_ci_load(array(
            '_ci_view'   => $view,
            '_ci_vars'   => $this->_ci_prepare_view_vars($vars),
            '_ci_return' => $return
        ));
    }

    /* ===================== MODEL ===================== */
    public function model($model, $name = '', $db_conn = FALSE)
    {
        if (empty($model)) return;

        if (is_array($model)) {
            foreach ($model as $m) {
                $this->model($m);
            }
            return;
        }

        $path = '';
        if (($last_slash = strrpos($model, '/')) !== FALSE) {
            $path  = substr($model, 0, $last_slash + 1);
            $model = substr($model, $last_slash + 1);
        }

        $name = $name ?: $model;

        if (isset($this->_ci_models[$name])) {
            return;
        }

        $model_file = $model.'.php';

        foreach ($this->_ci_model_paths as $mod_path) {
            $full = $mod_path.'models/'.$path.$model_file;
            if (file_exists($full)) {
                require_once($full);

                $CI =& get_instance();
                if ($db_conn === TRUE && ! isset($CI->db)) {
                    $CI->load->database();
                }

                $model_class = ucfirst($model);
                $CI->$name = new $model_class();
                $this->_ci_models[$name] = $name;
                return;
            }
        }

        show_error('Unable to locate the model you have specified: '.$model);
    }

    /* ===================== LIBRARY ===================== */
    public function library($library, $params = NULL, $object_name = NULL)
    {
        if (empty($library)) return;

        // kalau array, load satu-satu
        if (is_array($library)) {
            foreach ($library as $lib) {
                $this->library($lib, $params);
            }
            return;
        }

        // ------------ KHUSUS: session ------------
        // CI3 taruh di system/libraries/Session/Session.php dengan class CI_Session
        if (strtolower($library) === 'session') {
            $this->_load_session_library($params, $object_name);
            return;
        }
        // ------------ END KHUSUS SESSION ---------

        $class  = $library;
        $subdir = '';

        // library di subfolder?
        if (($last_slash = strrpos($library, '/')) !== FALSE) {
            $subdir = substr($library, 0, $last_slash + 1);
            $class  = substr($library, $last_slash + 1);
        }

        $class = ucfirst($class);

        if (isset($this->_ci_classes[$class])) {
            return;
        }

        $found = FALSE;

        foreach ($this->_ci_library_paths as $lib_path) {

            // pola umum: libraries/MyLib.php
            $file1 = $lib_path.'libraries/'.$subdir.$class.'.php';

            // pola kedua: libraries/MyLib/MyLib.php (ini buat beberapa lib bawaan)
            $file2 = $lib_path.'libraries/'.$subdir.$class.'/'.$class.'.php';

            if (file_exists($file1)) {
                $found = $file1;
                break;
            } elseif (file_exists($file2)) {
                $found = $file2;
                break;
            }
        }

        if ($found === FALSE) {
            show_error('Unable to load the requested library: '.$library);
        }

        require_once($found);

        $CI =& get_instance();
        $object_name = $object_name ?: strtolower($class);

        // coba buat instance dengan params kalau ada
        if ($params !== NULL) {
            $CI->$object_name = new $class($params);
        } else {
            $CI->$object_name = new $class();
        }

        $this->_ci_classes[$class] = $object_name;
    }

    /**
     * loader helper
     */
    public function helper($helpers = array())
    {
        if (empty($helpers)) return;

        if (is_array($helpers)) {
            foreach ($helpers as $h) {
                $this->helper($h);
            }
            return;
        }

        $helper = strtolower($helpers);

        if (isset($this->_ci_helpers[$helper])) {
            return;
        }

        $filename = $helper.'_helper.php';

        foreach ($this->_ci_helper_paths as $path) {
            if (file_exists($path.'helpers/'.$filename)) {
                include_once($path.'helpers/'.$filename);
                $this->_ci_helpers[$helper] = TRUE;
                return;
            }
        }

        show_error('Unable to load the requested helper: '.$helper);
    }

    /**
     * load database
     */
    public function database($params = '', $return = FALSE, $query_builder = NULL)
    {
        require_once(BASEPATH.'database/DB.php');

        if ($return === TRUE) {
            return DB($params, $query_builder);
        }

        $CI =& get_instance();
        $CI->db = DB($params, $query_builder);
    }

    /**
     * set vars buat view
     */
    public function vars($vars, $val = '')
    {
        if (is_string($vars)) {
            $vars = array($vars => $val);
        }

        $this->_ci_cached_vars = array_merge($this->_ci_cached_vars, $vars);
        return $this;
    }

    /* ===================== INTERNAL ===================== */

    protected function _ci_load($_ci_data)
    {
        $_ci_view   = $_ci_data['_ci_view'];
        $_ci_vars   = isset($_ci_data['_ci_vars']) ? $_ci_data['_ci_vars'] : array();
        $_ci_return = $_ci_data['_ci_return'];

        $_ci_file = '';

        foreach ($this->_ci_view_paths as $view_path => $cascade) {
            if (file_exists($view_path.$_ci_view.'.php')) {
                $_ci_file = $view_path.$_ci_view.'.php';
                break;
            }
        }

        if ($_ci_file === '') {
            show_error('Unable to load the requested file: '.$_ci_view.'.php');
        }

        extract($_ci_vars);

        ob_start();
        include($_ci_file);
        $buffer = ob_get_contents();
        @ob_end_clean();

        if ($_ci_return === TRUE) {
            return $buffer;
        }

        $CI =& get_instance();
        $CI->output->append_output($buffer);
    }

    protected function _ci_prepare_view_vars($vars)
    {
        return array_merge($this->_ci_cached_vars, (array) $vars);
    }

    /**
     * loader khusus untuk session CI3
     */
    protected function _load_session_library($params = NULL, $object_name = NULL)
    {
        // lokasi khas session CI3
        $session_file = BASEPATH.'libraries/Session/Session.php';
        if ( ! file_exists($session_file)) {
            show_error('CI_Session library not found at '.$session_file);
        }

        require_once($session_file);

        // class-nya CI_Session
        $CI =& get_instance();
        $object_name = $object_name ?: 'session';

        if ($params !== NULL) {
            $CI->$object_name = new CI_Session($params);
        } else {
            $CI->$object_name = new CI_Session(array());
        }

        // tandai sudah ke-load
        $this->_ci_classes['Session'] = $object_name;
    }
}
