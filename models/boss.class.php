<?php 
namespace models;

if(!PFW_INIT){
	echo "break in";
	die;
}
class boss extends models {
	function __construct(){
		parent::__construct();
		return;
	}
	/**
	 * 得到设备的账号信息
	 *
	 * @param string $SN 设备编号
	 */
	public function get_gtan_data($SN){
		$row = $this->db->get_row("select * from t_boss_devices where SN='{$SN}'");
		if(!$row){
			return false;
		}
		return $row;
	}
	/**
	 * 得到地区的boss接口信息
	 *
	 * @param string $cityid  地区标示
	 */
	public function get_boss_data($cityid){
		$row = $this->db->get_row("select * from t_boss_list where cityid='{$cityid}'");
		if(!$row){
			return false;
		}
		return $row;
	}
	
	public function get_boss_args($userid,$cityid,$providerid,$privatekey){
		//生成boss接口的key参数
		$key=strtoupper ( md5("userid={$userid}&providerid={$providerid}&privatekey={$privatekey}" ) );
		$args= array(
			'userid'=>$userid,
			'cityid'=>$cityid,
			'providerid'=>$providerid,
			'key'=>$key	
		);
		return $args;
	}
	/**
	 * 修改密码
	 *
	 * @param string $userid 用户账号
	 * @param string $password 密码
	 */
	public function update_password($userid,$password){
		return $this->db->query("update t_boss_devices set password='{$password}' where userid='{$userid}'");
	}
	
	public function test_get_password(){
		ini_set('soap.wsdl_cache_enabled',0);
		ini_set('soap.wsdl_cache_ttl',0);
		//$wsdl = "http://219.239.86.199:8586/WifiService/WebService.asmx?WSDL";
		$wsdl=PFW_MODELS_PATH."/WebService.wsdl";
		$args= array(
				'userid'=>'010010000101',
				'cityid'=>'beijing',
				'providerid'=>'1000000010',
				'key'=>'68ABC26E8C5FE2F7A18C511522C93AF9'
		);
		try {
			$client= new \SoapClient($wsdl,array('location'=>'http://219.239.86.199:8586/WifiService/WebService.asmx'));
			$res = $client->__soapCall("GetUserLoginPassword",array($args));
		}catch(\SoapFault $exception){
			echo $exception->getMessage();
		}
		return $res->GetUserLoginPasswordResult;
	}
	public function test_update_password(){
		ini_set('soap.wsdl_cache_enabled',0);
		ini_set('soap.wsdl_cache_ttl',0);
		//$wsdl = "http://219.239.86.199:8586/WifiService/WebService.asmx?WSDL";
		$wsdl=PFW_MODELS_PATH."/WebService.wsdl";
		$args = array(
				'userid'=>'010010000101',
				'cityid'=>'beijing',
				'providerid'=>'1000000010',
				'key'=>'68ABC26E8C5FE2F7A18C511522C93AF9'
		);
		$args['password'] =\helper::make_rand_string(6,true);
		try {
			$client= new \SoapClient($wsdl);
			$res = $client->__soapCall("ResetUserPassword",array($args));
		}catch(\SoapFault $exception){
			echo $exception->getMessage();
		}
		return $res->ResetUserPasswordResult;
	}
}

