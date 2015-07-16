<?php

class input{
	
	private $http_get = false;
	private $http_post = false;
	private $http_post_raw = false;
	private $http_request = false;
	private $http_server = false;
	private $user_agent = false;
	private $user_cookies = false;
	
	public function __get($name){
		$arguments_list = array ('path','http_get', 'http_post', 'http_post_raw', 'http_request', 'http_server', 'user_agent');
		if(in_array($name, $arguments_list)){
			$cmd = "\$result = \$this -> {$name};";
			eval($cmd);
            return $result;
		}
	}

	public function __construct(){
		$this->get_http_data();
		$this->get_user_agent();
	}
	
	public function get_cookie($key,$domain="/"){
		return helper::get_value_from_array($this->http_cookies, PFW_COOKIE_PREFIX . $key);
	}
	
	private function get_user_agent(){
		if(!application::is_http_request()){
			return;
		}
		require_once KKF_LIBS_PATH . DIRECTORY_SEPARATOR . 'user_agent.class.php';
		$ua= helper::get_value_from_array($this->http_server, 'HTTP_USER_AGENT');
		if($ua){
			$this->user_agent=new user_agent($ua);
		}
		return;
	}
	

	private function get_http_data(){
		if(!\application::is_http_request()){
			return false;
		}
		if (! get_magic_quotes_gpc ()) {
			if (! empty ( $_GET )) {
				$this->http_get = helper::addslashes_deep ( $_GET );
			}else{
				$this->http_get = $_GET;
			}
			if (! empty ( $_POST )) {
				$this->http_post = helper::addslashes_deep ( $_POST );
			}else{
				$this->http_post = $_POST;
			}
			
			$this->http_cookies = helper::addslashes_deep ( $_COOKIE );
			$this->http_request = helper::addslashes_deep ( $_REQUEST );
			$this->http_server = helper::addslashes_deep ( $_SERVER );
			$this->http_post_raw = file_get_contents("php://input");
		
		}
	}
	
	

}
