<?php
if(!PFW_INIT){
	echo "break in";
	die;
}

class user_agent{
	
	private $match_rule = array();
	private $user_agent = false;
	private $device_type = false; 
	
	function __construct($user_agent){
		$this->user_agent = $user_agent;
		$this->rule_init();
		$this->match();
		return;
	}
	
	function __get($name){
		switch ($name){
			case 'device_type':
				return $this->device_type;
				break;
			case 'user_agent':
				return $this->user_agent;
				break;
		}
		return;
	}
	
	private function match(){
		foreach($this->match_rule as $item){
			if(strstr($this->user_agent, $item['key'])){
				$this->device_type = $item['device_type'];
				if($item['finish']){
					break;
				}
			}
		}
	}
	
	private function rule_init(){
		
		$item['key']='iPad';
		$item['device_type']='smart_web';
		$item['finish']=false;
		$this->match_rule[]=$item;
		
		$item['key']='iPhone';
		$item['device_type']='smart_phone';
		$item['finish']=false;
		$this->match_rule[]=$item;
		
		$item['key']='Android';
		$item['device_type']='smart_phone';
		$item['finish']=false;
		$this->match_rule[]=$item;

		$item['key']='Symbian';
		$item['device_type']='poor_phone';
		$item['finish']=true;
		$this->match_rule[]=$item;
		
		$item['key']='Windows Phone';
		$item['device_type']='smart_phone';
		$item['finish']=false;
		$this->match_rule[]=$item;
		
		$item['key']='BlackBerry';
		$item['device_type']='poor_phone';
		$item['finish']=true;
		$this->match_rule[]=$item;
		
		$item['key']='UCWEB';
		$item['device_type']='poor_phone';
		$item['finish']=true;
		$this->match_rule[]=$item;
		
		$item['key']='Macintosh';
		$item['device_type']='smart_web';
		$item['finish']=true;
		$this->match_rule[]=$item;

		$item['key']='MSIE';
		$item['device_type']='poor_web';
		$item['finish']=false;
		$this->match_rule[]=$item;
		
		$item['key']='MSIE 10.0';
		$item['device_type']='smart_web';
		$item['finish']=true;
		$this->match_rule[]=$item;
		
		$item['key']='MSIE 9.0';
		$item['device_type']='smart_web';
		$item['finish']=true;
		$this->match_rule[]=$item;	
		
	}

}