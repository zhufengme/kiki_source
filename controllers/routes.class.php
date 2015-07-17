<?php
namespace controllers;
class routes extends \web {

    function __construct() {
        parent::__construct();
        echo "load";
    }

    final public static function enter($http_path){

        if(!$http_path) {
            $welcome = new \controllers\welcome();
            $welcome->main();
        }

        if($http_path[0]=="test"){
           $test = new \controllers\test();
           $test->main();
        }
    }




}