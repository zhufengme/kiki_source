<?php

define('OBJECT', 'OBJECT', true);
define('ARRAY_A', 'ARRAY_A', true);
define('ARRAY_N', 'ARRAY_N', true);
define('ASSOC', 'ASSOC', true);


class pdo_db {

    private $db = false;
    private $log = false;
    private $arr_sqls = false;

    function __construct ($db_user, $db_password, $db_name, $db_host, $db_encoding = 'utf8') {

        $str_dsn = "mysql:dbname={$db_name};host={$db_host}";
        try {
            $this->db = new \PDO($str_dsn, $db_user, $db_password);
            $this->db->exec("set NAMES $db_encoding");
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            echo "DB Connection error: $e->getMessage() \n";
            $this->write_log("DB Connection error: $e->getMessage()", "error");
            die;
        }
        return;
    }

    public function add_trans_sql ($str_prepare_sql,$array_paraments = null) {
        $this->arr_sqls[] = array(
            'str_prepare_sql' => $str_prepare_sql,
            'array_paraments' => $array_paraments
        );
    }

    public function trans_commit () {

        $result = false;

        if(!$this->arr_sqls) {
            $this->write_log("No sql queue existed.", "error");
            throw new Exception("No sql queue existed.");
        }

        $this->db->setAttribute(PDO::ATTR_AUTOCOMMIT, 0);

        $this->db->beginTransaction();
        try {
            foreach ($this->arr_sqls as $arr_sql) {
                $this->write_log("Trans-SQL: " . serialize($arr_sql));
                $result = $this->query($arr_sql['str_prepare_sql'],$arr_sql['array_paraments']);
                if (!$result){
                    $this->db->rollBack();
                    $this->write_log("Trans-SQL rollback");
                    $this->db->setAttribute(PDO::ATTR_AUTOCOMMIT, 1);
                    $this->arr_sqls = false;
                    return $result;
                }
            }
            $this->db->commit();
        } catch (Exception $e){
            $this->db->rollBack();
            $this->write_log("Trans-SQL ERROR: $e->getMessage()");
            $result=false;
        }

        $this->db->setAttribute(PDO::ATTR_AUTOCOMMIT, 1);

        $this->arr_sqls = false;

        return $result;

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

        if(!$obj_s) {
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

    public function get_row ($str_prepare_sql,$array_paraments = null, $output = OBJECT) {

        $this->write_log("SQL: $str_prepare_sql");

        $row = false;

        try {
            $stmt= $this->db->prepare($str_prepare_sql);
            $stmt->execute($array_paraments);
            if($output== OBJECT){
                $row = $stmt->fetch(PDO::FETCH_OBJ);
            }else{
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
            }

        } catch (\PDOException $e) {
            echo "DB Query error: $e->getMessage() \n";
            $this->write_log("DB Query error: $e->getMessage()", "error");
        }


        return $row;

    }

    public function get_results ($str_prepare_sql,$array_paraments = null, $output = OBJECT) {
        $this->write_log("SQL: $str_prepare_sql");

        $result = false;

        try {
            $stmt= $this->db->prepare($str_prepare_sql);
            $stmt->execute($array_paraments);
            if($output== OBJECT){
                $result = $stmt->fetchAll(PDO::FETCH_OBJ);
            }else{
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

        } catch (\PDOException $e) {
            echo "DB Query error: $e->getMessage() \n";
            $this->write_log("DB Query error: $e->getMessage()", "error");
        }

        return $result;

    }

    public function query ($str_prepare_sql,$array_paraments = null) {
        $this->write_log("SQL: $str_prepare_sql");

        $result = false;

        try {
            $stmt= $this->db->prepare($str_prepare_sql);
            $result = $stmt->execute($array_paraments);
        } catch (\PDOException $e) {
            echo "DB Query error: $e->getMessage() \n";
            $this->write_log("DB Query error: $e->getMessage()", "error");
        }

        return $result;
    }

    public function get_insertid () {
        return $this->db->lastInsertId();
    }

    function __get ($name) {
        switch ($name) {
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