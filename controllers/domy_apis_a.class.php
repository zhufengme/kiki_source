<?php
namespace controllers;


use models;
if(!PFW_INIT){
	echo "break in";
	die;
}

class domy_apis_a extends rest {
	
	private $arguments = false;
	private $ignore_verify = true;
	
	
	function __construct(){
		parent::__construct();
		$this->force_ssl(true);
		$this->load_lib("cache");
	}
	
	public function main(){
		$api_names=array('CHECK','PING','MOBV','GTAN','SWDL','GTCM','BINDMOBILE');
		if(\helper::get_value_from_array($this->input->http_get, '_c')=='CHECK'){
			$this->api_check();

			return;
		}
		

		$post_data = $this->input->http_post_raw;
		if($post_data){
			$this->arguments = json_decode($post_data,true);
			
		}else{
			if($this->input->http_get){
                 $this->arguments=$this->input->http_get;
             }
		}
		$this->log->info("rec: " . json_encode($this->arguments));
		
		if(!PFW_DEBUG_MODE){
			$this->verify();
		}else{
			$this->log->warn("domy-api verify has disabled!");
		}
		
		$_c = $this->arguments["_c"];
		
		switch ($_c){
			case "CHECK":
				$this->api_check();
				break;
			case "PING":
				$this->api_ping();
				break;
			case "MOBV":
				$this->api_mobv();
				break;
			case "GTAN":
				$this->api_gtan();
				break;
			case "GTCM":
				$this->api_gtcm();
				break;
			case "SWDL":
				$this->api_swdl();
				break;
			case "BINDMOBILE":
				$this->api_bindMobile();
				break;
			default:
				$this->api_response_error_message("10006",$this->arguments["_c"]);
				break;
		}
		return;
		
	}
	/**
	 * 绑定手机 1成功 ，2失败
	 */
	private function api_bindMobile(){
		$oDevice = \models\device::get_device_by_SN($this->arguments['SN']);
		if( $oDevice->bind_phone($this->arguments['SN'],$this->arguments['MOBILE']) ){
			$this->api_response('{"SUCC":1}');
		}else {
			$this->api_response('{"SUCC":2}');
		}
		return;
	}
	private function api_gtcm(){
		$data_cmd = false;
		$data_config = false;
		$key = "domy_CMD_LIST_" . $this->arguments['SN'];
		
		if(!$this->cache->hash_size($key)){
			$this->api_response_error_message(70001,"SN:" . $this->arguments['SN']);
			return;
		}
		
		$results = $this->cache->hash_get($key);
		
		foreach ($results as $row){
			if($row['cmd_type']=="CMD"){
				$data_cmd[$row['CID']]['CID'] = $row['CID'];
				$data_cmd[$row['CID']]['BODY'] = $row['body'];
			}
			if($row['cmd_type']=="CONFIG"){
				$data_config[$row['CID']]['CID'] = $row['CID'];
				$data_config[$row['CID']]['BODY'] = $row['body'];
			}
		}
		
		if($data_cmd){
			$result['CMD'] = $data_cmd;
		}
		if($data_config){
			$result['CONFIG'] = $data_config;
		}
		
		$this->api_response($result);
		return;
	}
	private function api_gtan(){
		$boss = new \models\boss();
		//boss接口测试用
		if(isset($this->arguments['test']) && $this->arguments['test']==1){
			$res = $boss->test_get_password();		
			$res1 = $boss->test_update_password();
			var_dump(\simplexml_load_string($res));
			var_dump(\simplexml_load_string($res1));
			return;
		}
		//非测试
		$gtan_data = $boss->get_gtan_data($this->arguments['SN']);
		if(!$gtan_data){ 
			$this->api_response_error_message('10001','SN:'.$this->arguments['SN'].' in ');
 			return;
		}
		$boss_data=$boss->get_boss_data( $gtan_data->cityid);//boss 接口信息 
		
		//初始化soap
		ini_set('soap.wsdl_cache_enabled',0);
		ini_set('soap.wsdl_cache_ttl',0);
		$soapServerUrl=trim( $boss_data->url );	//$wsdl=PFW_MODELS_PATH."/WebService.wsdl";
		$soap_options=array('location'=>$soapServerUrl);
		
		try {
			$client= new \SoapClient($soapServerUrl."?WSDL",$soap_options);
			$args=array();
			$args = $boss->get_boss_args($gtan_data->userid,$boss_data->cityid,$boss_data->providerid,$boss_data->privatekey);
			
			if($boss_data->pwd_type==='0'){
				//密码是明文 直接获取
				$boss_obj = $client->__soapCall("GetUserLoginPassword",array($args));
				$boss_str=$boss_obj->GetUserLoginPasswordResult;
			}else {
				//密码是非明文的
				$args['password']=\helper::make_rand_string(15,true);
				$boss_obj = $client->__soapCall("ResetUserPassword",array($args));
				$boss_str=$boss_obj->ResetUserPasswordResult;
			}
			$obj_res = simplexml_load_string($boss_str);
			//Code -1 失败
			if(strval( $obj_res->Code )==='-1'){
				//$this->api_response("{\"error_code\":\"5002\",\"error_msg\":\"".strval( $obj_res->Description)."\"}");
				$this->api_response_error_message("50002",strval($obj_res->Description) );
				return ;
			}
			//Code 0 成功
			if(strval( $obj_res->Code )==='0'){
				$arr_pwd['METHOD']="pppoe";
				$arr_pwd['PPPOE_USERNAME']=$gtan_data->userid;
				$arr_pwd['PPPOE_PASSWORD']=$obj_res->Password?strval($obj_res->Password):$args['password'];
				if(isset($args['password'])){
					$boss->update_password($gtan_data->userid,$args['password']);
				}
				$this->api_response($arr_pwd);
			}
		}catch(\SoapFault $exception){
			$this->api_response_error_message("50002", $exception->getMessage());
		}
	}
	private function api_mobv(){
		
		if(!\helper::is_mobile($this->arguments['MOBILE'])){
			$this->api_response_error_message("40001",$this->arguments['MOBILE']);
			return;
		}
		
		$key = "VCODE_CALL_COUNT_PH_" . $this->arguments['SN'] . "_" . $this->arguments['MOBILE'];
		
		$VCODE_CALL_COUNT_PH = $this->cache->get($key);
		if(!$VCODE_CALL_COUNT_PH){
			$VCODE_CALL_COUNT_PH=1;
		}else{
			$VCODE_CALL_COUNT_PH++;
		}
		$this->cache->setex($key,3600,$VCODE_CALL_COUNT_PH);
		
		$key = "VCODE_CALL_COUNT_PD_" . $this->arguments['SN'] . "_" . $this->arguments['MOBILE'];
		$VCODE_CALL_COUNT_PD = $this->cache->get($key);
		if(!$VCODE_CALL_COUNT_PD){
			$VCODE_CALL_COUNT_PD=1;
		}else{
			$VCODE_CALL_COUNT_PD++;
		}
		$this->cache->setex($key,86400,$VCODE_CALL_COUNT_PD);
		
		if($VCODE_CALL_COUNT_PD>10 && $VCODE_CALL_COUNT_PH>5){
			$this->api_response_error_message("40002");
			return;
		}
		
		
		$device = \models\device::get_device_by_SN($this->arguments['SN']);
		//SN不存在 $device false
		if(!$device){
		 $this->api_response_error_message('10001','SN:'.$this->arguments['SN']);
		 }
		$vcode = $device->get_mobile_vcode($this->arguments['MOBILE']);

		$sms_content = "欢迎使用大麦无线，您的手机验证码是：{$vcode}";
		
		$service = new \models\service();
		$service->create_sms($this->arguments['MOBILE'], $sms_content);
		
		$result = array(
						'VCODE' => "$vcode",
						'CALL_COUNT_PH' => $VCODE_CALL_COUNT_PH,
						'CALL_COUNT_PD' => $VCODE_CALL_COUNT_PD,
					);
		
		$this->api_response($result);
		return;
	}
	
