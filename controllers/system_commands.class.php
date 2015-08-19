<?php
namespace controllers;

class system_commands extends \console{

    public function test(){
        var_dump($this->stdin("input","blue"));
    }

    public function key_generate(){
        if(\application::env("APP_KEY")){
            $is_reset = $this->confirm("APP_KEY exist , reset it?",false);
            if(!$is_reset){
                return;
            }
        }

        $str_app_key = \helper::make_rand_string(32);

        $_env_array = parse_ini_string(KKF_ENV);
        $_env_array['APP_KEY'] = $str_app_key;

        \helper::write_ini($_env_array,KKF_ENV_FILE);


        $this->output->line("Success! .env file has been updated.");
        $this->output->line("The current key is : $str_app_key","red");

        return;

    }

}