<?php

class pdo_db {

    private $db = false;
    private $log = false;

    function __construct ($db_user, $db_password, $db_name, $db_host, $db_encoding='utf8') {
        $str_dsn = "mysql:dbname={$db_name};host={$db_host}";
        try {
            $this->db = new \PDO($str_dsn, $db_user, $db_password);
            $this->db -> exec("set NAMES $db_encoding");
        } catch (\PDOException $e) {
            echo "DB Connection error: $e->getMessage() \n";
            $this->write_log("DB Connection error: $e->getMessage()", "error");
            die;
        }
        return;
    }

    public function enable_log ($obj_log) {
        $this->log = $obj_log;
    }

    public function get_var ($sql) {
        $this->write_log("SQL: $sql");

        $obj_s = false;

        try {
            $obj_s = $this->db->query($sql, \PDO::FETCH_NUM);
        } catch (\PDOException $e) {
            echo "DB Query error: $e->getMessage() \n";
            $this->write_log("DB Query error: $e->getMessage()", "error");
        }

        if(!$obj_s){
            echo "DB Query error.\n";
            $this->write_log("DB Query error: $sql", "error");
            return false;
        }

        $result = $obj_s->fetch();
        if(count($result) >= 1) {
            return $result[0][0];
        }

        return false;

    }

    public function get_row ($sql, $output = OBJECT) {

        $this->write_log("SQL: $sql");

        $obj_s = false;

        try {
            $obj_s = $this->db->query($sql, \PDO::FETCH_OBJ);
        } catch (\PDOException $e) {
            echo "DB Query error: $e->getMessage() \n";
            $this->write_log("DB Query error: $e->getMessage()", "error");
        }

        if(!$obj_s){
            echo "DB Query error.\n";
            $this->write_log("DB Query error: $sql", "error");
            return false;
        }

        $result = $obj_s->fetch();

        return $result;

    }

    public function get_results ($sql, $output = OBJECT) {
        $this->write_log("SQL: $sql");

        $obj_s = false;

        try {
            $obj_s = $this->db->query($sql, \PDO::FETCH_OBJ);
        } catch (\PDOException $e) {
            echo "DB Query error: $e->getMessage() \n";
            $this->write_log("DB Query error: $e->getMessage()", "error");
        }

        if(!$obj_s){
            echo "DB Query error.\n";
            $this->write_log("DB Query error: $sql", "error");
            return false;
        }

        $result = $obj_s->fetchAll();

        return $result;
    }

    public function query ($sql) {
        $this->write_log("SQL: $sql");

        $result = false;

        try {
            $result = $this->db->exec($sql);
        } catch (\PDOException $e) {
            echo "DB Query error: $e->getMessage() \n";
            $this->write_log("DB Query error: $e->getMessage()", "error");
        }

        return $result;
    }

    public function get_insertid(){
        return $this->db->lastInsertId();
    }

    function __get ($name) {
        switch($name){
            case 'insert_id':
                return $this->get_insertid();
                break;
        }
    }

    private function write_log ($str, $level = "info") {
        if(is_object($this->log)) {
            /*
            $cmd_str="\$this->log->{$level}(\"{$str}\");";
            eval($cmd_str);
            */
            $this->log->$level($str);
        }
    }

    function __destruct () {
        $this->close();
        return;
    }

    public function close () {
        $this->db = null;
        $this->write_log("DB Closed.");
        return;
    }
}