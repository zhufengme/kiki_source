<?php
namespace controllers;

\application::is_breakin();

class welcome extends web {

    public function main(){

        $this->output->out("is OK");
        return;
    }


}