<?php 
namespace models;

if(!PFW_INIT){
	echo "break in";
	die;
}

define("WECHAT_SSID_TYPE_JOOME", 1);
define("WECHAT_SSID_TYPE_PASSWORD", 2);
define("WECHAT_SSID_TYPE_OPEN", 3);
define("WECHAT_SSID_TYPE_FEE", 4);
define("WECHAT_SSID_TYPE_VERIFY_MOBILE", 5);
define("WECHAT_SSID_TYPE_VERIFY_ID", 6);


class wechat_wifi extends models {
	
	private $wifi_id = false;
	private $cache_key = false;
	
	function __construct($wifi_id){
		parent::__construct();
		$this->wifi_id = (int) $wifi_id;
		$this->cache_key = __CLASS__.":{$wifi_id}";
		
		if(!$this->cache->exists($this->cache_key)){
			$row=$this->db->get_row("select wifi_id from t_wechat_wifis_list where wifi_id={$this->wifi_id}");
			if(!$row){
				$this->fatal("wifi_id {$wifi_id} not found");
				return;
			}
		}
		
		$this->cache->hash_set($this->cache_key,"_",$this->timestamp,3600);
		
		return;
	}
	
	public static function add($lat,$lon,$loc_name,$loc_address,$ssid_name,$ssid_type,$ssid_password,$loc_address,$mayor){
		$db=self::get_db_connect();
		$db->query("insert into t_wechat_wifis_list (loc_name,lat,lon,ssid_name,ssid_type,loc_address,ssid_password,mayor_UID) values ('{$loc_name}',$lat,$lon,'{$ssid_name}',$ssid_type,'$loc_address','$ssid_password',{$mayor->UID})");
		$wifi_id = $db->insert_id;
		if($wifi_id){
			return new \models\wechat_wifi($wifi_id);
		}
		return false;
	}
	
	public function get_info(){
		$cache_result=$this->cache->hash_get($this->cache_key);
		$result_list=array('wifi_id','loc_name','lat','lon','ssid_name','ssid_type','loc_address','ssid_password','mayor_UID');
	
		foreach ($result_list as $key){
			if(key_exists($key, $cache_result)){
				$result[$key] = $cache_result[$key];
			}else{
				$cmd_str="\$var=\$this->get_{$key}();";
				eval($cmd_str);
				$result[$key] = $var;
			}
		}
	
		return $result;
	}
	
	protected function get_mayor_UID(){
		if(!$this->cache->hash_exists($this->cache_key,"mayor_UID")){
			$result=(int) $this->db->get_var("select mayor_UID from t_wechat_wifis_list where wifi_id={$this->wifi_id}");
			$this->cache->hash_set($this->cache_key,"mayor_UID",$result);
		}else{
			$result=$this->cache->hash_get($this->cache_key,"mayor_UID");
		}
		return $result;
	}
	
	protected function get_ssid_password(){
		if(!$this->cache->hash_exists($this->cache_key,"ssid_password")){
			$result=(string) $this->db->get_var("select ssid_password from t_wechat_wifis_list where wifi_id={$this->wifi_id}");
			if($result === ""){
				$result = null;
			}
			$this->cache->hash_set($this->cache_key,"ssid_password",$result);
		}else{
			$result=$this->cache->hash_get($this->cache_key,"ssid_password");
		}
		return $result;
	}
	
	protected function get_loc_address(){
		if(!$this->cache->hash_exists($this->cache_key,"loc_address")){
			$result=(string) $this->db->get_var("select loc_address from t_wechat_wifis_list where wifi_id={$this->wifi_id}");
			if($result === ""){
				$result = null;
			}
			$this->cache->hash_set($this->cache_key,"loc_address",$result);
		}else{
			$result=$this->cache->hash_get($this->cache_key,"loc_address");
		}
		return $result;
	}
	
