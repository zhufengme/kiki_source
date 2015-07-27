<?php

class output {

    function __construct () {

    }

    public function out ($text,$color=false) {
        if(defined("KKF_OUTPUT_QUIET")){
            return;
        }

        if(defined("KKF_OUTPUT_NO_COLOR")){
            $color=false;
        }

        $color_list_web = array(
            "success" => "#008000",
            "warn" => '#FFA500',
            "fatal" => '#FF0000',
            "tips" => '#0000FF',

            "green" => "#008000",
            "yellow" => '#FFA500',
            "red" => '#FF0000',
            "blue" => '#0000FF',

        );
        $color_list_console = array(
            "success" => "\x1b[32m",
            "warn" => "\x1b[33m",
            "fatal" => "\x1b[41;37m",
            "tips" => "\x1b[34m",

            "green" => "\x1b[32m",
            "yellow" => "\x1b[33m",
            "red" => "\x1b[31m",
            "blue" => "\x1b[34m",
        );


        $color_prefix=null;
        $color_end=null;

        if(\application::is_http_request()) {
            if($color) {
                $color_prefix = "<span style='color:{$color_list_web[$color]}'}>";
                $color_end = "</span>";
            }
        } else {
            if($color) {
                $color_prefix = $color_list_console[$color];
                $color_end = "\x1b[0m";
            }
        }

        if(\application::is_http_request()){
            $text = htmlentities($text);
        }

        $s = $color_prefix . $text . $color_end;

        echo $s;

        return;

    }

    public function success ($text) {
        $this->color_line_text($text, "success");
        return;
    }

    public function warn ($text) {
        $this->color_line_text($text, "warn");
        return;
    }

    public function fatal ($text) {
        $this->color_line_text($text, "fatal");
        return;
    }

    public function tips ($text) {
        $this->color_line_text($text, "tips");
        return;
    }

    public function line ($text=null, $color = false) {
        $this->color_line_text($text, $color);
        return;
    }

    private function color_line_text ($text,$color=false) {
        $this->out($text,$color);
        if(!\application::is_http_request()){
            $this->out("\n");
        }else{
            $this->out("<br/>");
        }
        return;
    }

}