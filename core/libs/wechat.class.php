<?php
if(!PFW_INIT){
	echo "break in";
	die;
}

define("WECHAT_MSG_TYPE_IMAGE", "image");
define("WECHAT_MSG_TYPE_TEXT", "text");
define("WECHAT_MSG_TYPE_LOCATION", "location");
define("WECHAT_MSG_TYPE_LINK", "link");
define("WECHAT_MSG_TYPE_EVENT", "event");
define("WECHAT_MSG_TYPE_MUSIC", "music");
define("WECHAT_MSG_TYPE_NEWS", "news");

define("WECHAT_EVENT_SUBSCRIBE", "subscribe");
define("WECHAT_EVENT_UNSUBSCRIBE", "unsubscribe");
define("WECHAT_EVENT_CLICK", "click");


class wechat_request{
	
	private $request_string = false;
	public $to_username = false;
	public $from_username = false;
	public $msg_type = false;
	public $create_time = false;
	public $msg_id = false;
	public $content = false;
	public $pic_url = false;
	public $lat = false;
	public $lon = false;
	public $scale = false;
	public $label = false;
	public $title = false;
	public $description = false;
	public $url = false;
	public $event_type = false;
	public $event_key = false;
	
	function __construct($request_string){
		if(!function_exists("simplexml_load_string")){
			throw new Exception("simplexml_load_string model not found");
			die;
		}
		$this->request_string=$request_string;
		$this->request_parse();
		return;
	}
	
	private function request_parse(){
		$xml = simplexml_load_string($this->request_string);
		$this->to_username = $xml->ToUserName;
		$this->from_username = $xml -> FromUserName;
		$this->msg_type = $xml -> MsgType;
		$this->create_time = $xml -> CreateTime;
		$this->msg_id = $xml -> MsgId;
		
		switch ($this->msg_type){
			case WECHAT_MSG_TYPE_TEXT:
				$this->content = $xml -> Content;
				break;
			case WECHAT_MSG_TYPE_IMAGE:
				$this->pic_url = $xml -> PicUrl;
				break;
			case WECHAT_MSG_TYPE_LOCATION:
				$this->lat = $xml -> Location_X;
				$this->lon = $xml -> Location_Y;
				$this->scale = $xml -> Scale;
				$this->label = $xml -> Label;
				break;
			case WECHAT_MSG_TYPE_LINK:
				$this->url = $xml -> Url;
				$this->title = $xml -> Title;
				$this->description = $xml -> Description;
				break;
			case WECHAT_MSG_TYPE_EVENT:
				$this->event_type = $xml -> Event;
				$this->event_key = $xml -> EventKey;
				break;
		}
		
		return;
	}
	
	public static function valid($signature,$timestamp,$nonce,$WECHAT_TOKEN){
		$tmpArr = array($WECHAT_TOKEN, $timestamp, $nonce);
		sort($tmpArr);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		if($signature == $tmpStr){
			return true;
		}
		return false;
	}
}

class wechat_response{
	
	private $from_username = false;
	private $to_username = false;
	private $timestamp = false;
	private $msg_type = false;
	private $xml = false;
	
	function __construct($from_username,$to_username){
		$this->from_username = $from_username;
		$this->to_username = $to_username;
		$this->timestamp = time();
		
		$this->xml = "<xml>\n";
		$this->xml .= "<ToUserName><![CDATA[{$this->to_username}]]></ToUserName> \n";
		$this->xml .= "<FromUserName><![CDATA[{$this->from_username}]]></FromUserName> \n";
		$this->xml .= "<CreateTime>{$this->timestamp}</CreateTime> \n";

		return;
	}
	
	public function set_content($content){
		$this->xml .= "<MsgType><![CDATA[" . WECHAT_MSG_TYPE_TEXT . "]]></MsgType> \n";
		$this->xml .= "<Content><![CDATA[{$content}]]></Content> \n";
		return;
	}
	
	public function set_music($title,$description,$music_url,$hq_music_url){
		$this->xml .= "<MsgType><![CDATA[" . WECHAT_MSG_TYPE_MUSIC . "]]></MsgType> \n";
		$this->xml .= "<Music> \n";
		$this->xml .= "<Title><![CDATA[{$title}]]></Title> \n";
		$this->xml .= "<Description><![CDATA[{$description}]]></Description> \n";
		$this->xml .= "<MusicUrl><![CDATA[{$music_url}]]></MusicUrl> \n";
		$this->xml .= "<HQMusicUrl><![CDATA[{$hq_music_url}]]></HQMusicUrl> \n";
		$this->xml .= "</Music> \n";
		return;
	}
	
	public function set_news($articles){
		$this->xml .= "<MsgType><![CDATA[" . WECHAT_MSG_TYPE_NEWS . "]]></MsgType> \n";
		$this->xml .= $articles -> get_xml_str();
		return;
	}
	
	public function get_xml(){
		if(!$this->xml){
			return false;
		}
		$this->xml .= "</xml> \n";
		return $this->xml ;
	}
	
}

class wechat_articles{
	
	private $items = array();
	
	public function get_xml_str(){
		$items=\helper::array_sort($this->items, "sort" , "asc");
		$article_count = count($items);
		
		$xml_str = "<ArticleCount>{$article_count}</ArticleCount>\n";
		$xml_str .= "<Articles>\n";
		
		foreach ($items as $item){
			$xml_str .= "<item> \n";
			$xml_str .= "<Title><![CDATA[{$item['title']}]]></Title> \n";
			$xml_str .= "<Description><![CDATA[{$item['description']}]]></Description> \n";
			$xml_str .= "<PicUrl><![CDATA[{$item['pic_url']}]]></PicUrl> \n";
			$xml_str .= "<Url><![CDATA[{$item['url']}]]></Url> \n";
			$xml_str .= "</item> \n";
		}
		
		$xml_str .= "</Articles> \n";
		return $xml_str;
	}
	
	public function add_article($title,$description,$pic_url,$url,$sort=10){
		$item = array();
		$item['title'] = $title;
		$item['description'] = $description;
		$item['pic_url'] = $pic_url;
		$item['url'] = $url;
		$item['sort'] = $sort;
		$item_id = $this->get_item_id($item);
		$this->items[$item_id] = $item;
		return;
	}
	
	public function clear(){
		$this->items = array();
		return;
	}
	
	private function get_item_id($item){
		if(!is_array($item)){
			return false;
		}
		$str = implode("|", $item);
		return md5($str);
	}
	
}

class wechat_menu{
	
	
	private $wechat_access_token = false;
	
	function __construct($wechat_access_token){
		$this->wechat_access_token = $wechat_access_token;
		return;
	}
	
	public function create($menu_struct){
		$json = $menu_struct;
		if($json){
			$result = helper::http_request("https://api.weixin.qq.com/cgi-bin/menu/create?access_token=". $this->wechat_access_token,"POST",$json);
			return $result;
		}
		return false;
	}
	
	public function get(){
		$result = helper::http_request("https://api.weixin.qq.com/cgi-bin/menu/get?access_token=". $this->wechat_access_token,"GET");
		return $result;
	}
	
	public function delete(){
		$result = helper::http_request("https://api.weixin.qq.com/cgi-bin/menu/delete?access_token=". $this->wechat_access_token,"GET");
		return $result;
	}
	
}