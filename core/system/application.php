<?php

application::define_path();

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
        define('KKF_CACHE_PATH', KKF_ROOT_PATH . DIRECTORY_SEPARATOR . "cache");
        define('KKF_STORE_PATH', KKF_ROOT_PATH . DIRECTORY_SEPARATOR . "store");
        define('KKF_CONFIG_PATH', KKF_ROOT_PATH . DIRECTORY_SEPARATOR . "config");
        define('KKF_WEB_PATH', KKF_ROOT_PATH . DIRECTORY_SEPARATOR . "web");


        return;
    }

    /**
     *
     * 判断是否为web请求
     */
    final static function is_web_request () {
        if(empty ($_SERVER ['REQUEST_METHOD'])) {
            return false;
        } else {
            return true;
        }
    }

    final public static function load_controller () {

        $parm = func_get_args();
        $controller_target = \helper::get_value_from_array($parm, 0, false);
        if(!$controller_target) {
            die;
        }

        list($controller_name, $action_name) = explode("@", $controller_target);

        if(!$action_name) {
            $action_name = "main";
        }

        $param_string = null;
        if(count($parm) > 1) {
            for ($i = 1; $i < count($parm); $i++) {
                $param_string .= $parm[$i] . ",";
            }
            $param_string = substr($param_string, 0, strlen($param_string) - 1);
        }

        if(!file_exists(KKF_CONTROLLERS_PATH . DIRECTORY_SEPARATOR . $controller_name . ".class.php")) {

            if(self::is_web_request()) {
                header("HTTP/1.1 404 controller " . htmlspecialchars($controller_name) . " not found");
            } else {
                echo "controller " . $controller_name . " not found \n";
            }

            die ();
        }


        require_once KKF_CONTROLLERS_PATH . DIRECTORY_SEPARATOR . $controller_name . '.class.php';

        if(!class_exists("\\controllers\\{$controller_name}", false)) {

            if(self::is_web_request()) {
                header("HTTP/1.1 404 controller " . htmlspecialchars($controller_name) . " not define");
            } else {
                echo("controller " . $controller_name . " not define \n");
            }

            die ();
        }

        $controller = null;
        $str = "\$controller=new \\controllers\\{$controller_name}();";
        eval ($str);

        if(!method_exists($controller, $action_name)) {
            $action_name = "main";
        }

        if(!method_exists($controller, $action_name)) {
            if(self::is_web_request()) {
                header("HTTP/1.1 404 action_name " . htmlspecialchars($action_name) . " not define");
            } else {
                echo("action_name " . $action_name . " not define \n");
            }
            die();
        }
        $str = '$controller->' . $action_name . '(' . $param_string . ');';
        eval ($str);
        return;

    }

    final private static function load_bases () {
        require_once KKF_LIBS_PATH . DIRECTORY_SEPARATOR . 'helper.class.php';
        require_once KKF_BASE_PATH . DIRECTORY_SEPARATOR . 'base.class.php';
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

        if(self::is_web_request()) {

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

    final static function web_start () {

        define('KKF_INIT', true);
        self::load_bases();

        if(!self::is_web_request()) {
            echo("only for web request!\n");
            die ();
        }

        /*

        if ( \application::config("http")->session_auto_start ) {
            session_start ();
        }

        $argvs = self::get_web_path ();
        */

        /*
        if(!key_exists(0, $argvs)){
            header("HTTP/1.1 403 REST verison not set");
            echo "REST verison not set";
            var_dump($argvs);
            die;
        }

        if($argvs[0]!="v4"){
            header("HTTP/1.1 403 REST verison error");
            echo "REST verison error";
            var_dump($argvs);
            die;
        }

        if(!key_exists(1, $argvs)){
            header("HTTP/1.1 403 REST object not set");
            echo "REST object not set";
            var_dump($argvs);
            die;
        }

        $controller_name = $argvs[1];

        $action_name = "index";

        if(key_exists(2, $argvs)){
            $action_name = $argvs[2];
        }


        self::load_controller ( $controller_name, $action_name );
        */

        new \controllers\web();


        return;

    }

}