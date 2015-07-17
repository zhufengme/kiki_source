<?php
namespace controllers;
class routes extends \web {

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