<?php

define("EZLOG_OUTPUT_CONSOLE",1);

define("EZLOG_LOG_LEVEL_DEBUG",1);
define("EZLOG_LOG_LEVEL_INFO",2);
define("EZLOG_LOG_LEVEL_WARN",3);
define("EZLOG_LOG_LEVEL_ERROR",4);
define("EZLOG_LOG_LEVEL_FATAL",5);

class kklog {
	
	private $output = false;
	private $format_prefix = "%d [%n] [%r] %v - ";
	private $time_start = false;
	private $record_level = "DEBUG|INFO|WARN|ERROR|FATAL";
	private $thread_number = false;
	
	function __construct($output,$format_prefix=false){
		$this->time_start = microtime(true);
		$this->output=$output;
		if($format_prefix){
			$this->format_prefix=$format_prefix;
		}
		$this->thread_number=strtoupper(helper::make_rand_string(4));
	}
	
	public function set_record_level($level){
		$this->record_level=$level;
		return;
	}
	
	public function error($str,$output=false){
		if(!strstr($this->record_level, "ERROR")){
			return;
		}
		if(!$output){
			$output=$this->output;
		}
		$this->write_log($str, $output, EZLOG_LOG_LEVEL_ERROR);
		return;		
	}
	
	public function debug($str,$output=false){
		if(!strstr($this->record_level, "DEBUG")){
			return;
		}
		if(!$output){
			$output=$this->output;
		}
		$this->write_log($str, $output, EZLOG_LOG_LEVEL_DEBUG);
		return;
	}
	
	public function info($str,$output=false){
		if(!strstr($this->record_level, "INFO")){
			return;
		}
		if(!$output){
			$output=$this->output;
		}
		$this->write_log($str, $output, EZLOG_LOG_LEVEL_INFO);
		return;
	}
	
	public function warn($str,$output=false){
		if(!strstr($this->record_level, "WARN")){
			return;
		}
		if(!$output){
			$output=$this->output;
		}
		$this->write_log($str, $output, EZLOG_LOG_LEVEL_WARN);
		return;
	}
	
	public function fatal($str,$output=false){
		if(!strstr($this->record_level, "FATAL")){
			return;
		}
		if(!$output){
			$output=$this->output;
		}
		$this->write_log($str, $output, EZLOG_LOG_LEVEL_FATAL);
		return;
	}
	
	
	private function write_log($str,$output,$level){
		$str=$this->format_prefix . $str;

		$str=$this->make_log_str($str,$level);

		if(is_numeric($output)){
			switch ($output){
				case EZLOG_OUTPUT_CONSOLE:
					echo $str . "\n";
					return;
			}
		}
		
		$time_str=date("Ymd",time());
		$output=str_replace("%d", $time_str, $output);
		$fp=fopen($output, "a+");
		if(!$fp){
			echo "open log file fail : $output";
			return;
		}
		$str.="\n";
		$result= fwrite($fp, $str);
		fclose($fp);
		@chmod($output, 0666);
		return;
	}
	
	/**
	 * 把日志字符串转化成实际内容
	 * 
	 * @param string $str 日志字符串
	 * @param string $format 格式描述
	 * 		
	 * 		%d 日期时间
	 * 		%m 方法名 __METMOD__
	 * 		%c 类名  __CLASS__
	 * 		%l 行号  __LINE__
	 * 		%r 程序执行的毫秒数
	 * 		%n 当前进程编号
	 * 		%v 日志级别 DEBUG INFO WARN ERROR FATAL
	 */
	private function make_log_str($str,$level){
		$str=str_replace("%d", date("c",time()), $str);
		$str=str_replace("%m", __METHOD__ , $str);
		$str=str_replace("%c", __CLASS__ , $str);
		$str=str_replace("%l", __LINE__ , $str);
		$str=str_replace("%n", $this->thread_number , $str);
		$str=str_replace("%r", round(((microtime(true)-$this->time_start) * 1000),4) , $str);
		
		switch ($level){
			case EZLOG_LOG_LEVEL_DEBUG:
				$str=str_replace("%v", "DEBUG", $str);
				break;
			case EZLOG_LOG_LEVEL_INFO:
				$str=str_replace("%v", "INFO", $str);
				break;			
			case EZLOG_LOG_LEVEL_WARN:
				$str=str_replace("%v", "WARN", $str);
				break;	
			case EZLOG_LOG_LEVEL_ERROR:
				$str=str_replace("%v", "ERROR", $str);
				break;
			case EZLOG_LOG_LEVEL_FATAL:
				$str=str_replace("%v", "FATAL", $str);
				break;
		}
		
		return $str;
		
	}
}