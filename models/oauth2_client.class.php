<?php 
namespace models;

if(!PFW_INIT){
	echo "break in";
	die;
}

class oauth2_client extends models {
	
	protected $client_id = false;
	
	function __construct($client_id){
		parent::__construct();
		$row=$this->db->get_row("select client_id from t_api_clients_list where client_id='$client_id'");
		if(!$row){
			$this->fatal("ClientID not found: $client_id");
			return;
		}
		$this->client_id=$client_id;
		return;
	}
	
	public function get_ac_by_wechat($wechat_account,$wechat_openid,$scope='basic'){
		$wechat_user = wechat_user::create($wechat_account, $wechat_openid);
		if(!is_object($wechat_user)){
			return false;
		}
		$ac = self::create_access_token($scope,$wechat_user);
		return $ac;
	}
	
	/**
	 * 创建一个access_token
	 * 
	 * @param string $scope 权限组
	 * @param object $user 归属用户，如果为false，则建立平台级access_token
	 * 
	 * @return object 返回access_token对象
	 */
	public function create_access_token($scope,$user = false){
		$UID=0;
		if(is_object($user)){
			$UID=$user->UID;
		}
		$row = $this->db->get_row("select access_token from t_api_tokens_list where UID={$UID} and client_id='{$this->client_id}' and at_expire_time>{$this->timestamp}");
		if($row){
			$ac = new access_token($row->access_token);
			if(is_object($ac)){
				$ac->set_scope($scope);
				return $ac;
			}
		}
		$at_expire_time = $this->timestamp + $this->at_expire_time;
		$rt_expire_time = $this->timestamp + $this->rt_expire_time;
		$access_token = access_token::make_token(64,"v400");
		$refresh_token = access_token::make_token(128,"v400");
		
		$this->db->query("insert into t_api_tokens_list (access_token,refresh_token,scope,UID,client_id,at_expire_time,rt_expire_time) values ('$access_token','$refresh_token','$scope',$UID,'{$this->client_id}',$at_expire_time,$rt_expire_time)");
		return new access_token($access_token);
		
	}
	
	/**
	 * 检查ClientID是否具有特定授权
	 * 
	 * @param string $scope 待检查的授权
	 * @return boolean
	 */
	public function check_scope($scope){
		$scope_list = $this->db->get_var("select scope_list from t_api_clients_list where client_id='{$this->client_id}'");
		if(strstr($scope_list, $scope)){
			return true;
		}
		return false;
	}
	
	/**
	 * 
	 * 检查client_secret是否正确
	 * @param string $client_secret
	 * @return boolean
	 */
	public function check_client_secret($client_secret){
		$var=$this->db->get_var("select client_secret from t_api_clients_list where client_id='{$this->client_id}'");
		if($client_secret==$var){
			return true;
		}
		return false;
	}
	
	protected function get_rt_expire_time(){
		$var=$this->db->get_var("select rt_expire_time from t_api_clients_list where client_id='{$this->client_id}'");
		return $var;
	}
	
	protected function get_at_expire_time(){
		$var=$this->db->get_var("select at_expire_time from t_api_clients_list where client_id='{$this->client_id}'");
		return $var;
	}
	
	protected function get_client_name(){
		$var=$this->db->get_var("select client_name from t_api_clients_list where client_id='{$this->client_id}'");
		return $var;
	}
		
	protected function get_client_id(){
		return $this->client_id;
	}
	
}