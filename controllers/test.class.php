<?php
namespace controllers;

class test extends \web{

	public function main() {
		$this->output->success("test case succ");
		$this->output->warn("test case warn ");
		$this->output->tips("test case tips ");
		$this->output->fatal("test case fatal ");
		$this->output->line("test case line");

	}


}