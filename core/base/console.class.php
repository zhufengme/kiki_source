<?php

class console extends \base {

    protected $argv = false;

    function __construct ($argv) {
        parent::__construct();
        if(\application::is_http_request()) {
            $this->log->fatal("request not in console");
            $this->output->fatal("request not in console");
            return;
        }
        $this->argv = $argv;
    }

    final public static function start ($argv) {
        if(count($argv)<=1){
            new \controllers\console($argv);
            return;
        }

        list($command_class,$command_method)=explode(":",$argv[1]);

        if(!$command_method){
            $command_method=$command_class;
            $command_class="system";
        }



    }

    protected function main_help(){

    }


}