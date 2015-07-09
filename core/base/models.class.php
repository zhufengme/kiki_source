<?php
namespace models;

if(!PFW_INIT){
	echo "break in";
	die;
}

class models extends \base{
	protected $db;
	
	function __construct(){
		parent::__construct();
		$this->load_lib("cache");
		$this->db=self::get_db_connect();
		$this->db->enable_log($this->log);
	}
	
	function __destruct(){
		$this->db->close();
	}
	
	protected static function get_db_connect(){
		$db=new \mysql(PFW_DB_USERNAME,PFW_DB_PASSWORD,PFW_DB_DBNAME,PFW_DB_HOST,"utf-8");		
		return $db;
	}
}