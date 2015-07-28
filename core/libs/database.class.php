<?php

require_once "ezsql/ez_sql_core.php";
require_once "ezsql/ez_sql_mysql.php";

class mysql {
	
	private $db = false;
	private $log = false;
	
	function __construct($db_user,$db_password,$db_name,$db_host,$db_encoding){
		$this->db=new \ezSQL_mysql($db_user,$db_password,$db_name,$db_host,$db_encoding);
		return;
	}
	
	public function enable_log($obj_log){
		$this->log = $obj_log;
	}
	
	public function get_var($sql){
		$this->write_log("SQL: $sql");
		return $this->db->get_var($sql);
	}
	
	public function get_row($sql,$output=OBJECT){
		$this->write_log("SQL: $sql");
		return $this->db->get_row($sql,$output);
		
	}
	
	public function get_results($sql,$output=OBJECT){
		$this->write_log("SQL: $sql");
		return $this->db->get_results($sql,$output);
	}
	
	public function query($sql){
		$this->write_log("SQL: $sql");
		return $this->db->query($sql);
	}
	
	
	function __get($name){
		$result = false;
		eval("\$result = \$this->db->{$name};");
		return $result;
	}
	
	private function write_log($str,$level="info"){
		if(is_object($this->log)){
			/*
			$cmd_str="\$this->log->{$level}(\"{$str}\");";
			eval($cmd_str);
			*/
			$this->log->$level($str);
		}
	}
	
	function __destruct(){
		$this->close();
		return;
	}
	
	public function close(){
		$this->db->disconnect();
		$this->write_log("DB Closed.");
		return;
	}
	
}