	private function api_ping(){ 		
		$device = \models\device::get_device_by_SN($this->arguments['SN']);
		if(!$device){
			$this->api_response_error_message("10001",$this->arguments['SN']);
			return;
		}
		
		if($device->usable==-1){
			$this->api_response_error_message("10002",$this->arguments['SN']);
			return;
		}
		$this->cache->hash_set("domy_PING_STATUS_{$device->SN}","LAST_PING",$this->timestamp);
		$this->cache->hash_set("domy_PING_STATUS_{$device->SN}","SID",$this->arguments['SID']);
		
		$this->cache->queue_l_push("domy_PING_QUEUE",$this->arguments);
		
		//感知数据搜集
		if (!empty($this->arguments['DETAIL']) && is_array($this->arguments['DETAIL'])) {
			$perception = new \models\perception($this->arguments['SN']);
			$perception->ping_perception($this->arguments['DETAIL']);
		}
		$QUEUE_SIZE = $this->cache->queue_size("domy_PING_QUEUE");
		$ret['QUEUE_SIZE']=$QUEUE_SIZE;
		
		//check version code=2
		if( isset($this->arguments["VERSION"]) && !empty(  $this->arguments["VERSION"] ) ){
			    $mVersion = new \models\version($this->arguments['SN']);
				$token_count = $mVersion->get_swdl_count();
				if($token_count < SWDL_COUNT_MAX){
					//check new version
					if($this->arguments["VERSION"]=='0.9.6' && $this->arguments['HD_TYPE']=='DM202'){
						$data_version = $mVersion->get_version_info($this->arguments['HD_TYPE'],'0.9.7');
					}else {
						$data_version = $mVersion->check_newest_version_v3($this->arguments['HD_TYPE'],$this->arguments["VERSION"]);
					}
					$file_path = isset($data_version['file_path'])? PFW_VERSION_UPLOAD_PATH.'/'.$data_version['file_path'] : '';
					if( $data_version && file_exists($file_path) && is_readable($file_path) ){
						$update_token=uniqid().\helper::make_rand_string(5,true); 
						if($this->arguments['HD_TYPE']!='PW-101'){
							$res_version_session = $mVersion->insert_update_token($update_token,$data_version['version'],$data_version['file_size'],$data_version['file_path']);
						}else {
							$res_version_session = $mVersion->insert_update_token_pw($update_token,$data_version['version'],$data_version['file_size'],$data_version['file_path']);
						}
						if($res_version_session){
							$ret['UPDATE_TOKEN']=$update_token;
							$ret['UPDATE_VERSION']=$data_version['version'];
							$ret['UPDATE_SIZE']=$data_version['file_size'];
							$ret['UPDATE_SUM']=$data_version['md5sum'];
							$ret['UPDATE_VINFO']=$data_version['vinfo'];
							$ret['IS_FORCE']=$data_version['is_force'];
							$ret['IS_FORMAL']=$data_version['is_formal'];
							$ret['IS_NEED']= $data_version['is_need'];
							$ret['CODE']=2;
							$this->api_response($ret);	
							return;
						}
					}
				}
		}
		
		$ret['CODE']=1;
		if($this->check_cmd($this->arguments['SN'])){
			$ret['CODE']=3;//check cmd  code=3
		}
		$this->api_response($ret);
		return;
	}
	
