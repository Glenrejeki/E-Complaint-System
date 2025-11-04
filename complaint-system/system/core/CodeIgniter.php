<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2019 - 2022, CodeIgniter Foundation
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package	CodeIgniter
 * @author	EllisLab Dev Team
 * @copyright	Copyright (c) 2008 - 2014, EllisLab, Inc. (https://ellislab.com/)
 * @copyright	Copyright (c) 2014 - 2019, British Columbia Institute of Technology (https://bcit.ca/)
 * @copyright	Copyright (c) 2019 - 2022, CodeIgniter Foundation (https://codeigniter.com/)
 * @license	https://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 1.0.0
 * @filesource
 */

const CI_VERSION = '3.2.0-dev';

// Memuat konstanta-konstanta yang dibutuhkan
if (file_exists(APPPATH.'config/'.ENVIRONMENT.'/constants.php')) {
    require_once(APPPATH.'config/'.ENVIRONMENT.'/constants.php');
}

if (file_exists(APPPATH.'config/constants.php')) {
    require_once(APPPATH.'config/constants.php');
}

// Memuat fungsi global
require_once(BASEPATH.'core/Common.php');

// Menetapkan handler untuk error
set_error_handler('_error_handler');
set_exception_handler('_exception_handler');
register_shutdown_function('_shutdown_handler');

// Menetapkan subclass_prefix
if (!empty($assign_to_config['subclass_prefix'])) {
    get_config(array('subclass_prefix' => $assign_to_config['subclass_prefix']));
}

// Cek apakah menggunakan autoload Composer
if ($composer_autoload = config_item('composer_autoload')) {
    if ($composer_autoload === TRUE) {
        if (file_exists(APPPATH.'vendor/autoload.php')) {
            require_once(APPPATH.'vendor/autoload.php');
        } else {
            log_message('error', '$config[\'composer_autoload\'] is set to TRUE but '.APPPATH.'vendor/autoload.php was not found.');
        }
    } elseif (file_exists($composer_autoload)) {
        require_once($composer_autoload);
    } else {
        log_message('error', 'Could not find the specified $config[\'composer_autoload\'] path: '.$composer_autoload);
    }
}

// Memulai timer
$BM =& load_class('Benchmark', 'core');
$BM->mark('total_execution_time_start');
$BM->mark('loading_time:_base_classes_start');

// Menginstansiasi kelas config
$CFG =& load_class('Config', 'core');

// Jika ada item config manual yang ditetapkan
if (isset($assign_to_config) && is_array($assign_to_config)) {
    foreach ($assign_to_config as $key => $value) {
        $CFG->set_item($key, $value);
    }
}

// Menginstansiasi kelas hooks
$EXT =& load_class('Hooks', 'core', $CFG);

// Memanggil hook 'pre_system' jika ada
$EXT->call_hook('pre_system');

// Menetapkan charset
$charset = strtoupper(config_item('charset'));
ini_set('default_charset', $charset);

if (extension_loaded('mbstring')) {
    define('MB_ENABLED', TRUE);
    @ini_set('mbstring.internal_encoding', $charset);
    mb_substitute_character('none');
} else {
    define('MB_ENABLED', FALSE);
}

if (extension_loaded('iconv')) {
    define('ICONV_ENABLED', TRUE);
    @ini_set('iconv.internal_encoding', $charset);
} else {
    define('ICONV_ENABLED', FALSE);
}

if (is_php('5.6')) {
    ini_set('php.internal_encoding', $charset);
}

// Memuat fungsi kompatibilitas
require_once(BASEPATH.'core/compat/mbstring.php');
require_once(BASEPATH.'core/compat/hash.php');
require_once(BASEPATH.'core/compat/password.php');
require_once(BASEPATH.'core/compat/standard.php');

// Menginstansiasi kelas UTF-8
$UNI =& load_class('Utf8', 'core', $charset);

// Menginstansiasi kelas URI
$URI =& load_class('URI', 'core', $CFG);

// Menginstansiasi kelas Router dan mengatur routing
$RTR =& load_class('Router', 'core', isset($routing) ? $routing : NULL);

// Menginstansiasi kelas Output
$OUT =& load_class('Output', 'core');

// Cek apakah ada file cache yang valid
if ($EXT->call_hook('cache_override') === FALSE && $OUT->_display_cache($CFG, $URI) === TRUE) {
    exit;
}

// Memuat kelas Security untuk xss dan csrf
$SEC =& load_class('Security', 'core', $charset);

// Memuat kelas Input dan sanitasi globals
$IN =& load_class('Input', 'core', $SEC);

// Memuat kelas Language
$LANG =& load_class('Lang', 'core');

// Memuat controller aplikasi dan controller lokal
require_once BASEPATH.'core/Controller.php';

// Fungsi untuk mengembalikan instance CI_Controller
function &get_instance() {
    return CI_Controller::get_instance();
}

// Memuat controller subclass jika ada
if (file_exists(APPPATH.'core/'.$CFG->config['subclass_prefix'].'Controller.php')) {
    require_once APPPATH.'core/'.$CFG->config['subclass_prefix'].'Controller.php';
}

// Mark point untuk benchmarking
$BM->mark('loading_time:_base_classes_end');

// Cek apakah ada kelas yang valid untuk routing
$e404 = FALSE;
$class = ucfirst($RTR->class);
$method = $RTR->method;

if (empty($class) OR ! file_exists(APPPATH.'controllers/'.$RTR->directory.$class.'.php')) {
    $e404 = TRUE;
} else {
    require_once(APPPATH.'controllers/'.$RTR->directory.$class.'.php');

    if ( ! class_exists($class, FALSE) OR $method[0] === '_' OR method_exists('CI_Controller', $method)) {
        $e404 = TRUE;
    } elseif (method_exists($class, '_remap')) {
        $params = array($method, array_slice($URI->rsegments, 2));
        $method = '_remap';
    } elseif ( ! method_exists($class, $method)) {
        $e404 = TRUE;
    } else {
        $reflection = new ReflectionMethod($class, $method);
        if ( ! $reflection->isPublic() OR $reflection->isConstructor()) {
            $e404 = TRUE;
        }
    }
}

if ($e404) {
    if ( ! empty($RTR->routes['404_override'])) {
        if (sscanf($RTR->routes['404_override'], '%[^/]/%s', $error_class, $error_method) !== 2) {
            $error_method = 'index';
        }

        $error_class = ucfirst($error_class);

        if ( ! class_exists($error_class, FALSE)) {
            if (file_exists(APPPATH.'controllers/'.$RTR->directory.$error_class.'.php')) {
                require_once(APPPATH.'controllers/'.$RTR->directory.$error_class.'.php');
                $e404 = ! class_exists($error_class, FALSE);
            } elseif ( ! empty($RTR->directory) && file_exists(APPPATH.'controllers/'.$error_class.'.php')) {
                require_once(APPPATH.'controllers/'.$error_class.'.php');
                if (($e404 = ! class_exists($error_class, FALSE)) === FALSE) {
                    $RTR->directory = '';
                }
            }
        } else {
            $e404 = FALSE;
        }
    }

    if ( ! $e404) {
        $class = $error_class;
        $method = $error_method;

        $URI->rsegments = array(
            1 => $class,
            2 => $method
        );
    } else {
        show_404($RTR->directory.$class.'/'.$method);
    }
}

if ($method !== '_remap') {
    $params = array_slice($URI->rsegments, 2);
}

$EXT->call_hook('pre_controller');

$BM->mark('controller_execution_time_( '.$class.' / '.$method.' )_start');

$CI = new $class();

$EXT->call_hook('post_controller_constructor');

call_user_func_array(array(&$CI, $method), $params);

$BM->mark('controller_execution_time_( '.$class.' / '.$method.' )_end');

$EXT->call_hook('post_controller');

if ($EXT->call_hook('display_override') === FALSE) {
    $OUT->_display();
}

$EXT->call_hook('post_system');
