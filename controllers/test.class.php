<?php
namespace controllers;

class test extends \rest{


	public function main(){
		$this->api_response_error_message("10404");
		return;
	}

	public function main1() {

		$this->set_view("welcome");

		$this->view->view_display("welcome.tpl");


		//print_r($this->server());

	}


}