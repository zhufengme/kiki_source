<?php
namespace controllers;

if(!PFW_INIT){
	echo "break in";
	die;
}

class webtest extends web {
	
	function __construct(){
		parent::__construct();
		$this->force_ssl(true);
		$this->set_view("access_portal", "zh-cn", "smart_phone");
		return;
	}
	
	public function index(){
		$this->view->view_assign("abc", "测试变量");
		$this->view->view_display("test.tpl");
		
	}
}