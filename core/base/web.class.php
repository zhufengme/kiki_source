<?php
namespace controllers;

if(!PFW_INIT){
	echo "break in";
	die;
}

class web extends \base {

	protected $view = false;
	
	function __construct(){
		parent::__construct();
		if(!\application::is_web_request()){
			$this->log->fatal("not web request");
			throw new \Exception("not web request!");
			return;
		}
		$this->load_lib("view");
	}
	
	protected function set_view($view_name,$default_lang,$default_device){
		if(!$default_lang){
			throw new \Exception("default lang must be set");
			die;
		}
		if(!$default_device){
			throw new \Exception("default device must be set");
			die;
		}
		if(!strstr(PFW_ALLOW_LANGS, $default_lang)){
			throw new \Exception("default lang not allow");
			die;
		}
		$default_device = strtolower($default_device);
		$default_lang = strtolower($default_lang);
		
		$lang = $this->get_lang();
		if(!$lang){
			$lang= $default_lang;
		}
		$device = $this->get_device();
		if(!$device){
			$device= $default_device;
		}
		
		$this->view = new \ezview($view_name, $lang, $device, $default_lang, $default_device);
		
		return ;
	}
	
	private function get_device(){
		$ua=$this->input->user_agent;
		if(!is_object($ua)){
			return false;
		}
		$device = $ua->device_type;
		if($device){
			return $device;
		}
		return false;
	}
	
	private function get_lang(){
	
		$user_choice_lang=strtolower(\helper::get_value_from_array($this->input->http_get,'lang'));
		if($user_choice_lang){
			if(strstr(PFW_ALLOW_LANGS, $user_choice_lang)){
				$this->output->set_cookie("lang",$user_choice_lang,60*60*24*30);
				return $user_choice_lang;
			}
		}
	
		$user_choice_lang=strtolower($this->input->get_cookie("lang"));
		if($user_choice_lang){
			if(strstr(PFW_ALLOW_LANGS, $user_choice_lang)){
				return $user_choice_lang;
			}
		}
	
		$str=strtolower(\helper::get_value_from_array($this->input->http_server, 'HTTP_ACCEPT_LANGUAGE'));
		if(!$str){
			return false;
		}
		$str=strtolower($str);
		$arr=explode(",", $str);
		if(!$arr[0]){
			return false;
		}
		$brower_lang=$arr[0];

		if(strstr(PFW_ALLOW_LANGS, $brower_lang)){
			return $brower_lang;
		}
	
		if(strlen($brower_lang)>2){
			$phylum=substr($brower_lang, 0,2);
			if(strstr(PFW_ALLOW_LANGS,$phylum)){
				return $phylum;
			}
		}
		return false;
	}
	
	
	
}


