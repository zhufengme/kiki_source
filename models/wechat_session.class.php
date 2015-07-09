<?php
namespace models;

if(!PFW_INIT){
	echo "break in";
	die;
}

class wechat_session extends models {
	
	private $ws_id = false;
	
	function __construct($ws_id){
		parent::__construct();

		$row=$this->db->get_row("select ws_id from t_wechat_sessions where ws_id={$ws_id} and expire_time>{$this->timestamp}");
		if(!$row){
			$this->fatal("wechat session not found or expired");
			return;
		}
		
		$this->ws_id = $ws_id;
		
		return;
		
	}
	
	protected function get_lat(){
		$var = $this->db->get_var("select lat from t_wechat_sessions where ws_id = {$this->ws_id}");
		return $var;
	}
	
	protected function get_lon(){
		$var = $this->db->get_var("select lon from t_wechat_sessions where ws_id = {$this->ws_id}");
		return $var;		
	}
	
	protected function get_user(){
		$UID = $this->db->get_var("select UID from t_wechat_sessions where ws_id = {$this->ws_id}");
		return new wechat_user($UID);
	}
	
	public static function exist($wechat_user){
		if(!is_object($wechat_user)){
			return false;
		}
		$db=self::get_db_connect();
		$row = $db->get_row("select ws_id from t_wechat_sessions where UID={$wechat_user->UID} and expire_time>" . self::get_timestamp());
		if($row){
			return true;
		}
		return false;
	}
	
	public static function create($wechat_user,$lat,$lon){
		
		if(!is_object($wechat_user)){
			return false;
		}
		if(!is_numeric($lat) || !is_numeric($lon)){
			return false;
		}
		
		$db=self::get_db_connect();
		self::clear_expired_sessions();
		$ws_id=false;
		$create_time = self::get_timestamp();
		$expire_time = $create_time + 600;

		$row = $db->get_row("select ws_id from t_wechat_sessions where UID={$wechat_user->UID} and expire_time>" . self::get_timestamp());

		if(!$row){
			$db->query("insert into t_wechat_sessions (lat,lon,create_time,expire_time,UID) values ({$lat},{$lon},$create_time,$expire_time,{$wechat_user->UID})");
			$ws_id = $db->insert_id;
		}else{
			$db->query("update t_wechat_sessions set lat={$lat},lon={$lon},expire_time={$expire_time} where ws_id={$row->ws_id}");
			$ws_id = $row->ws_id;
		}
		if($ws_id){
			return new wechat_session($ws_id);
		}
		return false;
	}
	
	public static function clear_expired_sessions(){		
		$db=self::get_db_connect();
		$db->query("delete from t_wechat_sessions where expire_time<" . self::get_timestamp() - 86400);
		return;
	}
	
	
	
}