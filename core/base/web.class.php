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

		$arr_support_device = \kkview::get_support_device($this->view_name);
		$ua=$this->user_agent();

		if(!is_object($ua)){
			$this->log->error("User_Agent fail");
			return "default";
		}

		$device = $ua->device_type;
		$key = array_search($device,$arr_support_device);
		if(!$key){
			return "default";
		}else{
			return $arr_support_device[$key];
		}

	}
	
	private function get_lang(){
	
		$user_choice_lang=$this->get("lang");
		$arr_support_lang=\kkview::get_support_langs($this->view_name);

		if($user_choice_lang){
			if(array_search($user_choice_lang,$arr_support_lang)){
				$this->set_cookie("lang",$user_choice_lang,60*60*24*30);
				return $user_choice_lang;
			}
		}


		$user_choice_lang=$this->cookie("lang");
		if($user_choice_lang){
			if(array_search($user_choice_lang,$arr_support_lang)){
				return $user_choice_lang;
			}
		}


		$str_accept_lang = strtolower($this->server('HTTP_ACCEPT_LANGUAGE'));

		if(!$str_accept_lang){
			return $arr_support_lang[0];
		}

		$arr_for_accept_lang_str = explode(",",$str_accept_lang);
		$arr_accpet_lang = false;

		foreach($arr_for_accept_lang_str as $lang_str){
			list($_lang) = explode(";",$lang_str);
			$arr_accpet_lang[]=$_lang;
		}


		$lang_key = false;
		foreach($arr_accpet_lang as $accept_lang){
			$lang_key = array_search($accept_lang,$arr_support_lang);
			if($lang_key){
				return $arr_support_lang[$lang_key];
			}
		}


		$arr_accpet_lang_short = false;
		foreach($arr_for_accept_lang_str as $lang_str){
			list($_lang) = explode(";",$lang_str);
			list($_lang) = explode("-",$_lang);
			$arr_accpet_lang_short[]=$_lang;
		}

		$arr_support_lang_short = false;
		foreach($arr_support_lang as $_support_lang){
			list($_lang) = explode("-",$_support_lang);
			$arr_support_lang_short[]=$_lang;
		}

		$lang_key = false;
		foreach($arr_accpet_lang_short as $accept_lang){
			$lang_key = array_search($accept_lang,$arr_support_lang_short);
			if($lang_key){
				return $arr_support_lang[$lang_key];
			}
		}

		return $arr_support_lang[0];

	}



	
	
	
}


