<?php 
namespace models;

if(!PFW_INIT){
	echo "break in";
	die;
}

class device extends models {

	protected $SN = false;
	private $cache_key = false;
	
	function __construct($SN){
		parent::__construct();
		$this->SN= $SN;
		$this->cache_key = __CLASS__.":{$SN}";
	
		if(!$this->cache->exists($this->cache_key)){
			$row=$this->db->get_row("select SN from t_devices_list where SN='{$this->SN}'");
			$this->create_status_row();
			if(!$row){
				throw new \Exception("device SN not found");
				return;
			}
		}
	
		$this->cache->hash_set($this->cache_key,"_",$this->timestamp,3600);
		return;
	}
	
	function __destruct(){
		parent::__destruct();
	}
	
	function dispose(){
		self::__destruct();
	}
	
	
	/**
	 * 创建一条状态记录
	 */
	private function create_status_row(){
		$row=$this->db->get_row("select SN from t_devices_status where SN='{$this->SN}'");
		if(!$row){
			$this->db->query("insert into t_devices_status (SN) values ('{$this->SN}')");
		}
		return;
	}
	
	public function set_wan_ip($value){
				
		$value = (string) $value;
		$value = substr($value, 0,40);
		$store_value = $this->cache->hash_get($this->cache_key,"wan_ip");
		if($store_value != $value){
			$this->db->query("update t_devices_status set wan_ip='$value' where SN='{$this->SN}'");
			$this->cache->hash_set($this->cache_key,"wan_ip",$value);
		}
		return $value;
	}
	
	public function set_SID($value){
		$value = (int) $value;
		$store_value = $this->cache->hash_get($this->cache_key,"SID");
		if($store_value != $value){
			$this->db->query("update t_devices_status set SID='$value' where SN='{$this->SN}'");
			$this->cache->hash_set($this->cache_key,"SID",$value);
		}
		return $value;
	}
	
	public function set_mem_total($value){
		$value = (int) $value;
		$store_value = $this->cache->hash_get($this->cache_key,"mem_total");
		if($store_value != $value){
			$this->db->query("update t_devices_status set mem_total='$value' where SN='{$this->SN}'");
			$this->cache->hash_set($this->cache_key,"mem_total",$value);
		}
		return $value;
	}
	
	public function set_mem_free($value){
		$value = (int) $value;
		$store_value = $this->cache->hash_get($this->cache_key,"mem_free");
		if($store_value != $value){
			$this->db->query("update t_devices_status set mem_free='$value' where SN='{$this->SN}'");
			$this->cache->hash_set($this->cache_key,"mem_free",$value);
		}
		return $value;
	}
	
	public function set_cpu_load($value){
		$value = (double) $value;
		$store_value = $this->cache->hash_get($this->cache_key,"cpu_load");
		if($store_value != $value){
			$this->db->query("update t_devices_status set cpu_load='$value' where SN='{$this->SN}'");
			$this->cache->hash_set($this->cache_key,"cpu_load",$value);
		}
		return $value;
	}
	
	public function set_online_client($value){
		$value = (int) $value;
		$store_value = $this->cache->hash_get($this->cache_key,"online_client");
		if($store_value != $value){
			$this->db->query("update t_devices_status set online_client='$value' where SN='{$this->SN}'");
			$this->cache->hash_set($this->cache_key,"online_client",$value);
		}
		return $value;
	}	
	
	public function set_status($value){
		$value = (string) $value;
		$value = substr($value, 0,10);
		$store_value = $this->cache->hash_get($this->cache_key,"status");
		if($store_value != $value){
			$this->db->query("update t_devices_status set status='$value' where SN='{$this->SN}'");
			$this->cache->hash_set($this->cache_key,"status",$value);
		}
		return $value;
	}
	
	public function set_poweron_time($value){
		$value = (int) $value;
		$store_value = $this->cache->hash_get($this->cache_key,"poweron_time");
		if($store_value != $value){
			$this->db->query("update t_devices_status set poweron_time='$value' where SN='{$this->SN}'");
			$this->cache->hash_set($this->cache_key,"poweron_time",$value);
		}
		return $value;
	}
	