	protected function get_ssid_type(){
		if(!$this->cache->hash_exists($this->cache_key,"ssid_type")){
			$result=(int) $this->db->get_var("select ssid_type from t_wechat_wifis_list where wifi_id={$this->wifi_id}");
			$this->cache->hash_set($this->cache_key,"ssid_type",$result);
		}else{
			$result=$this->cache->hash_get($this->cache_key,"ssid_type");
		}
		return $result;
	}
	
	protected function get_ssid_name(){
		if(!$this->cache->hash_exists($this->cache_key,"ssid_name")){
			$result=(string) $this->db->get_var("select ssid_name from t_wechat_wifis_list where wifi_id={$this->wifi_id}");
			if($result === ""){
				$result = null;
			}
			$this->cache->hash_set($this->cache_key,"ssid_name",$result);
		}else{
			$result=$this->cache->hash_get($this->cache_key,"ssid_name");
		}
		return $result;
	}
	
	protected function get_lon(){
		if(!$this->cache->hash_exists($this->cache_key,"lon")){
			$result=(float) $this->db->get_var("select lon from t_wechat_wifis_list where wifi_id={$this->wifi_id}");
			$this->cache->hash_set($this->cache_key,"lon",$result);
		}else{
			$result=$this->cache->hash_get($this->cache_key,"lon");
		}
		return $result;
	}
	
	protected function get_lat(){
		if(!$this->cache->hash_exists($this->cache_key,"lat")){
			$result=(float) $this->db->get_var("select lat from t_wechat_wifis_list where wifi_id={$this->wifi_id}");
			$this->cache->hash_set($this->cache_key,"lat",$result);
		}else{
			$result=$this->cache->hash_get($this->cache_key,"lat");
		}
		return $result;
	}
	
	protected function get_loc_name(){
		if(!$this->cache->hash_exists($this->cache_key,"loc_name")){
			$result=(string) $this->db->get_var("select loc_name from t_wechat_wifis_list where wifi_id={$this->wifi_id}");
			if($result === ""){
				$result = null;
			}
			$this->cache->hash_set($this->cache_key,"loc_name",$result);
		}else{
			$result=$this->cache->hash_get($this->cache_key,"loc_name");
		}
		return $result;
	}
	
	protected function get_wifi_id(){
		return (int) $this->wifi_id;
	}
	
	/**
	 * 得到附近的WiFi热点
	 * 
	 * @param float $lat 中心点纬度
	 * @param float $lon 中心点经度
	 * @param int $count 最大返回数量
	 * @param int $max_distance 最大距离
	 *  
	 */
	public static function get_near_wifis($lat,$lon,$count,$max_distance=1000){
		$db=self::get_db_connect();
		$sql='select wifi_id,round(ACOS(SIN(('.$lat.' * 3.14159) / 180 ) *SIN((lat * 3.14159) / 180 ) +COS(('.$lat.' * 3.14159) / 180 ) * COS((lat * 3.14159) / 180 ) *COS(('.$lon.'* 3.14159) / 180 - (lon * 3.14159) / 180 ) ) * 6378317) as distance 
					from t_wechat_wifis_list where 
					lat > '.$lat.'-1 and 
					lat < '.$lat.'+1 and 
					lon > '.$lon.'-1 and 
					lon < '.$lon.'+1  
					order by ACOS(SIN(('.$lat.' * 3.14159) / 180 ) *SIN((lat * 3.14159) / 180 ) +COS(('.$lat.' * 3.14159) / 180 ) * COS((lat * 3.14159) / 180 ) *COS(('.$lon.'* 3.14159) / 180 - (lon * 3.14159) / 180 ) ) * 6378317 asc limit '.$count;		
		$results = false;
		$rows = $db->get_results($sql);
		if(!$rows){
			return false;
		}
		foreach ($rows as $row){
			if($row->distance <= $max_distance){
				$result['wifi'] = new wechat_wifi($row->wifi_id);
				$result['distance'] = $row->distance;
				$results[]=$result;
			}
		}
		return $results;
	}
	
	
}