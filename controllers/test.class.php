<?php
namespace controllers;

class test extends \web{

	public function main() {

		$this->set_view("welcome");

		$this->view->view_display("welcome.tpl");


		//print_r($this->server());

	}


}