<?php
namespace models;

class dtest extends \models {
    function __construct () {
        parent::__construct();
    }


    function test(){

        //$this->db->add_trans_sql("update test set age1='abc' where uid=1");
        //$this->db->add_trans_sql("insert into test (username,gender,age) values ('andie','F','22')");

        var_dump($this->db->trans_commit());

        //var_dump($this->db->get_row("select * from test limit 10"));
        //var_dump($this->db->insert_id);
    }

    //put codes here

}