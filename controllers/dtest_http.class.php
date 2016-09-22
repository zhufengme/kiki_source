<?php
namespace controllers;

class dtest_http extends \rest  {
    function __construct () {
        parent::__construct();
    }

    public function main(){
        $r = new \stdClass();
        $r->a = "a";
        $this->api_response($r);

    }

    //put codes here
}