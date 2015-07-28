<?php
namespace controllers;

class test extends \rest{


	public function main(){
		var_dump(\PDO::getAvailableDrivers());
//		$obj = new \models\test();
//		$this->output->success($obj->main(10));
	}


	public function main2(){
		$this->api_response_error_message("10404");
		return;
	}

	public function main1() {

		$this->set_view("welcome");

		$this->view->view_display("welcome.tpl");


		//print_r($this->server());

	}


}