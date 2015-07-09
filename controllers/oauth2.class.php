<?php
namespace controllers;


use models\user;

if(!PFW_INIT){
	echo "break in";
	die;
}

class oauth2 extends rest {
	
	function __construct(){
		parent::__construct();
		$this->force_ssl(true);
		return;
	}
	
	public function token(){
		$client_id = \helper::get_value_from_array($this->input->http_get, "client_id");
		$client_secret = \helper::get_value_from_array($this->input->http_get, "client_secret");
		try {
			$client = new \models\oauth2_client($client_id);
		} catch (\Exception $e){
			$this->api_response_error_message(110001,"request client id = $client_id");
			return;
		}
		$result = $client->check_client_secret($client_secret);
		if(!$result){
			$this->api_response_error_message(110002,"request client secret = $client_secret");
			return;
		}
		$scope = \helper::get_value_from_array($this->input->http_get, "scope");
		if(!$scope){
			$this->api_response_error_message(110005,"miss scope");
			return;
		}
		
		$grant_type = \helper::get_value_from_array($this->input->http_get, "grant_type");
		switch ($grant_type){
			case "wechat_account":
				$this->get_ac_by_wechat_account($client);
				break;
			case "uid":
				$this->get_ac_by_uid($client);
				break;
			default:
				$this->api_response_error_message(110004,"request grant_type  = $grant_type");
				break;
		}
		

		
	}
	
	private function get_ac_by_uid($client){
       if(!$client->check_scope("private")){
			$this->api_response_error_message(110003,"request client id = {$client->client_id}");
			return;
		}
		$UID = \helper::get_value_from_array($this->input->http_get, "uid");
		$scope = \helper::get_value_from_array($this->input->http_get, "scope");
		try{
			$user = new user($UID);
		}catch (\Exception $e){
			$this->api_response_error_message(110005,"uid error");
		}
		
		$ac = $client->create_access_token($scope,$user);
		if(!is_object($ac)){
			$this->api_response_error_message(110007);
			return;
		}
		$result = array(
				'access_token' => $ac->access_token,
				'refresh_token' => $ac->refresh_token,
				'access_token_expire_in' => $ac ->access_token_expire_in,
				'refresh_token_expire_in' => $ac ->refresh_token_expire_in,
				'scope' => $ac -> scope
		);
		$this->api_response($result);
		return;		
		
	}
	
	private function get_ac_by_wechat_account($client){
		if(!$client->check_scope("private")){
			$this->api_response_error_message(110003,"request client id = {$client->client_id}");
			return;			
		}
		$wechat_account = \helper::get_value_from_array($this->input->http_get, "wechat_account");
		$wechat_openid = \helper::get_value_from_array($this->input->http_get, "wechat_openid");
		if(!$wechat_account || !$wechat_openid){
			$this->api_response_error_message(110005,"in get_ac_by_wechat_account");
			return;
		}
		$scope = \helper::get_value_from_array($this->input->http_get, "scope");
		$ac = $client->get_ac_by_wechat($wechat_account,$wechat_openid,$scope);
		if(!is_object($ac)){
			$this->api_response_error_message(110006);
			return;
		}
		$result = array(
				'access_token' => $ac->access_token,
				'refresh_token' => $ac->refresh_token,
				'access_token_expire_in' => $ac ->access_token_expire_in,
				'refresh_token_expire_in' => $ac ->refresh_token_expire_in,
				'scope' => $ac -> scope
		);
		$this->api_response($result);
		return;
	}
	
	public function test(){
		$arr=array(
				'key1' => "1",
				"key2" => "2",
		);
		$result=array(
				'bbool' => true,
				'nnumber' => 123,
				'sstring' => "aaa",
				'aarray' => $arr,
				
		);
		

		
		$this->api_response($result);

	}
	
	
}