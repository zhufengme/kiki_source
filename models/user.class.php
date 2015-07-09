<?php 
namespace models;

if(!PFW_INIT){
	echo "break in";
	die;
}

class user extends models {
	
	protected $UID = false;
	private $cache_key = false;
	
	function __construct($UID){
		parent::__construct();
		$this->UID= (int) $UID;
		$this->cache_key = __CLASS__.":{$UID}";
		
		if(!$this->cache->exists($this->cache_key)){
			$row=$this->db->get_row("select UID from t_users_list where UID={$this->UID}");
			if(!$row){
				throw new \Exception("UID not found");
				return;
			}
		}
		
		$this->cache->hash_set($this->cache_key,"_",$this->timestamp,3600);
		
		return;
	}
	
	public function get_info(){
		$cache_result=$this->cache->hash_get($this->cache_key);		
		$result_list=array('UID','username','nickname','is_active');
		
		foreach ($result_list as $key){
			if(key_exists($key, $cache_result)){
				$result[$key] = $cache_result[$key];
			}else{
				$cmd_str="\$var=\$this->get_{$key}();";
				eval($cmd_str);
				$result[$key] = $var;
			}
		}
		
		return $result;
	}
	
	protected function get_UID(){
		return $this->UID;
	}

	protected function get_is_active(){
		if(!$this->cache->hash_exists($this->cache_key,"is_active")){
			$result=$this->db->get_var("select is_active from t_users_list where UID={$this->UID}");			
			if($result==1){
				$result = true;
			}else{
				$result = false;
			}
			$this->cache->hash_set($this->cache_key,"is_active",$result);
		}else{
			$result=$this->cache->hash_get($this->cache_key,"is_active");
		}

		
		return $result;
	}	
	
	protected function get_nickname(){
		if(!$this->cache->hash_exists($this->cache_key,"nickname")){
			$result=$this->db->get_var("select nickname from t_users_list where UID={$this->UID}");
			$this->cache->hash_set($this->cache_key,"nickname",$result);
		}else{
			$result=$this->cache->hash_get($this->cache_key,"nickname");
		}	
		return $result;		
	}
	
	protected function get_username(){
		if(!$this->cache->hash_exists($this->cache_key,"username")){
			$result=$this->db->get_var("select username from t_users_list where UID={$this->UID}");
			$this->cache->hash_set($this->cache_key,"username",$result);
		}else{
			$result=$this->cache->hash_get($this->cache_key,"username");
		}
		return $result;
	}
	
	/**
	 * 创建一个虚用户
	 */
	public static function create(){
		$db=self::get_db_connect();
		$result= $db->query("insert into t_users_list (username,log_time) values ('guest'," . time() . ")");
		if($result){
			$UID= $db->insert_id;
			$user = new user($UID);
			return $user;
		}
		return false;
	}
	

}