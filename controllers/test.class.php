<?php
namespace controllers;

class test extends \web{

	public function main() {

		$en =  \kkcrypt::aes_cbc_encrypt("a","123");

		$this->output->fatal($en);

		$de = \kkcrypt::aes_cbc_decrypt($en,"123");

		$this->output->fatal($de);



		//print_r($this->server());

	}


}