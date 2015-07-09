<?php
namespace controllers;


if(!PFW_INIT){
	echo "break in";
	die;
}

class wechat_api extends rest {
	
	private $ac = false;
	
	function __construct(){
		parent::__construct();
		$this->force_ssl(true);
		try {
			$ac = new \models\access_token($this->access_token);
			$this->ac = $ac;
		}catch (\Exception $e){
			$this->api_response_error_message(110008);
			return;
		}
		
		return;
	}
	
	public function wifi(){
	
		$action = \helper::get_value_from_array($this->input->path,3);
		switch ($action){
			case "add":
				$this->add_wifi();
				return;
			case "near":
				$this->near();
				return;
		}
		
		if(is_numeric($action)){
			$this->wifi_info($action);
			return;
		}
		
	}
	
	private function near(){
		if(!$this->ac->check_scope("basic")){
			$this->api_response_error_message(110008);
			return;
		}
		
		$lat=\helper::get_value_from_array($this->input->http_get, 'lat');
		$lon=\helper::get_value_from_array($this->input->http_get, 'lon');
		$count=\helper::get_value_from_array($this->input->http_get, 'count',10);
		$max_distance=\helper::get_value_from_array($this->input->http_get, 'max_distance',1000);
		
		$result = \models\wechat_wifi::get_near_wifis($lat, $lon, $count,$max_distance);
		
		if(!$result){
			$this->api_response_error_message(201002);
			return;
		}
		
		$data = array();
		
		foreach ($result as $wifi){
			$row['info'] = $wifi['wifi']->get_info();
			$row['distance']= (int) $wifi['distance'];
			
			$data[]=$row;
		}
		
		$result = array();
		$result['count'] = (int) $count;
		$result['max_distance'] = (int) $max_distance;
		$result['lat'] = (double) $lat;
		$result['lon'] = (double) $lon;
		$result['results'] = $data;
		
		$this->api_response($result);
		return;
	}
	
	private function wifi_info($wifi_id){
		if(!$this->ac->check_scope("basic")){
			$this->api_response_error_message(110008);
			return;
		}
		try {
			$wifi = new \models\wechat_wifi($wifi_id);
		} catch (\Exception $e){
			$this->api_response_error_message(201001,"wifi_id not found = $wifi_id");
		}
		
		$result = $wifi->get_info();
		if(is_array($result)){
			$this->api_response($result);
		}else{
			$this->api_response_error_message(201001,"wifi_id = $wifi_id");
		}
		return;
	}
	
	private function add_wifi(){
		if(!$this->ac->check_scope("basic")){
			$this->api_response_error_message(110008);
			return;
		}
		$lat=(double) \helper::get_value_from_array($this->input->http_post, "lat");
		$lon=(double) \helper::get_value_from_array($this->input->http_post, "lon");
		$loc_name=(string) \helper::get_value_from_array($this->input->http_post, "loc_name");
		$loc_address=(string) \helper::get_value_from_array($this->input->http_post, "loc_address");
		$ssid_type=(int) \helper::get_value_from_array($this->input->http_post, "ssid_type");
		$ssid_password=(string) \helper::get_value_from_array($this->input->http_post, "ssid_password");
		$ssid_name=(string) \helper::get_value_from_array($this->input->http_post, "ssid_name");
		
		if(!$lat || !$lon || !$loc_name){
			$this->api_response_error_message(200001,"miss lat or lon or loc_name");
			return;
		}
		if($ssid_type==2 && !$ssid_password){
			$this->api_response_error_message(200001,"miss ssid_password");
			return;
		}
		if(mb_strlen($loc_name,"utf-8")>100){
			$this->api_response_error_message(200001,"loc_name too long");
			return;
		}
		if($ssid_password){
			if(mb_strlen($ssid_password,"utf-8")>50){
				$this->api_response_error_message(200001,"ssid_password too long");
				return;
			}
		}
		if($ssid_name){
			if(mb_strlen($ssid_password,"utf-8")>50){
				$this->api_response_error_message(200001,"ssid_name too long");
				return;
			}
		}		
		if($loc_address){
			if(mb_strlen($loc_address,"utf-8")>50){
				$this->api_response_error_message(200001,"loc_address too long");
				return;
			}
		}
		$wechat_wifi=\models\wechat_wifi::add($lat, $lon, $loc_name, $loc_address, $ssid_name, $ssid_type, $ssid_password, $loc_address, $this->ac->user);
		if(is_object($wechat_wifi)){
			$result=array(
					'wifi_id' => $wechat_wifi->wifi_id,
			);
			$this->api_response($result);
			return;
		}
		$this->api_response_error_message(201999);
		return;
		
	}
}