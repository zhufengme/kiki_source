<?php 
namespace models;

use controllers\oauth2;
if(!PFW_INIT){
	echo "break in";
	die;
}

class access_token extends models {
	
	private $access_token = false;
	private $cache_key = false;
	
	
	function __construct($access_token){
		parent::__construct();
		$this->access_token = $access_token;
		$this->cache_key = __CLASS__.":{$access_token}";
		
		if(!$this->cache->exists($this->cache_key)){
			$row=$this->db->get_row("select token_id,at_expire_time from t_api_tokens_list where access_token='{$access_token}' and at_expire_time>{$this->timestamp} and status='Y'");
			if(!$row){
				$this->fatal("token not found or expire: $access_token");
				return;
			}
			$this->cache->hash_set($this->cache_key,"_",$this->timestamp,$row->at_expire_time - $this->timestamp);
		}

		return;
		
	}
	
	/**
	 * 验证access_token是否具备相应授权
	 * 
	 * @param string $scope 所需授权
	 * @param object $user 归属用户，如果为false，则验证平台授权
	 * 
	 * @return int 返回值
	 * 				1 - 成功，access_token有效
	 * 				-1 - access_token 不存在或已过期
	 * 				-2 - 不满足授权
	 * 				
	 */
	public static function valid($access_token,$scope,$user=false){
		if(!access_token::exist($access_token)){
			return -1;
		}
		$ac = new access_token($access_token);
		if(!$user){
			$result = $ac->client->check_scope($scope);
			if(!$result){
				return -2;
			}
		}else{
			$result = $ac->check_scope($scope);
			if(!$result){
				return -2;
			}
		}
		return true;
	}
	
	/**
	 * 检查access_token是否存在或有效
	 * @param string $access_token
	 */
	public static function exist($access_token){
		$db=self::get_db_connect();
		$timestamp = self::get_timestamp();
		$row=$db->get_row("select token_id from t_api_tokens_list where access_token='{$access_token}' and at_expire_time>{$timestamp} and status='Y'");
		if($row){
			return true;
		}
		return false;
	}
	
	/**
	 * 检查access_token是否具有特定授权
	 *
	 * @param string $scope 待检查的授权
	 * @return boolean
	 */
	public function check_scope($scope){
		$scope_list = $this->db->get_var("select scope from t_api_tokens_list where access_token='{$this->access_token}'");
		if(strstr($scope_list, $scope)){
			return true;
		}
		return false;
	}
	
	
	protected function get_scope(){
		if(!$this->cache->hash_exists($this->cache_key,"scope")){
			$result=$this->db->get_var("select scope from t_api_tokens_list where access_token='{$this->access_token}'");
			$this->cache->hash_set($this->cache_key,"scope",$result);
		}else{
			$result=$this->cache->hash_get($this->cache_key,"scope");
		}
		
		if($result){
			return $result;
		}
		
		return false;		
	}
	
	public function set_scope($scope){
		$this->cache->hash_del($this->cache_key,"scope");
		$this->db->query("update t_api_tokens_list set scope='$scope' where access_token='{$this->access_token}'");
		return;
	}
	
	protected function get_user(){
		if(!$this->cache->hash_exists($this->cache_key,"UID")){
			$result=$this->db->get_var("select UID from t_api_tokens_list where access_token='{$this->access_token}'");
			$this->cache->hash_set($this->cache_key,"UID",$result);
		}else{
			$result=$this->cache->hash_get($this->cache_key,"UID");
		}
		
		if($result){
			return new user($result);
		}
		
		return false;		
	
	}
	
	protected function get_client(){
		if(!$this->cache->hash_exists($this->cache_key,"client_id")){
			$result=$this->db->get_var("select client_id from t_api_tokens_list where access_token='{$this->access_token}'");
			$this->cache->hash_set($this->cache_key,"client_id",$result);
		}else{
			$result=$this->cache->hash_get($this->cache_key,"client_id");
		}
	
		if($result){
			return new oauth2_client($result);
		}
	
		return false;
	}
	
	protected function get_refresh_token(){
		if(!$this->cache->hash_exists($this->cache_key,"refresh_token")){
			$result=$this->db->get_var("select refresh_token from t_api_tokens_list where access_token='{$this->access_token}'");
			$this->cache->hash_set($this->cache_key,"refresh_token",$result);
		}else{
			$result=$this->cache->hash_get($this->cache_key,"refresh_token");
		}

		return $result;
	}
	
	protected function get_access_token_expire_in(){
		if(!$this->cache->hash_exists($this->cache_key,"at_expire_time")){
			$result=$this->db->get_var("select at_expire_time from t_api_tokens_list where access_token='{$this->access_token}'");
			
			$this->cache->hash_set($this->cache_key,"at_expire_time",$result);
		}else{
			$result=$this->cache->hash_get($this->cache_key,"at_expire_time");
		}
		
		$result = (int) $result - $this->timestamp;
		return $result;
	}
	
	protected function get_refresh_token_expire_in(){
		if(!$this->cache->hash_exists($this->cache_key,"rt_expire_time")){
			$result=$this->db->get_var("select rt_expire_time from t_api_tokens_list where access_token='{$this->access_token}'");			
			$this->cache->hash_set($this->cache_key,"rt_expire_time",$result);
		}else{
			$result=$this->cache->hash_get($this->cache_key,"rt_expire_time");
		}
	
		$result = (int) $result - $this->timestamp;
		return $result;
	}
	
	public static function make_token($len,$prefix=false){
		$str_len = $len;
		if($prefix){
			$str_len = $len - strlen($prefix);
		}
		$str = \helper::make_rand_string($str_len);
		if($prefix){
			$str = $prefix . $str;
		}
		return $str;
	}
	
	protected function get_access_token(){
		return $this->access_token;
	}
}