<?php
if(!PFW_INIT){
	echo "break in";
	die;
}

class output{
	
	
	public function out($str){
		echo $str;
	}
	
	public function add_http_header($key,$value){
		header("{$key}: {$value}");
		return;
	}
	
	public function set_cookie($key,$value,$expirein,$domain="/"){
		if(application::is_web_request()){
			setcookie(PFW_COOKIE_PREFIX . $key , $value ,time()+$expirein,"/");
		}
	}
	
}