<?php
class http extends \base {

    function __construct() {
        parent::__construct();
        if(!\application::is_http_request()){
            $this->log->fatal("not in http request");
            throw new \Exception("not in http request!");
            return;
        }
    }

    public function start(){
        \controllers\routes::enter(self::get_path());
        return;
    }

    private static function get_path(){
        if(!\application::is_http_request()){
            return false;
        }
        $argvs=explode("/", $_SERVER["REQUEST_URI"]);
        $result=false;
        foreach ($argvs as $argv){
            if($argv){
                $result[]=$argv;
            }
        }
        $result = \helper::addslashes_deep($result);

        return $result;

    }


}