	public function set_reboot_time($value){
		$value = (int) $value;
		$store_value = $this->cache->hash_get($this->cache_key,"reboot_time");
		if($store_value != $value){
			$this->db->query("update t_devices_status set reboot_time='$value' where SN='{$this->SN}'");
			$this->cache->hash_set($this->cache_key,"reboot_time",$value);
		}
		return $value;
	}	
	public function set_hd_version($value){
		$value = (string) $value;
		$store_value = $this->cache->hash_get($this->cache_key,"hd_version");
		if($store_value != $value){
			$this->db->query("update t_devices_status set hd_version='$value' where SN='{$this->SN}'");
			$this->cache->hash_set($this->cache_key,"hd_version",$value);
		}
		return $value;
	}
	public function set_hd_type ($value){
		$value = (string) $value;
		$store_value = $this->cache->hash_get($this->cache_key,"hd_type");
		if($store_value != $value){
			$this->db->query("update t_devices_status set hd_type='$value' where SN='{$this->SN}'");
			$this->db->query("update t_devices_list set device_type='$value' where SN='{$this->SN}'");
			$this->cache->hash_set($this->cache_key,"hd_type",$value);
		}
		return $value;
	}
	public function set_ping_time($value){
		$value = (int) $value;
		$store_value = $this->cache->hash_get($this->cache_key,"ping_time");
		if($store_value != $value){
			$this->db->query("update t_devices_status set ping_time='$value' where SN='{$this->SN}'");
			$this->db->query("update t_devices_list set ping_time='$value' where SN='{$this->SN}'");
			$this->cache->hash_set($this->cache_key,"ping_time",$value);
		}
		return $value;
	}
	
	public function set_version($value){
		$value = (string) $value;
		$value = substr($value, 0,10);
		$store_value = $this->cache->hash_get($this->cache_key,"version");
		if($store_value != $value){
			$this->db->query("update t_devices_status set version='$value' where SN='{$this->SN}'");
			//改变主表的版本号
			$this->db->query("update t_devices_list set version='$value' where SN='{$this->SN}'");
			//保存上个版本的版本号
			$this->db->query("update t_devices_status set oldversion='{$store_value}' where SN='{$this->SN}'");
			
			$this->cache->hash_set($this->cache_key,"version",$value);
		}
		return $value;
	}
	
	/**
	 * 获取手机验证码
	 * 
	 * @param string $mobile 用户手机号
	 * @return string 验证码（4位字符）
	 */
	
	public function get_mobile_vcode($mobile){
		$row = $this->db->get_row("select * from t_device_vcode where SN='{$this->SN}' and mobile = '$mobile'");
		if(!$row){
			$vcode = rand(1000,9999);
			$this->db->query("insert into t_device_vcode (SN,mobile,vcode,create_time) values ('{$this->SN}','$mobile','$vcode',{$this->timestamp})");
		}else{
			$vcode = $row->vcode;			
		}
		return $vcode;
		
	}
	
	
	protected function get_SN(){
		return $this->SN;
	}
	
	protected function get_usable(){
		if(!$this->cache->hash_exists($this->cache_key,"usable")){
			$result=$this->db->get_var("select usable from t_devices_list where SN='{$this->SN}'");
			if(!$result){
				return false;
			}
			$this->cache->hash_set($this->cache_key,"usable",$result);
		}else{
			$result=$this->cache->hash_get($this->cache_key,"usable");
		}
		return $result;
		
	}
	/**
	 * 绑定手机
	 * 
	 * @param string $SN 设备编号
	 * @param string $mobile 手机号码
	 * @return boolean
	 */
	public function bind_phone($SN,$mobile){
		$mobile=trim($mobile);
		$sql ="update t_devices_list set mobile='{$mobile}' where SN='{$SN}'";
		if( $this->db->query($sql) ){
			return true;	
		}else {
			return false;
		}
	}
	public static function get_device_by_SN($SN){
		$db=self::get_db_connect();
		$SN = $db->get_var("select SN from t_devices_list where SN='{$SN}'");
		if(!$SN){
			return false;
		}
		$db->close();
		return new \models\device($SN);
	}
	
	
}