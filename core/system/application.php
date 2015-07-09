<?php

define ( "PFW_ROOT_PATH", substr ( __DIR__, 0, strlen ( __DIR__ ) - 7 ) );

include_once __DIR__ . '/global-config.php';

if(PFW_DEBUG_MODE){
	error_reporting(E_ALL ^ E_DEPRECATED ^ E_NOTICE);
}else{
	error_reporting(0);
}

final class application {
		
	/**
	 * 
	 * 判断是否为web请求
	 */
	final static function is_web_request() {
		if (empty ( $_SERVER ['REQUEST_METHOD'] )) {
			return false;
		} else {
			return true;
		}
	}
	
	final private static function load_controller($controller_name, $action_name) {

		if (! file_exists ( PFW_CONTROLLERS_PATH . DIRECTORY_SEPARATOR . $controller_name . ".class.php" )) {

			if(self::is_web_request()){
				header ( "HTTP/1.1 404 controller " . htmlspecialchars ( $controller_name ) . " not found" );
			}else{
				echo "controller " .  $controller_name . " not found \n";
			}
			
			die ();
		}

		require_once PFW_CONTROLLERS_PATH . DIRECTORY_SEPARATOR . $controller_name . '.class.php';
		
		if (! class_exists ( "\\controllers\\{$controller_name}" , false )) {
			
			if(self::is_web_request()){
				header ( "HTTP/1.1 404 controller " . htmlspecialchars ( $controller_name ) . " not define" );
			}else{
				echo ( "controller " . $controller_name . " not define \n" );
			}
			
			die ();
		}

		$str = "\$controller=new \\controllers\\{$controller_name}();";
		eval ( $str );
		
		if (! method_exists ( $controller, $action_name )) {
			$action_name="index";
		}
		
		if (! method_exists ( $controller, $action_name )) {
			if(self::is_web_request()){
				header ( "HTTP/1.1 404 action_name " . htmlspecialchars ( $action_name ) . " not define" );
			}else{
				echo ( "action_name " . $action_name . " not define \n" );
			}
			die();
		}
		$str = '$controller->' . $action_name . '();';
		eval ( $str );
		return;
	
	}
	
	final private static function load_libs() {
		require_once PFW_LIBS_PATH . DIRECTORY_SEPARATOR . 'helper.class.php';
		require_once PFW_BASE_PATH . DIRECTORY_SEPARATOR . 'base.class.php';
		require_once PFW_BASE_PATH . DIRECTORY_SEPARATOR . 'rest.class.php';
		require_once PFW_BASE_PATH . DIRECTORY_SEPARATOR . 'models.class.php';
		require_once PFW_LIBS_PATH . DIRECTORY_SEPARATOR . 'ezsql' . DIRECTORY_SEPARATOR . 'ez_sql_core.php';
		require_once PFW_LIBS_PATH . DIRECTORY_SEPARATOR . 'ezsql' . DIRECTORY_SEPARATOR . 'ez_sql_mysql.php';
		require_once PFW_LIBS_PATH . DIRECTORY_SEPARATOR . 'database.class.php';
		require_once PFW_LIBS_PATH . DIRECTORY_SEPARATOR . 'input.class.php';

		return;
	}

	final static function console_start($argv,$argc) {
		
		define ( 'PFW_INIT', true );
		self::load_libs ();
		
		
		$controller_name = helper::get_value_from_array($argv, 1,"console");
		if (! $controller_name) {
			echo ( "controller not set! \n" );
			die ();
		}
		$action_name = helper::get_value_from_array($argv, 2,"default_action");
		
		if (self::is_web_request ()) {
			header ( "HTTP/1.1 403 only for console!" );
			die ();
		}
				

		self::load_controller ( $controller_name, $action_name );
		return;
	
	}	
	
	final static function web_start() {

		define ( 'PFW_INIT', true );
		self::load_libs ();
		
		if (! self::is_web_request ()) {
			echo ( "only for web request!\n" );
			die ();
		}
		
		if (PFW_AUTO_SESSION_START) {
			session_start ();
		}
		
		$argvs=input::get_web_path();
		
		/*
		if(!key_exists(0, $argvs)){
			header("HTTP/1.1 403 REST verison not set");
			echo "REST verison not set";
			var_dump($argvs);
			die;
		}
		
		if($argvs[0]!="v4"){
			header("HTTP/1.1 403 REST verison error");
			echo "REST verison error";
			var_dump($argvs);
			die;
		}
		
		if(!key_exists(1, $argvs)){
			header("HTTP/1.1 403 REST object not set");
			echo "REST object not set";
			var_dump($argvs);
			die;
		}
		
		$controller_name = $argvs[1];
		$action_name = "index";
		
		if(key_exists(2, $argvs)){
			$action_name = $argvs[2];
		}
		
	
		self::load_controller ( $controller_name, $action_name );
		*/
		
		self::load_controller ( "domy_apis_a", "main" );
		
		return;
	
	}
	
}