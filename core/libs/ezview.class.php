<?php

if(!PFW_INIT){
	echo "break in";
	die;
}

require_once PFW_LIBS_PATH . DIRECTORY_SEPARATOR . 'smarty' . DIRECTORY_SEPARATOR . 'Smarty.class.php';

class ezview {
	
	private $view_name = false;
	private $view = false;
	private $view_path = false;
	
	private $device = false;
	private $lang = false;
	
	private $lang_file = false;
	private $tpl_path = false;
	
	private $cache_hash = false;
	
	function __construct($view_name , $lang , $device , $default_lang , $default_device){

		$this->view = new \Smarty();
		$this->view_name = $view_name;
		$this->view_path = PFW_TPLS_PATH . DIRECTORY_SEPARATOR . $view_name ;
		if(!file_exists($this->view_path)){
			throw new Exception("view path not found :" . $this->view_path);
			return false;
		}
		
		$this->lang = strtolower($lang);
		$this->device = strtolower($device);
		$default_lang = strtolower($default_lang);
		$default_device = strtolower($default_device);
		
		$this->lang_file = PFW_TPLS_PATH . DIRECTORY_SEPARATOR . $view_name . DIRECTORY_SEPARATOR . "langs.{$this->lang}.php";
		if(!file_exists( $this->lang_file )){
			$this->lang_file = PFW_TPLS_PATH . DIRECTORY_SEPARATOR . $view_name . DIRECTORY_SEPARATOR . "langs.{$default_lang}.php";
		}
		if(!file_exists( $this->lang_file )){
			throw new Exception("default lang not found : {$this->lang_file}");
			die;
		}
		
		$this->tpl_path = PFW_TPLS_PATH . DIRECTORY_SEPARATOR . $view_name . DIRECTORY_SEPARATOR . $this->device;
		if(!file_exists( $this->tpl_path )){
			$this->tpl_path = PFW_TPLS_PATH . DIRECTORY_SEPARATOR . $view_name . DIRECTORY_SEPARATOR . $default_device;
		}
		if(!file_exists( $this->tpl_path )){
			throw new Exception("default tpl path not found : {$this->tpl_path} ");
			return false;
		}
		
		$this->cache_hash = $this->view_name . "-" . $this->lang . "-" . $this->device;
		
		$cache_path=PFW_CACHE_PATH . DIRECTORY_SEPARATOR . $this->cache_hash ;
		$this->view->setTemplateDir($this->tpl_path);
		helper::make_dir($cache_path."/compile/");
		$this->view->setCompileDir($cache_path."/compile/");
		helper::make_dir($cache_path."/config/");
		$this->view->setConfigDir($cache_path."/config/");
		helper::make_dir($cache_path."/cache/");
		$this->view->setCacheDir($cache_path."/cache/");

		return;
		
	}
	
	public function view_assign($name,$value){
		if(!$this->view){
			throw new Exception("View not be set!");
			die();
		}
		$this->view->assign($name,$value);
	}
	
	public function view_display($tpl_name){
		if(!\application::is_web_request()){
			throw new Exception("not is web request");
			die;
		}
		header("Content-Type: text/html; charset=utf-8");
		$html=$this->view_fetch($tpl_name);
		echo $html;
		return;
	}
	
	public function get_lang_string($key){
		require_once $this->lang_file;
		return helper::get_value_from_array($LANGS, $key);
	}
	
	public function view_fetch($tpl_name){
		if(!$this->view){
			throw new Exception("View not be set!");
			die();
		}
		require_once $this->lang_file;
		$this->view_assign('LANGS',$LANGS);
		$this->view_assign('view', $this->view_name);
		$this->view_assign('lang', $this->lang);
		$html = $this->view->fetch($tpl_name);
		return $html;
	}
	
	
}