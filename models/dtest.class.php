<?php
namespace models;

class dtest extends \models {
    function __construct () {
        parent::__construct();
    }


    function test(){
        var_dump($this->db->query("insert into test (username,age,gender) values ('中文',40,'M')"));
        var_dump($this->db->insert_id);
    }

    //put codes here

}