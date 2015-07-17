<?php

if(!KKF_INIT){
	echo "break in";
	die;
}

require_once KKF_LIBS_PATH . DIRECTORY_SEPARATOR . 'smarty' . DIRECTORY_SEPARATOR . 'Smarty.class.php';

class kkview {
	
	private $view_name = false;
	private $view = false;
	private $view_path = false;
	
	private $device = false;
	private $lang = false;
	
	private $lang_file = false;
	private $tpl_path = false;
	
	private $cache_hash = false;


	function __construct($view_name , $lang , $device = "default"){

		$this->view = new \Smarty();
		$this->view_name = $view_name;
		$this->view_path = KKF_VIEWS_PATH . DIRECTORY_SEPARATOR . $view_name ;
		if(!file_exists($this->view_path)){
			throw new Exception("view path not found :" . $this->view_path);
			return false;
		}
		
		$this->lang = $lang;
		$this->device = strtolower($device);

		$this->lang_file = KKF_VIEWS_PATH . DIRECTORY_SEPARATOR . $view_name . DIRECTORY_SEPARATOR . "langs.{$this->lang}.php";
		if(!file_exists( $this->lang_file )){
			throw new Exception("lang file not found : {$this->lang_file}");
			die;
		}
		
		$this->tpl_path = KKF_VIEWS_PATH . DIRECTORY_SEPARATOR . $view_name . DIRECTORY_SEPARATOR . $this->device;
		if(!file_exists( $this->tpl_path )){
			throw new Exception("default tpl path not found : {$this->tpl_path} ");
			return false;
		}
		
		$this->cache_hash = $this->view_name . "-" . $this->lang . "-" . $this->device;
		
		$cache_path=KKF_CACHE_PATH . DIRECTORY_SEPARATOR . $this->cache_hash ;
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
		if(!\application::is_http_request()){
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
		return \helper::get_value_from_array($LANGS, $key);
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

	public static function get_support_langs($view_name){

		$view_path = KKF_VIEWS_PATH . DIRECTORY_SEPARATOR . $view_name;
		$result = false;

		if(!is_dir($view_path)){
			throw new Exception("view path not found");
			return;
		}

		$obj_dir = dir($view_path);

		while($file = $obj_dir->read()){
			$lang = null;
			if(substr($file,0,5)=='langs' && \helper::get_filetype($file)=='.php'){
				$lang = substr($file,strpos($file,".")+1);
				$lang = substr($lang,0,strrpos($lang,"."));
				$result[]=$lang;
			}
		}

		return $result;


	}

	public static function get_support_device($view_name){

		$view_path = KKF_VIEWS_PATH . DIRECTORY_SEPARATOR . $view_name;
		$result = false;

		if(!is_dir($view_path)){
			throw new Exception("view path not found");
			return;
		}

		$obj_dir = dir($view_path);

		while($file = $obj_dir->read()){
			$full_filename = $view_path.DIRECTORY_SEPARATOR.$file;
			if(is_dir($full_filename) && ($file!="..") && ($file!=".")){
				$result[]=$file;
			}
		}
		return $result;
	}
	
}