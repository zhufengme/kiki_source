<?php
class rest extends \http {
	
	protected $access_token = false;
	
	function __construct(){
		parent::__construct();
		$this->get_access_token();
	}
	
	
	private function get_access_token(){
		
		$Authorization = $this->server('HTTP_AUTHORIZATION');
		if($Authorization){
			if(substr($Authorization, 0,6)=="OAuth2"){
				list($_t,$access_token)=explode(" ", $Authorization);
				$access_token=urldecode($access_token);
				$this->access_token=$access_token;
				return;
			}
		}
		
		$access_token = $this->post('access_token');
		if($access_token){
			$this->access_token=$access_token;
			return;
		}
		
		$access_token = $this->get('access_token');
		if($access_token){
			$this->access_token=$access_token;
			return;
		}

		return false;
	}
	
	/**
	 * 返回API错误信息
	 *
	 * @param int $error_code 错误码
	 * @param string $error_msg 错误信息
	 *
	 */
	protected function api_response_error_message($error_code,$addin_msg = false){

		$obj_result = \application::config("rest","errors");
		if(!$obj_result){
			throw new Exception("error setting not found, check rest.json file");
			die;
		}

		if(!property_exists($obj_result,"{$error_code}")){
			throw new Exception("error not found, check rest.json file");
			die;
		}

		$obj_result = $obj_result->$error_code;

		$obj_return = new stdClass();
		$obj_return->error_code = $error_code;
		$obj_return->message = $obj_result->message;

		if($addin_msg) {
			$obj_return->message .= " : ". $addin_msg;
		}

		$http_code = $obj_result->http_code;

		$this->api_response($obj_return,$http_code);
		die;

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


		$this->set_http_code($http_code);
	
		$callback=$this->get_callback();
		if(is_array($result) || is_object($result)){
			$json=json_encode($result);
		}else{
			$json=$result;
		}
	
		if(!\application::is_http_request()){
			throw new Exception("REST request must http request");
			return;
		}

		if(!$callback){
			$this->add_http_header("Content-type","application/json");
		}else{
			$this->add_http_header("Content-type", "text/javascript");
			$json=$callback."(".$json;
			$json=$json.");";
		}
		$this->add_acao();
		$this->log->info("rsp: " . $json);
		$this->output->out($json,false,true);

		return;
	}
	
	private  function get_callback(){
		if(!\application::is_http_request()){
			return false;
		}
		$callback=$this->get('callback');
		if(!$callback){
			$callback=$this->post('callback');
		}
		return $callback;
	}
	
	private function add_acao(){		
		$this->add_http_header("Cache-Control","no-cache");
		$this->add_http_header("Access-Control-Allow-Origin", "*");
		$this->add_http_header("Access-Control-Allow-Headers", "Authorization, DNT,X-Mx-ReqToken,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type");
		$this->add_http_header("Access-Control-Allow-Methods", "GET, POST, PUT, DELETE, OPTIONS");
		$this->add_http_header("Access-Control-Allow-Credentials", "true");
		$this->add_http_header("Access-Control-Max-Age", "1728000");
		return;
	}
}