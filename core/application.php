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
            if(!is_readable(KKF_ENV_FILE)) {
                die(".env file not found");
            }

            $_env_string = file_get_contents(KKF_ENV_FILE);

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
                return false;
            }
            return $obj->{$key};
        }
        return false;
    }

    final  static function define_path () {

        define("KKF_ROOT_PATH", substr(__DIR__, 0, strlen(__DIR__) - 5));
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
        define('KKF_VENDOR_PATH', KKF_ROOT_PATH . DIRECTORY_SEPARATOR . "vendor");


        define('KKF_ENV_FILE', KKF_ROOT_PATH . DIRECTORY_SEPARATOR . ".env");


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
        require_once KKF_BASE_PATH . DIRECTORY_SEPARATOR . 'wechat.class.php';
        require_once KKF_BASE_PATH . DIRECTORY_SEPARATOR . 'models.class.php';
        require_once KKF_BASE_PATH . DIRECTORY_SEPARATOR . 'console.class.php';

        $db_method = \application::env("DB_METHOD");
        if($db_method == "pdo") {
            require_once KKF_LIBS_PATH . DIRECTORY_SEPARATOR . 'pdo_db.class.php';
        } else {
            require_once KKF_LIBS_PATH . DIRECTORY_SEPARATOR . 'database.class.php';
        }

        //require_once KKF_LIBS_PATH . DIRECTORY_SEPARATOR . 'input.class.php';

        /*
        require_once KKF_LIBS_PATH . DIRECTORY_SEPARATOR . 'ezsql' . DIRECTORY_SEPARATOR . 'ez_sql_core.php';
        require_once KKF_LIBS_PATH . DIRECTORY_SEPARATOR . 'ezsql' . DIRECTORY_SEPARATOR . 'ez_sql_mysql.php';
        require_once KKF_LIBS_PATH . DIRECTORY_SEPARATOR . 'database.class.php';

        */
        return;
    }

    final static function start ($argv=false) {
        define('KKF_INIT', true);
        self::load_bases();

        if(self::is_http_request()) {
            \http::start();
            return;
        }else{
            \console::start($argv);
            return;
        }
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

    final public static function system_exec($str_cmd,$error = true,$background = false){

        $str_cmd_prefix = "export LANG=en;";
        $str_cmd_suffix = null;

        if(!$error){
            $str_cmd_suffix = " 2>/dev/null";
        }
        if($background){
            $str_cmd_prefix = "nohup " . $str_cmd_prefix;
            $str_cmd_suffix = " 1&2>/dev/null &";
        }


        $str_cmd = $str_cmd_prefix . $str_cmd . $str_cmd_suffix;

        $str_result = shell_exec($str_cmd);

        return $str_result;

    }
}