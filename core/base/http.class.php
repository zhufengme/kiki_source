<?php

class http extends \base {

    function __construct () {
        parent::__construct();
        if(!\application::is_http_request()) {
            $this->log->fatal("not in http request");
            throw new \Exception("not in http request!");
            return;
        }
    }

    final public static function start () {
        \controllers\routes::enter(self::path());
        return;
    }

    protected function post_raw(){
        return file_get_contents("php://input");
    }

    protected function set_cookie($key,$value=null,$expire=null){
        if($expire===null){
            $expire = $this->timestamp;
        }else{
            $expire += $this->timestamp;
        }

        if($value!==null){
            $value=\kkcrypt::aes_cbc_encrypt($value,\application::env("APP_KEY"));
        }

        $key = \application::env("COOKIE_PREFIX").$key;

        return setcookie($key,$value,$expire);
    }

    protected function cookie($key=false){

        $cookies = \helper::addslashes_deep ( $_COOKIE );

        if(!$key){
            return $cookies;
        }

        $key = \application::env("COOKIE_PREFIX").$key;

        if(array_key_exists($key,$cookies)){
            $result = $cookies[$key];
            $result = \kkcrypt::aes_cbc_decrypt($result,\application::env("APP_KEY"));
            return $result;
        }

        return false;

    }

    protected function post($key = false){
        $posts = \helper::addslashes_deep($_POST);
        if(!$key) {
            return $posts;
        }
        if(array_key_exists($key, $posts)) {
            return $posts[$key];
        }
        return false;
    }

    protected function get($key = false){
        $gets = \helper::addslashes_deep($_GET);
        if(!$key) {
            return $gets;
        }
        if(array_key_exists($key, $gets)) {
            return $gets[$key];
        }
        return false;
    }

    protected function user_agent () {
        $objua = false;
        $this->load_lib("ua");
        if($this->server_parameters("HTTP_USER_AGENT")) {
            $objua = new user_agent($this->server_parameters("HTTP_USER_AGENT"));
        }
        return $objua;
    }

    protected function server ($key = false) {
        $servers = helper::addslashes_deep($_SERVER);

        if(!$key) {
            return $servers;
        }

        if(array_key_exists($key, $servers)) {
            return $servers[$key];
        }

        return false;
    }

    final public static function path () {
        if(!\application::is_http_request()) {
            return false;
        }
        $argvs = explode("/", $_SERVER["REQUEST_URI"]);
        $result = false;
        foreach ($argvs as $argv) {
            if($argv) {
                $result[] = $argv;
            }
        }
        $result = \helper::addslashes_deep($result);

        return $result;

    }


}