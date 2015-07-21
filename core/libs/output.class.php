<?php

class output {

    function __construct () {

    }

    public function out ($str) {
            echo $str;
    }

    public function success($text){
        $this->color_line_text($text,"success");
        return;
    }
    public function warn($text){
        $this->color_line_text($text,"warn");
        return;
    }
    public function fatal($text){
        $this->color_line_text($text,"fatal");
        return;
    }
    public function tips($text){
        $this->color_line_text($text,"tips");
        return;
    }
    public function line($text,$color="default"){
        $this->color_line_text($text,$color);
        return;
    }

    public function add_http_header ($key, $value) {
        header("{$key}: {$value}");
        return;
    }

    public function set_cookie ($key, $value, $expirein, $domain = "/") {
        if(\application::is_http_request()) {
            setcookie(PFW_COOKIE_PREFIX . $key, $value, time() + $expirein, "/");
        }
    }

    private function color_line_text ($text, $color = "default") {
        $color_list_web = array(
            "success" => "#008000",
            "warn" => '#FFA500',
            "fatal" => '#FF0000',
            "tips" => '#0000FF',

            "green" => "#008000",
            "yellow" => '#FFA500',
            "red" => '#FF0000',
            "blue" => '#0000FF',

            "normal" => 'default'
        );
        $color_list_console = array(
            "success" => '\033[32m',
            "warn" => '\033[33m',
            "fatal" => '\033[41;37m',
            "tips" => '\033[34m',

            "green" => '\033[32m',
            "yellow" => '\033[33m',
            "red" => '\033[41;37m',
            "blue" => '\033[34m',

            "normal" => '\033[37m'
        );

        if(\application::is_http_request()){
            $nr = "<br/>";
            $color_prefix = "<span style='color:{$color_list_web[$color]}'}>";
            $color_end = "</span>";
        }else{
            $color_prefix = $color_list_console[$color];
            $color_end = '\033[0m';
            $nr = "\n";
        }


        $s = $color_prefix . $text . $color_end . $nr;
        $this->out($s);
        return;
    }

}