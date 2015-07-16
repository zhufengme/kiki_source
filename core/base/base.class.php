<?php
if(!defined("KKF_INIT")) {
    die("break in");
}

class base {

    protected $timestamp = false;
    protected $log = false;
    protected $cache = false;
    protected $input = false;
    protected $output = false;

    function __construct () {
        $this->timestamp = self::get_timestamp();
        spl_autoload_register("self::load_classes");

        $this->load_lib("input");
        if(!is_object($this->input)) {
            $this->input = new \input();
        }

        $this->load_lib("output");
        if(!is_object($this->output)) {
            $this->output = new \output();
        }

        $this->load_lib("log");
        if((bool)\application::env("LOG_ENABLED")) {
            if(!is_object($this->log)) {
                $this->log = new \ezLog(\application::env("LOG_FILENAME"));
                $this->log->set_record_level(\application::env("LOG_LEVEL"));
            }
        }

        $this->load_lib("cache");
        if((bool)\application::env("CACHE_ENABLED")) {
            if(!is_object($this->cache)) {
                $this->cache = new \ezcache(\application::env("REDIS_HOST"), \application::env("REDIS_PORT"));
                $this->cache->enable_log($this->log);
            }
        }

    }

    protected function fatal ($message) {
        if(is_object($this->log)) {
            $this->log->fatal($message);
        }
        throw new \Exception($message);
        die;
    }

    protected static function get_timestamp () {
        return time();
    }


    function __get ($name) {
        $cmd_str = "\$result=\$this->get_{$name}();";
        eval($cmd_str);
        return $result;
    }


    private function load_classes ($class_name) {

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
            $this->log->fatal("class $class_name file not found : " . htmlentities($class_filename));
            throw new Exception("class $class_name file not found : " . htmlentities($class_filename));
        }
        return;
    }

    final protected function load_lib ($lib_name) {

        $libs = \application::config("app", "libs");

        if(!property_exists($libs, $lib_name)) {
            throw new Exception("lib $lib_name not defined");
            return;
        }

        $lib = $libs->{$lib_name};

        require_once KKF_LIBS_PATH . DIRECTORY_SEPARATOR . $lib;

        return;
    }

    final protected function force_ssl ($is_ssl = true) {
        if(!\application::is_web_request()) {
            return;
        }
        $proto = false;

        $hostname = helper::get_value_from_array($this->input->http_server, 'HTTP_X_REAL_HOST');

        if(!$hostname) {
            $hostname = $this->input->http_server ["HTTP_HOST"];
        }

        $uri = $this->input->http_server ['REQUEST_URI'];

        $current_ssl = false;

        if(helper::get_value_from_array($this->input->http_server, 'HTTP_HTTPS') == "on") {
            $current_ssl = true;
        }

        if(!helper::get_value_from_array($this->input->http_server, 'HTTP_HTTPS')) {
            if(helper::get_value_from_array($this->input->http_server, 'HTTPS') == "on") {
                $current_ssl = true;
            }
        }

        if($is_ssl != $current_ssl) {
            if($is_ssl) {
                $proto = "https://";
            } else {
                $proto = "http://";
            }
            $url = $proto . $hostname . $uri;

            header("Location: $url");
        }

        return;

    }

}