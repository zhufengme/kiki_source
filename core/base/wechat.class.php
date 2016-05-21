<?php

class wechat extends \http {
	
	private $wechat_request = false;
	
	function __construct(){
		parent::__construct();
		$this->load_lib("wechat");

	}
	
    protected function get_oauth_access_token_by_code($str_code){
        $str_url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . \application::env("WECHAT_APPID"). "&secret=" .\application::env("WECHAT_APPSECRET") . "&code={$str_code}&grant_type=authorization_code";
        $result = \helper::http_request($str_url);
        if($result){
            return json_decode($result,true);
        }
        return false;
    }

	protected function get_userinfo_by_oauth_access_token($str_oauth_access_token,$str_wechat_user_id){
		$str_url = "https://api.weixin.qq.com/sns/userinfo?access_token={$str_oauth_access_token}&openid={$str_wechat_user_id}&lang=zh_CN ";
		$result = \helper::http_request($str_url);
		if($result){
			return json_decode($result,true);
		}
		return false;
	}
	
	protected function get_wechat_access_token(){

        $cache_file = KKF_CACHE_PATH.DIRECTORY_SEPARATOR."WECHAT_ACCESS_TOKEN";
        if(file_exists($cache_file)){
            $str_json = file_get_contents($cache_file);
            $result = json_decode($str_json,true);
            if($result['expire_time']>$this->timestamp){
                return $result['access_token'];
            }
        }

		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . application::env("WECHAT_APPID") . "&secret=" . application::env("WECHAT_APPSECRET");
		$result = \helper::http_request($url);
		$result = json_decode($result,true);
		if(key_exists("access_token", $result)){
            $arr_cache['expire_time']= $this->timestamp+$result['expires_in'];
            $arr_cache['access_token']= $result['access_token'];
            $str_cache = json_encode($arr_cache);
            file_put_contents($cache_file,$str_cache);
            return $result['access_token'];
		}
		
		return false;
	}
	
	protected function get_wechat_request(){
		return $this->wechat_request;
	}
	
	protected function wechat_response($wechat_response){
		$xml = $wechat_response->get_xml();
		$this->log->info("Wechat Response: \n$xml");
		$this->output->add_http_header("Content-type","application/xml");
		$this->output->out($xml);		
		$this->sync_menu();
		return;
	} 
	
	protected function wechat_receiver(){

		if($this->get('echostr')){
			$this->output->out($this->get('echostr'));
			return;
		}
		
		if($this->post_raw()){
			$this->log->info("Wechat Request: \n" . $this->post_raw());
			$this->wechat_request = new \wechat_request($this->post_raw());
			return;
		}
		
		$this->output->out("What are you doing?");
		return;
		
	}
	
	protected function sync_menu(){

        $menu_struct = null;
        if(!file_exists(KKF_CONFIG_PATH.DIRECTORY_SEPARATOR."wechat_menu.json")){
            return false;
        }else{
            $menu_struct=file_get_contents(KKF_CONFIG_PATH.DIRECTORY_SEPARATOR."wechat_menu.json");
        }

        $wechat_access_token = $this->get_wechat_access_token();
        $this->log->info("wechat access_token : $wechat_access_token");
        $wm=new \wechat_menu($wechat_access_token);
        $result = $wm->create($menu_struct);
        $this->log->info("wechat menu struct update : $result");
        return $result;

	}
	
	private function check_signature($echo=false){
		$signature = \helper::get_value_from_array($this->input->http_get, "signature");
		$timestamp = \helper::get_value_from_array($this->input->http_get, "timestamp");
		$nonce = \helper::get_value_from_array($this->input->http_get, "nonce");
		$echostr = \helper::get_value_from_array($this->input->http_get, "echostr");
	
		$result = \wechat_request::valid($signature, $timestamp, $nonce, WECHAT_TOKEN);
		if($result){
			if($echo){
				$this->output->out($echostr);
			}
			return true;
		}
		return false;
	}
}