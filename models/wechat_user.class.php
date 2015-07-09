<?php
namespace models;

if(!PFW_INIT){
	echo "break in";
	die;
}

class wechat_user extends user {

	private $cache_key = false;

	function __construct($UID){
		parent::__construct($UID);
		$this->cache_key = __CLASS__.":{$UID}";
		
		
		if(!$this->cache->exists($this->cache_key)){
			$row=$this->db->get_row("select UID from t_wechat_users_list where UID={$this->UID}");
			if(!$row){
				throw new \Exception("wechat user not found");
				return;
			}
		}
		
		$this->cache->hash_set($this->cache_key,"_",$this->timestamp,3600);
		
		return;
	}
	
	protected function get_session(){
		$ws_id = $this->db->get_var("select ws_id from t_wechat_sessions where UID={$this->UID} and expire_time>{$this->timestamp}");
		if($ws_id){
			return new wechat_session($ws_id);
		}
		return false;
	}
	
	public static function create($wechat_account,$wechat_openid){
		$db=self::get_db_connect();
		$timestamp = time();

		$row = $db->get_row("select UID from t_wechat_users_list where wechat_account='{$wechat_account}' and wechat_openid='{$wechat_openid}'");
		if($row){
			$user = new \models\wechat_user($row->UID);
			$user -> active();
			return $user;
		}
		
		$user=\models\user::create();
		$UID=$user->UID;
		
		$result = $db->query("insert into t_wechat_users_list (UID,wechat_account,wechat_openid,create_time,last_active) values ($UID,'{$wechat_account}','{$wechat_openid}',{$timestamp},{$timestamp})");
		if($result){
			$user = new \models\wechat_user($UID);
			return $user;
		}
		return false;
	}
	
	/**
	 * 重设微信用户上次活跃时间
	 */
	public function active(){
		$this->db->query("update t_wechat_users_list set last_active={$this->timestamp} where UID={$this->UID}");
		return;
	}
	
}