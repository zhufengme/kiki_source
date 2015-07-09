<?php 
namespace models;

if(!PFW_INIT){
	echo "break in";
	die;
}

class queue extends models {
	
	function __construct(){
		parent::__construct();
		return;		
	}
	
	public function main_loop_ping(){
		$counter = 0;
		while (true){
			$row = $this->cache->queue_r_pop("domy_PING_QUEUE");
			if(!$row){
				sleep(1);
			}else{
				$this->process_ping($row);
				$counter++;				
			}			
			if($counter>100){
				sleep(2);
				return;
			}
		}
	}
	
	public function main_loop_cmd_queue(){
		while (true){
			$this->timestamp = time();
			$results = $this->db->get_results("select * from t_cmd_queue where execute_time=0 order by CID limit 100");
			if($results){
				foreach ($results as $row){
					$this->process_cmd($row);
				}
				sleep(2);
			}else {
				sleep(2);
			}
		}
	}
	
	/**
	 *
	 * 通过烽火信通平台发短信
	 * @param string $recipient 手机号
	 * @param string $message 内容
	 */
	private function send_sms_by_fenghuo($recipient,$message){
		$recipient = str_replace ( "+86", "", $recipient );
	
		$message = "【JooMe】 ". $message;
	
		$url = "http://118.244.212.86:89/sendsms.asp?name=joome&password=6fa741c464&";
		$url .= "mobile={$recipient}&";
		$url .= "message=" . urlencode(iconv("UTF-8", "gbk", $message));
	
		$result = \helper::http_request ( $url, "GET", null, 30 );
	
		if ($result) {
			$result_array = array ('result' => $result );
			return json_encode ( $result_array );
		}
	
		return false;
	}
	/**
	 *
	 * 通过烽火信通平台发短信    new
	 * @param string $recipient 手机号
	 * @param string $message 内容
	 */
	private function send_sms_by_fenghuo_new($recipient,$message){
		$recipient = str_replace ( "+86", "", $recipient );
	
		$message = "【JooMe】 ". $message;
		$url="http://118.244.212.84:9080/SmsPro/sendmt.do";
		$arr_data=array(
			'spid'=>base64_encode('joome'),
			'password'=>base64_encode('joome6fa741c464'),
			'phone'=>$recipient,
			'uc'=>'0111',
			'msg'=>base64_encode(iconv("UTF-8", "gbk", $message))
				
		);
		/*
		$url = "http://118.244.212.86:89/sendsms.asp?name=joome&password=6fa741c464&";
		$url .= "mobile={$recipient}&";
		$url .= "message=" . urlencode(iconv("UTF-8", "gbk", $message));
		*/
		$result = \helper::http_request ( $url, "POST", $arr_data, 30 );
	
		if ($result) {
			$result_array = array ('result' => $result );
			return json_encode ( $result_array );
		}
	
		return false;
	}
	public function main_loop_sms_queue(){
		$timer = 0;
		while ( true ){
			$row = $this->cache->queue_r_pop("DOMY_SMS_QUEUE");
			if(!$row){
				sleep(5);
				$timer++;
				if($timer > 60){
					$this->db->query("set names utf8");
				}
				continue;
			}
			/*
			$url = "http://www.tosms.cn/Api/sendsms.ashx";
			$data = array(
					'username'=>'joome',
					'Password'=>'F8ACF26D86AA24D4E0D915B0DF9DA2B1',
					'Phones'=>substr($row['recipient'],3),
					'Content'=>$row['content']
			);
			$schedule_time =$this->db->get_var("select schedule_time from t_sms_queue where sms_id={$row['sms_id']}");
			//SendTime	 定时发送时间	  时间格式 yyyy-MM-dd HH:mm:ss
			if($schedule_time !=0){
				$data['SendTime']=date('Y-m-d H:i:s',$schedule_time);
			}
			$ch = curl_init();
			curl_setopt($ch,CURLOPT_URL,$url);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch,CURLOPT_POST,1);
			curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
			$output = curl_exec($ch);
			$output = addslashes($output);
			curl_close($ch);
			*/
			$output=$this->send_sms_by_fenghuo($row['recipient'], $row['content']);
			//$output=$this->send_sms_by_fenghuo_new($row['recipient'], $row['content']);
			$send_time=time();
			$this->db->query( "update t_sms_queue set sent_time={$send_time},result='{$output}' where sms_id={$row['sms_id']}" );
			
			/*debug
			$logMsg ="sms_return:".$output."; db-res:".$sms_res.';'.mysql_error();
            $path = "/data/www/domy-apis.ezlink-wifi.com/log.txt";
            if(!file_exists($path)){
                     touch($path);
              }
         	 $str = file_get_contents($path);
             file_put_contents( $path , $str."\n".'['.date('Y-m-d H:i:s').'] '.$logMsg );
            */
		     sleep(2);
		}
	}
	private function process_cmd($row){
		$this->timestamp = time();
		$list_key = "domy_CMD_LIST_" . $row->SN;
		if(!$this->cache->hash_exists($list_key,$row->CID)){
			$data = array (
							'CID' => $row->CID,
							'cmd_type' => $row->cmd_type,
							'body' => $row->body,
						);
			$this->cache->hash_set($list_key,$row->CID,$data);
			$this->db->query("update t_cmd_queue set queue_time = {$this->timestamp},try_count=try_count+1 where CID={$row->CID}");
			$this->log->info("CMD added to LIST:" .serialize($data));
		}
		return ;
	}
	
	private function process_ping($row){
		$this->log->debug("ping queue find: " . serialize($row));
		$device = device::get_device_by_SN($row['SN']);
		if(!$device){
			$this->log->fatal("SN not found : {$row['SN']} on process_ping");
			return;
		}
		$this->timestamp = time();
		$device->set_ping_time($this->timestamp);
				
		foreach ($row as $key => $value){
			switch ($key){
				case "CTL_RESULT":
					$this->ctl_result($device,$value);
					break;
				case "WAN" :
					$device->set_wan_ip($value);
					break;
				case "SID":
					$device->set_SID($value);
					break;
				case "FREE":
					$this->set_ping_mem($device,$value);
					break;
				case "LOAD":
					$device->set_cpu_load($value);
					break;
				case "OL":
					$device->set_online_client($value);
					break;
				case "STATUS":
					$device->set_status($value);
					break;
				case "VERSION":
					$device->set_version($value);
					break;
				case "VIRGIN":
					if($value=="1"){
						$device->set_poweron_time($this->timestamp);
					}
					break;
				case "JUST_REBOOT":
					if($value=="1"){
						$device->set_reboot_time($this->timestamp);
					}
					break;
				case "HD_VERSION":
						$device->set_hd_version($value);
					break;
				case "HD_TYPE":
					$device->set_hd_type($value);
					break;
			}
		}
		$device->dispose();
		return;
	}
	
	private function ctl_result($device,$value){
		$SN = $device->SN;
		$key = "domy_CMD_LIST_{$SN}";
		foreach ($value as $row){
			if($row['RET']==1){
				$this->db->query("update t_cmd_queue set result={$row['RET']} , execute_time = {$this->timestamp} where CID={$row['CID']}");
			}else{
				$this->db->query("update t_cmd_queue set result={$row['RET']},execute_time=0,try_count=try_count+1 where CID={$row['CID']}");
			}
			$this->cache->hash_del($key,$row['CID']);
		}
	}
	
	private function set_ping_mem($device,$value){
		
		list($mem_free,$mem_total)=explode("/", $value);
		
		$device->set_mem_free($mem_free);
		$device->set_mem_total($mem_total);
		
		return;
	}
	
	
}