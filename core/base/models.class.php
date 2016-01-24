<?php

class models extends \base {
    protected $db;

    function __construct () {
        parent::__construct();
        $this->db = self::get_db_connect($this->log);
        
    }

    function __destruct () {
        //$this->db->close();
    }

    protected static function get_db_connect ($obj_log = false) {
        $db_method = \application::env("DB_METHOD");

        if($db_method == "pdo") {
            $db = new \pdo_db(\application::env("DB_USER"),
                \application::env("DB_PASSWORD"),
                \application::env("DB_NAME"),
                \application::env("DB_HOST"),
                "utf8");
        } else {
            $db = new \mysql(\application::env("DB_USER"),
                \application::env("DB_PASSWORD"),
                \application::env("DB_NAME"),
                \application::env("DB_HOST"),
                "utf-8");
        }

        if($obj_log){
            $db->enable_log($obj_log);
        }

        return $db;
    }
}