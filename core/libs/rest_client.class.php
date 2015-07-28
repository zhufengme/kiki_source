<?php

/**
 * 
 *  API 调用类
 * @author zhufeng
 *
 */

class rest_client {
	private $data = array ();
	private $access_token = false;
	private $url_prefix = null;
	private $_api_return = null;
	
	public function __construct($url_prefix,$access_token=false) {
		$this->access_token = $access_token;
		if($this->access_token){
			$this->add_data_field ( "access_token", $this->access_token );
		}
		$this->url_prefix = $url_prefix;
	
	}
	
	public function add_data_field($key, $value) {
		$this->data [$key] = $value;
	}
	
	public function get_api_return() {
		return $this->_api_return;
	}
	
	private function data_to_string() {
		$str = null;
		if (! count ( $this->data )) {
			return false;
		}
		foreach ( $this->data as $key => $value ) {
			$value = urlencode ( $value );
			$str .= $key . "=" . $value;
			$str .= "&";
		}
		$str = substr ( $str, 0, strlen ( $str ) - 1 );
		return $str;
	}
	

	
	/**
	 * 
	 * 发起API请求
	 * 
	 * @param string $api_name API接口名，如 user/add 
	 * @param string $method 请求方法，GET或者POST，或者MULT（用于文件上传类接口）
	 * @param string $is_array 是否以数组格式返回结果，默认返回的是对象
	 * 
	 */
	public function api_request($api_name, $method = 'GET', $is_array = false) {
		$url_prefix = $this->url_prefix . $api_name . "/";
		$api_return = false;
		for($i = 1; $i <= 3; $i ++) {
			if ($method == "GET") {
				$url = $url_prefix . "?" . $this->data_to_string ();
				$api_return = helper::http_request ( $url );
			} else {
				if($method=='MULT'){
					$api_return = helper::http_request ( $url_prefix, 'POST', $this->data);
				}
				if($method=='POST'){
					$api_return = helper::http_request ( $url_prefix, $method, $this->data_to_string () );
				}
			}
			if($api_return){
				break;
			}
			
			usleep(300000);
		}
		
		if(!$api_return){
			throw new Exception("API connection error : {$url_prefix}");
			exit;
		}
				
		$this->_api_return = $api_return;
		$api_array = json_decode ( $api_return, $is_array );
		
		/*
		if($is_array){
			return $api_array;
		}else{
			$obj=new ArrayObject($api_array);
			return $obj;
		}
		*/
		return $api_array;
	}

}