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
        $this->input = new \input();

        $this->load_lib("output");
        $this->output = new \output();

        $this->load_lib("log");
        if(\application::env("LOG_ENABLED")) {
            if(!is_object($this->log)) {
                $this->log = new \ezLog(\application::env("LOG_FILENAME"));
                $this->log->set_record_level(\application::env("LOG_LEVEL"));
            }
        }

        $this->load_lib("cache");
        if(\application::env("CACHE_ENABLED")) {
            if(!is_object($this->cache)) {
                $this->cache = new \ezcache(\application::env("REDIS_HOST"), \application::env("REDIS_PORT"));
                $this->cache->enable_log($this->log);
            }
        }

    }

    protected function fatal ($message) {
        $this->log->fatal($message);
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

        if(!helper::instr($class_name, "models\\")) {
            return;
        }

        $class_name = str_replace("models\\", "", $class_name);
        $filename = KKF_MODELS_PATH . "/" . $class_name . ".class.php";

        if(file_exists($filename)) {
            require_once $filename;
            return;
        } else {
            echo "class " . htmlentities($class_name) . " file not found : " . htmlentities($filename);
            $this->log->error("class " . $class_name . " file not found : " . $filename);
            return;
        }
    }

    final protected function load_lib ($lib_name) {

        $libs = \application::config("app", "libs");

        if(!property_exists($libs, $lib_name)) {
            throw new Exception("lib $lib_name not defined");
            return;
        }

        $lib = $libs->{$lib_name};
        require_once PFW_LIBS_PATH . DIRECTORY_SEPARATOR . $lib;
        return;

        switch ($lib_name) {
            case "log":
                require_once PFW_LIBS_PATH . DIRECTORY_SEPARATOR . 'ezlog.class.php';
                $this->get_log_connect();
                break;
            case "cache":
                require_once PFW_LIBS_PATH . DIRECTORY_SEPARATOR . 'ezcache.class.php';
                $this->get_cache_connect();
                break;
            case "input":
                require_once PFW_LIBS_PATH . DIRECTORY_SEPARATOR . 'input.class.php';
                $this->input = new \input();
                break;
            case "output":
                require_once PFW_LIBS_PATH . DIRECTORY_SEPARATOR . 'output.class.php';
                $this->output = new \output();
                break;
            case "wechat":
                require_once PFW_LIBS_PATH . DIRECTORY_SEPARATOR . 'wechat.class.php';
                break;
            case "view":
                require_once PFW_LIBS_PATH . DIRECTORY_SEPARATOR . 'ezview.class.php';
                return;
            case "rest_client":
                require_once PFW_LIBS_PATH . DIRECTORY_SEPARATOR . 'rest_client.class.php';
                return;
        }
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