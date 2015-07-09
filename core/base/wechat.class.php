<?php
namespace controllers;

if(!PFW_INIT){
	echo "break in";
	die;
}

class wechat extends \base {
	
	private $wechat_request = false;
	
	function __construct(){
		parent::__construct();
		$this->load_lib("wechat");
		$this->load_lib("cache");
		$this->wechat_init();
	}
	
	protected function get_wechat_access_token(){
		
		if($this->cache->exists("wechat_access_token")){
			return $this->cache->get("wechat_access_token");
		}
		
		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . WECHAT_APPID . "&secret=" . WECHAT_APPSECRET;
		$result = \helper::http_request($url);
		$result = json_decode($result,true);
		if(key_exists("access_token", $result)){
			$this->cache->setex("wechat_access_token",$result['expires_in'],$result['access_token']);
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
	
	private function wechat_init(){
		$this->log->info("Wechat valid request: " . serialize($this->input->http_get));
		if($this->input->http_post_raw){
			if(!PFW_DEBUG_MODE){
				$valid = $this->check_signature(false);
				if(!$valid){
					$this->output->out("Hello Hacker!");
					die;
				}
				
			}
		}else{
			$this->log->info("just is a valid");
			$this->check_signature(true);
			return;
		}
		
		
		if($this->input->http_post_raw){
			$this->log->info("Wechat Request: \n" . $this->input->http_post_raw);
			$this->wechat_request = new \wechat_request($this->input->http_post_raw);
			return;
		}
		
		$this->output->out("What are you doing?");
		return;
		
	}
	
	private function sync_menu(){
		if(!WECHAT_MENU_STRUCT){
			return false;
		}
		
		$sum = $this->cache->get("wechat_menu_sum");
		$currnet_sum = md5(WECHAT_MENU_STRUCT);
		if($sum!=$currnet_sum){
			$this->log->info("wechat menu updated");
			$wechat_access_token = $this->get_wechat_access_token();
			$this->log->info("wechat access_token : $wechat_access_token");
			$wm=new \wechat_menu($wechat_access_token);
			$result = $wm->create(WECHAT_MENU_STRUCT);
			$this->log->info("wechat menu struct update : $result");
			$this->cache->setex("wechat_menu_sum",86400,$currnet_sum);
		} 
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