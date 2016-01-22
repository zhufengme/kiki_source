<?php
namespace models;

class dtest extends \models {
    function __construct () {
        parent::__construct();
    }


    function test(){
        var_dump($this->db->get_row("select * from test limit 10"));
        //var_dump($this->db->insert_id);
    }

    //put codes here

}