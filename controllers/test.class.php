<?php
namespace controllers;

class test extends \web{

	public function main() {

		print_r($this->set_cookie("name","value",3600000));
		print_r($this->cookie("name"));

	}


}