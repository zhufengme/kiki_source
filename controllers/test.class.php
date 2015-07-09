<?php
namespace controllers;


if(!PFW_INIT){
	echo "break in";
	die;
}

class test extends wechat {
	public function test(){
		echo mb_strlen("我们123","utf-8");
		
	}
	
	public function ac(){
		$client = new \models\oauth2_client("client_id");
		$user = new \models\user(428531);
		$result = \models\access_token::valid("v400xXNdLTLaehLmVzrTxkYZRtzkTxbSkxPhUbMfvYGZGSMcSDwPpUpFMnsfkUyW", "basic",$user);
		var_dump($result);
	}
	
	public function json(){
		$arr=array(
				'wifi_id' => 1,
		);
		
		echo json_encode($arr);
	}
	
	
	
	public function wifi(){
		$result=\models\wechat_wifi::get_near_wifis(50.092449, 117.131599,5,1000);
		
		foreach ($result as $row){
			echo $row['wifi']->loc_name;
			echo "\n";
		}
	}
	
	public function btn(){
		$this->load_lib("wechat");
		$wechat_access_token = $this->get_wechat_access_token();
		$wm = new \wechat_menu($wechat_access_token);
		$result =$wm -> create(WECHAT_MENU_STRUCT);
		echo $result;
		
		
		//echo $result;
		
	}
	
	public function cu(){
		$user = \models\wechat_user::create("account", "openid");
		echo $user->UID;
		
		return;
	}
	
	public function ua(){
		$ua = new \user_agent("Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 6.2; ARM; Trident/6.0; Touch; WebView/1.0)");
		echo "User Agent : " . $ua->user_agent ."\n";
		echo "device_type : " . $ua->device_type;
		return;

	}
	
	public function post_raw(){
		
		echo $this->input->http_post_raw;
		
	}
	
	public function info(){
		var_dump( \input::get_web_path());
		var_dump($this->input->http_get);
		echo  \helper::real_ip();
		phpinfo();
		return;
	}
	
	public function cache(){
		$arr=array(
				'key1' => "1",
				"key2" => "2",
		);
		$result=array(
				'bbool' => true,
				'nnumber' => 123,
				'sstring' => "aaa",
				'aarray' => $arr,
				
		);
		//$this->cache->setex("cde",5,"2345");
		//var_dump( $this->cache->get("cde"));
	}
	
	public function argv(){
		print_r($this->input->http_get);
	}
	
	public function wechat(){
		$this->load_lib("wechat");
		$wechat = new \wechat_response("findwifi", "zhufengme");
		
		$items = new \wechat_articles();
		$items->add_article("title1", "description1", "pic_url1", "url1");
		$items->add_article("title2", "description2", "pic_url2", "url2");
		$items->add_article("title3", "description3", "pic_url3", "url3");
		
		$wechat->set_news($items);
		
		echo $wechat->get_xml();
	}
}