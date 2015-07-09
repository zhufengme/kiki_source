<?php

/**
 * 注意：这是一个配置文件的例子
 * 实际部署时，应该将本文件名字中间的 sample 字样去掉，如改为 global-config.php
 */

define ( 'PFW_LIBS_PATH', PFW_ROOT_PATH . "/libs" );
define ( 'PFW_CONTROLLERS_PATH', PFW_ROOT_PATH . "/../controllers" );
define ( 'PFW_MODELS_PATH', PFW_ROOT_PATH . "/../models" );
define ( 'PFW_PLUGINS_PATH', PFW_ROOT_PATH . "/plugins" );
define ( 'PFW_BASE_PATH', PFW_ROOT_PATH . "/base" );
define ( 'PFW_TPLS_PATH', PFW_ROOT_PATH . "/../tpls" );
define ( 'PFW_CACHE_PATH', PFW_ROOT_PATH . "/../cache" );

//开发模式
define("PFW_DEBUG_MODE", true);

//数据库配置

define ( 'PFW_DB_USERNAME', "root" );
define ( 'PFW_DB_PASSWORD', "hushuqi1120" );
define ( 'PFW_DB_DBNAME', "joome_portal_dev" );
define ( 'PFW_DB_HOST', "localhost" );

//支持的浏览器语言，逗号分开
define( 'PFW_ALLOW_LANGS', "zh-cn,en");

//cookies 前缀
define( 'PFW_COOKIE_PREFIX', "jm_");

//Redis配置
define ( 'PFW_CACHE_ENABLED', true );
define ( 'PFW_REDIS_HOST', "localhost" );
define ( 'PFW_REDIS_PORT', "6379" );

//日志配置
define ( 'PFW_LOG_ENABLED', true );
define ( 'PFW_LOG_FILENAME', "/var/log/joome_api_%d.log" );

if(PFW_DEBUG_MODE){
	define ( 'PFW_LOG_LEVEL', "DEBUG|INFO|WARN|ERROR|FATAL" );
	ini_set("display_errors",1);
}else{
	define ( 'PFW_LOG_LEVEL', "WARN|ERROR|FATAL" );
	ini_set("display_errors",0);
}

//是否自动session_start
define("PFW_AUTO_SESSION_START", false);