	private function api_swdl(){
		$key ='swdl_'.$this->arguments['UPDATE_TOKEN'];
		$flag = false;
		if($this->cache->exists($key)){
			$res = $this->cache->get($key);//PW-101
			$flag= true;
		}else {
			$mVersion = new \models\version( trim($this->arguments['SN']) );
			$res = $mVersion->select_update_token();//get token
			if(!$res || $res['token'] !=trim($this->arguments['UPDATE_TOKEN'])){
				$this->api_response_error_message(60001);//token error
				return;
			}
		}
		try{
			header('content-type:text/html;charset=UTF-8');
			$path =PFW_VERSION_UPLOAD_PATH.'/'.$res['file_path'];
			header('Content-type:application/octet-stream');
			header('Content-Disposition:attachment;filename="'.$res['file_path'].'"');
			header('Content-Length:'.$res['file_size']);
			readfile($path);
			if($flag){
				$this->cache->del($key);
			}else {
				$mVersion->delete_update_token();//delete token from redis
			}
			exit;
		}catch(\Exception $e){
			echo $e->getMessage();
		}
		return;
	}
	
	private function check_cmd($SN){
		$key = "domy_CMD_LIST_".$SN;
		$size = $this->cache->hash_size($key);
		if($size){
			return true;
		}
		return false;
	}
	
	private function api_check(){
		
		$service = new models\service();
		$db_status = $service->check_db_status();
		//$file_status= $service->check_file_access();
		$cache_status = $service->check_cache_status();
		$ping_queue = $this->cache->queue_size("domy_PING_QUEUE");
		$ret = array(
						"DB" => (bool) $db_status,
				 		"CACHE" => (bool) $cache_status,
						//"FILE" => (bool) $file_status,
						"TS" => (int) $this->timestamp,
						"IP" => (string) $this->input->http_server['REMOTE_ADDR'],
						"PING_QUEUE" => (int) $ping_queue
					);
		
		$this->api_response($ret);
	}
	
	private function verify(){
		
		if(!$this->arguments){
			$this->log->warn("api verify error : empty request");
			$this->api_response_error_message("10005","empty request");			
			return;
		}
		
		if(!key_exists("_t",$this->arguments)){
			$this->log->warn("api verify error : miss _t");
			$this->api_response_error_message("10005","miss _t");		
			return;
		}
		if(!key_exists("_v",$this->arguments)){
			$this->log->warn("api verify error : miss _v");
			$this->api_response_error_message("10005","miss _v");
			return false;
		}
		if(!key_exists("_c",$this->arguments)){
			$this->log->warn("api verify error : miss _c");
			$this->api_response_error_message("10005","miss _c");			
			return false;
		}
	
		
		if(abs($this->arguments['_t']-$this->timestamp)>900){
			$this->log->warn("api verify error : timeout server ts : " . $this->timestamp);
			$this->api_response_error_message("10004","server ts : " . $this->timestamp);			
			return false;
		}
		
		ksort($this->arguments);
		$row = $this->arguments;
		unset($row["_v"]);
		
		$str = false;
		
		foreach ($row as $key => $value){
			$str .= $value;
		}
		
		$expect_v= md5($str);
		
		if($expect_v!=$this->arguments['_v']){
			$this->log->warn("api verify error : _v error! expect _v is :" . $expect_v);
			$this->api_response_error_message("10003","expect _v is : $expect_v");			
			return false;
		}
		
		return true;		
	}
	
}
