<?php

application::define_path();
spl_autoload_register("application::load_classes");

if((bool)(application::env("DEBUG"))) {
    error_reporting(E_ALL ^ E_DEPRECATED ^ E_NOTICE);
} else {
    error_reporting(0);
}

final class application {


    final static function env ($key) {

        if(!defined('KKF_ENV')) {
            if(!is_readable(KKF_ROOT_PATH . DIRECTORY_SEPARATOR . ".env")) {
                die(".env file not found");
            }

            $_env_string = file_get_contents(KKF_ROOT_PATH . DIRECTORY_SEPARATOR . ".env");

            define("KKF_ENV", $_env_string);

        }

        $_env_array = parse_ini_string(KKF_ENV);
        if(array_key_exists($key, $_env_array)) {
            return $_env_array[$key];
        } else {
            return false;
        }

    }

    final static function config ($item, $key = false) {
        $filename = KKF_CONFIG_PATH . DIRECTORY_SEPARATOR . $item . ".json";
        if(!file_exists($filename)) {
            throw new \Exception("config file $item not found");
            die;
        }
        $result = file_get_contents($filename);
        $obj = json_decode($result);

        if(!$key) {
            return $obj;
        } else {
            if(!property_exists($obj, $key)) {
                throw new Exception("property not found: {$key}");
                die;
            }
            return $obj->{$key};
        }
        return ;
    }

    final  static function define_path () {

        define("KKF_ROOT_PATH", substr(__DIR__, 0, strlen(__DIR__) - 12));


        define("KKF_CORE_PATH", KKF_ROOT_PATH . DIRECTORY_SEPARATOR . "core");
        define("KKF_CONTROLLERS_PATH", KKF_ROOT_PATH . DIRECTORY_SEPARATOR . "controllers");
        define('KKF_LIBS_PATH', KKF_ROOT_PATH . DIRECTORY_SEPARATOR . "core" . DIRECTORY_SEPARATOR . "libs");
        define('KKF_MODELS_PATH', KKF_ROOT_PATH . DIRECTORY_SEPARATOR . "models");
        define('KKF_PLUGINS_PATH', KKF_CORE_PATH . DIRECTORY_SEPARATOR . "plugins");
        define('KKF_BASE_PATH', KKF_CORE_PATH . DIRECTORY_SEPARATOR . "base");
        define('KKF_VIEWS_PATH', KKF_ROOT_PATH . DIRECTORY_SEPARATOR . "views");

        define('KKF_STORE_PATH', KKF_ROOT_PATH . DIRECTORY_SEPARATOR . "store");
        define('KKF_CACHE_PATH', KKF_STORE_PATH . DIRECTORY_SEPARATOR . "cache");

        define('KKF_CONFIG_PATH', KKF_ROOT_PATH . DIRECTORY_SEPARATOR . "config");
        define('KKF_WEB_PATH', KKF_ROOT_PATH . DIRECTORY_SEPARATOR . "web");


        return;
    }

    /**
     *
     * 判断是否为web请求
     */
    final static function is_http_request () {
        if(empty ($_SERVER ['REQUEST_METHOD'])) {
            return false;
        } else {
            return true;
        }
    }


    final private static function load_bases () {
        require_once KKF_LIBS_PATH . DIRECTORY_SEPARATOR . 'helper.class.php';
        require_once KKF_BASE_PATH . DIRECTORY_SEPARATOR . 'base.class.php';
        require_once KKF_BASE_PATH . DIRECTORY_SEPARATOR . 'http.class.php';
        require_once KKF_BASE_PATH . DIRECTORY_SEPARATOR . 'rest.class.php';
        require_once KKF_BASE_PATH . DIRECTORY_SEPARATOR . 'web.class.php';
        require_once KKF_BASE_PATH . DIRECTORY_SEPARATOR . 'models.class.php';

        //require_once KKF_LIBS_PATH . DIRECTORY_SEPARATOR . 'input.class.php';

        /*
        require_once KKF_LIBS_PATH . DIRECTORY_SEPARATOR . 'ezsql' . DIRECTORY_SEPARATOR . 'ez_sql_core.php';
        require_once KKF_LIBS_PATH . DIRECTORY_SEPARATOR . 'ezsql' . DIRECTORY_SEPARATOR . 'ez_sql_mysql.php';
        require_once KKF_LIBS_PATH . DIRECTORY_SEPARATOR . 'database.class.php';

        */
        return;
    }

    final static function start () {
        define('KKF_INIT', true);
        self::load_bases();

        if(self::is_http_request()) {
            \http::start();
            return;
        }

    }

    final static function console_start ($argv, $argc) {

        define('KKF_INIT', true);
        self::load_bases();


        $controller_name = helper::get_value_from_array($argv, 1, "console");
        if(!$controller_name) {
            echo("controller not set! \n");
            die ();
        }
        $action_name = helper::get_value_from_array($argv, 2, "default_action");

        if(self::is_web_request()) {
            header("HTTP/1.1 403 only for console!");
            die ();
        }


        self::load_controller($controller_name, $action_name);
        return;

    }

    final public static function load_classes ($class_name) {

        $level1_path = false;

        if(substr($class_name,0,7)=="models\\"){
            $level1_path = KKF_MODELS_PATH;
            $class_name = substr($class_name,7);
        }

        if(substr($class_name,0,12)=="controllers\\"){
            $level1_path = KKF_CONTROLLERS_PATH;
            $class_name= substr($class_name,12);
        }

        if(!$level1_path){
            return;
        }

        $class_filename = str_replace("\\",DIRECTORY_SEPARATOR,$class_name);
        $class_filename = $level1_path.DIRECTORY_SEPARATOR.$class_filename.".class.php";

        if(file_exists($class_filename)) {
            require_once $class_filename;
            return;
        } else {
            throw new Exception("class $class_name file not found : " . htmlentities($class_filename));
        }
        return;
    }
}