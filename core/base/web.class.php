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
			if(in_array($user_choice_lang,\ezview::get_support_langs($this->v))){
				$this->output->set_cookie("lang",$user_choice_lang,60*60*24*30);
				return $user_choice_lang;
			}
		}
	
		$user_choice_lang=$this->input->get_cookie("lang");
		if($user_choice_lang){
			if(strstr(PFW_ALLOW_LANGS, $user_choice_lang)){
				return $user_choice_lang;
			}
		}

		$str= strtolower(\helper::get_value_from_array($this->input->http_server, 'HTTP_ACCEPT_LANGUAGE'));
		if(!$str){
			return false;
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


