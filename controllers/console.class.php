<?php
namespace controllers;

if(!PFW_INIT){
	echo "break in";
	die;
}

class console extends \base{
	public function default_action(){
		echo "Hello World!";
		return;
	}
	
	public function daemon_thread_ping(){
		while(true){
			$queue = new \models\queue();
			$queue->main_loop_ping();
		}
	}
	
	public function daemon_cmd_queue(){
		while(true){
			$queue = new \models\queue();
			$queue->main_loop_cmd_queue();				
		}
	}
	public function daemon_sms_queue(){
		while(true){
			$queue = new \models\queue();
			$queue->main_loop_sms_queue();				
		}
	}
	
	public function watchdog(){
		$rule = WATCH_DOG_RULE;
		$result = explode("|", $rule);
		
		foreach ($result as $row){
			$row = str_replace("\n", "", $row);
			$row = str_replace("\r", "", $row);
			if($row){
				list($tag,$cmd) = explode(",", $row);
				$num = exec("ps -ef | grep $tag | grep -v grep | wc -l");
				if(!$num){
					$this->log->fatal("watchdog detected $tag is down!");
					$this->log->fatal("try restart : $cmd");
					exec($cmd);
				}
			}
		}
	
	}
}