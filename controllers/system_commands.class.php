<?php
namespace controllers;

class system_commands extends \console{

    public function test(){
        var_dump($this->argv);
    }

}