<?php

class web extends \http{

	protected $view = false;
	private $view_name = false;

	function __construct() {
		parent::__construct();
	}


	protected function set_view($view_name,$lang=false,$device=false){
		if(!is_object($this->view)){
			$this->load_lib("view");
		}
		$this->view_name= $view_name;

		if(!$lang){
			$lang = $this->get_lang();
		}

		if(!$device){
			$device=$this->get_device();
		}

		$this->view = new \kkview($view_name, $lang, $device);
		
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
	
		$user_choice_lang=$this->get("lang");
		if($user_choice_lang){
			if(in_array($user_choice_lang,\kkview::get_support_langs($this->view_name))){
				$this->set_cookie("lang",$user_choice_lang,60*60*24*30);
				return $user_choice_lang;
			}
		}
	
		$user_choice_lang=$this->cookie("lang");
		if($user_choice_lang){
			if(in_array($user_choice_lang,\kkview::get_support_langs($this->view_name))){
				return $user_choice_lang;
			}
		}

		$str_accept_lang = strtolower($this->server('HTTP_ACCEPT_LANGUAGE'));

		if(!$str_accept_lang){
			return "default";
		}

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


