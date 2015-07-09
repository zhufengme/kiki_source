<?php
namespace controllers;

if(!PFW_INIT){
	echo "break in";
	die;
}

class rest extends \base {
	
	protected $access_token = false;
	
	function __construct(){
		parent::__construct();
		//$this->get_access_token();
	}
	
	
	private function get_access_token(){
		
		$Authorization = \helper::get_value_from_array($this->input->http_server, 'HTTP_AUTHORIZATION');
		if($Authorization){
			if(substr($Authorization, 0,6)=="OAuth2"){
				$arr_oauth2=explode(" ", $Authorization);
				$access_token=\helper::get_value_from_array($arr_oauth2, 1);
				$access_token=urldecode($access_token);
				$this->access_token=$access_token;
				return;
			}
		}
		
		$access_token = \helper::get_value_from_array($this->input->http_post,'access_token');
		if($access_token){
			$this->access_token=$access_token;
			return;
		}
		
		$access_token = \helper::get_value_from_array($this->input->http_get,'access_token');
		if($access_token){
			$this->access_token=$access_token;
			return;
		}
		
	}
	
	/**
	 * 返回API错误信息
	 *
	 * @param int $error_code 错误码
	 * @param string $error_msg 错误信息
	 *
	 */
	protected function api_response_error_message($error_code,$addin_msg = false){
		$service = new \models\service();
		$result = $service->get_error_info_by_code($error_code , $addin_msg);
		$http_code = $result['http_code'];
		unset($result['http_code']);
		$this->api_response($result,$http_code,true);
		die();
		return;
	}
	
	/**
	 * 输出API返回结果
	 *
	 * @param string $result 返回结果的json字符串
	 * @param int $http_code http错误码
	 * @param int $number_on 是否强制把数字输出成字符串
	 *
	 */
	protected function api_response($result,$http_code=200){
	
		\helper::response_http_code($http_code);
	
		$callback=$this->get_callback();
		if(is_array($result) || is_object($result)){
			$json=json_encode($result);
		}else{
			$json=$result;
		}
	
		if(!\application::is_web_request()){
			$this->output->out($json);
			return;
		}
		if(!$callback){
			header("Content-type: application/json");
		}else{
			header("Content-type: text/javascript");
			$json=$callback."(".$json;
			$json=$json.");";
		}
		$this->add_acao();
		$this->log->info("rsp: " . $json);
		$this->output->out($json);
		return;
	}
	
	private  function get_callback(){
		if(!\application::is_web_request()){
			return false;
		}
		$callback=\helper::get_value_from_array($this->input->http_get, 'callback');
		if(!$callback){
			$callback=\helper::get_value_from_array($this->input->http_post, 'callback');
		}
		return $callback;
	}
	
	private function add_acao(){		
		$this->output->add_http_header("Cache-Control","no-cache");
		$this->output->add_http_header("Access-Control-Allow-Origin", "*");
		$this->output->add_http_header("Access-Control-Allow-Headers", "Authorization, DNT,X-Mx-ReqToken,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type");
		$this->output->add_http_header("Access-Control-Allow-Methods", "GET, POST, PUT, DELETE, OPTIONS");
		$this->output->add_http_header("Access-Control-Allow-Credentials", "true");
		$this->output->add_http_header("Access-Control-Max-Age", "1728000");
		return;
	}
}