<?php
namespace controllers;

if(!PFW_INIT){
	echo "break in";
	die;
}

class wechat_mp extends wechat{
	
	function __construct(){
		parent::__construct();
		$this->force_ssl(false);
		return;
	}
	
	public function index(){
		$user = \models\wechat_user::create($this->wechat_request->to_username, $this->wechat_request->from_username);
		$session = false;
				
		if($this->wechat_request->msg_type == WECHAT_MSG_TYPE_LOCATION){
			$lat = (double) $this->wechat_request->lat;
			$lon = (double) $this->wechat_request->lon;
			$session = \models\wechat_session::create($user, $lat,  $lon);
			$this->log->info("create wechat_session lat = $lat , lon = $lon");
		}else{
			$session = $user->session;
		}
		
		if(!$session){
			$this->rsp_lbs_request($this->wechat_request);
			return;
		}
		
		$this->rsp_near_wifis($session,$this->wechat_request);
		
		return;
		$this->dev_response($this->wechat_request);

	}
	
	private function rsp_near_wifis($session,$wechat_request){
		$wechat_response= new \wechat_response($wechat_request->to_username, $wechat_request->from_username);
		$articles = new \wechat_articles();
		
		$results = \models\wechat_wifi::get_near_wifis($session->lat, $session->lon, 8,2000);
		
		if(!$results){
			$articles->add_article("糟糕，周围还没有WiFi被收录", 
									"", 
									"http://static.joome.net/wechat/b_not_found.jpg", 
									"https://".WECHAT_WEB_URL."/wechat_web/add_wifi/?lat={$session->lat}&lon={$session->lon}&wechat_account={$wechat_request->to_username}&wechat_openid={$wechat_request->from_username}",0);
			
		}else{
			$articles->add_article("在附近找到". count($results) ."个免费WiFi",
									"",
									"http://static.joome.net/wechat/b_found.jpg",
									"https://".WECHAT_WEB_URL."/wechat_web/near_wifi/?lat={$session->lat}&lon={$session->lon}&wechat_account={$wechat_request->to_username}&wechat_openid={$wechat_request->from_username}",0);	
			$sort = 10;
			
			foreach ($results as $row){
				$wifi = $row['wifi'];
				$distance = $row['distance'];
				
				if($wifi->ssid_type == WECHAT_SSID_TYPE_JOOME){
					$pic_url = "http://static.joome.net/wechat/s_joome.png";
				}
				
				$info = $wifi -> loc_name;
				if( strlen($wifi -> loc_address)>5 ){
					$info .= "，" . $wifi->loc_address;
				}
				$info .= "，距离您" .$distance."米";
				
				$articles->add_article($info,
						"",
						$pic_url,
						"https://".WECHAT_WEB_URL."/wechat_web/wifi_info/?wifi_id={$wifi->wifi_id}&wechat_account={$wechat_request->to_username}&wechat_openid={$wechat_request->from_username}",$sort);

				$sort++;
			}
		}
		
		
		
		$articles->add_article("分享附近的WiFi信息给大家，即可得手机充值，点击进入",
				"",
				"http://static.joome.net/wechat/s_joome.png",
				"https://".WECHAT_WEB_URL."/wechat_web/add_wifi/?lat={$session->lat}&lon={$session->lon}&wechat_account={$wechat_request->to_username}&wechat_openid={$wechat_request->from_username}",100);
		
		$wechat_response -> set_news($articles);
		$this->wechat_response($wechat_response);
		return;
		
	}
	
	private function rsp_lbs_request($wechat_request){
		$wechat_response= new \wechat_response($wechat_request->to_username, $wechat_request->from_username);
		$wechat_response->set_content("为了帮您找到最近的WiFi，我们需要知道您的位置，请点击消息发送区的 “+” 号，把您的位置发给我们先");
		$this->wechat_response($wechat_response);
		return;		
	}
	
	private function dev_response($wechat_request){
		$wechat_response= new \wechat_response($wechat_request->to_username, $wechat_request->from_username);
		$wechat_response->set_content("您好，欢迎使用找WiFi服务，您发来的信息已经被保存。更多功能正在开发中，敬请期待");
		$this->wechat_response($wechat_response);
		return;
	}
	
	
	

}