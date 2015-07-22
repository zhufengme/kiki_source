<?php

class console extends \base {

    protected $argv = false;

    function __construct ($argv) {
        parent::__construct();
        if(\application::is_http_request()) {
            $this->log->fatal("request not in console");
            $this->output->fatal("request not in console");
            return;
        }
        $this->argv = $argv;
        $this->parse_cmd_line();
    }

    private function parse_cmd_line () {
        if(count($this->argv) <= 1) {
            $this->display_main_help();
            return;
        }

        if(array_search("-q", $this->argv) || array_search("--quiet", $this->argv)) {
            define("KKF_OUTPUT_QUIET", true);
        }

        if(array_search("--no-ansi", $this->argv)) {
            define("KKF_OUTPUT_NO_COLOR", true);
        }

        if(array_search("-V", $this->argv) || array_search("--version", $this->argv)) {
            $this->display_version();
            die;
        }
        if(array_search("-h", $this->argv) || array_search("--help", $this->argv)) {
            $this->display_main_help();
            die;
        }
        return;
    }

    final public static function start ($argv) {
        if(count($argv) <= 1) {
            $argv[]="-h";
            new \console($argv);
            return;
        }


        for ($i = 1; $i < count($argv); $i++) {

            if(!(substr($argv[$i], 0, 1) == "-" || substr($argv[$i], 0, 2) == "--")) {
                list($command_class, $command_method) = explode(":", $argv[$i]);

                if(!$command_method) {
                    $command_method = $command_class;
                    $command_class = "system";
                }

                $obj_config = \application::config("console", "cmds");


                if(!property_exists($obj_config, $command_class)) {
                    echo("command class define not found : $command_class \n\n");
                    $obj_console = new \console($argv);
                    $obj_console->display_main_help();
                    return;
                }

                if(!property_exists($obj_config->$command_class, $command_method)) {
                    echo("command method define not found : $command_method \n\n");
                    $obj_console = new \console($argv);
                    $obj_console->display_main_help();
                    return;
                }

                $str_config_method = $obj_config->$command_class->$command_method->method;

                list($str_obj_class_name, $str_obj_method_name) = explode("@", $str_config_method);

                $str_eval = "\$obj_cmd = new \\controllers\\{$str_obj_class_name}(\$argv);";
                eval($str_eval);

                $str_eval = "\$obj_cmd->{$str_obj_method_name}();";
                eval($str_eval);
                return;
            }

        }

        new \console($argv);
        return;

    }

    private
    function display_version () {
        $obj_config = \application::config("app");
        $str_version = $obj_config->version;

        $this->output->out("KiKi", "fatal");
        $this->output->out(" Framework ", "green");
        $this->output->out("version ");
        $this->output->out($str_version, "yellow");
        $this->output->line();
        return;
    }

    public
    function display_main_help () {
        $this->display_version();
        $this->output->line();
        $this->output->line("Usage:", "yellow");
        $this->output->line("  command [options] [arguments]");
        $this->output->line();

        $this->output->line("Options:", "yellow");
        $this->output->out("  -h, --help\t\t", "green");
        $this->output->out("Display this help message\n");

        $this->output->out("  -q, --quiet\t\t", "green");
        $this->output->out("Do not output any message\n");

        $this->output->out("  -V, --version\t\t", "green");
        $this->output->out("Display this application version\n");

        $this->output->out("      --no-ansi\t\t", "green");
        $this->output->out("Disable ANSI output\n");
        $this->output->line();

        $this->output->line("Available commands:", "yellow");

        $obj_conf = \application::config("console", "cmds");
        if(!property_exists($obj_conf, "system")) {
            return false;
        }

        $obj_conf_systems = $obj_conf->system;
        foreach ($obj_conf_systems as $str_method_name => $obj_conf_system) {
            $this->output->out("  " . $str_method_name . "\t\t\t", "green");
            $this->output->line($obj_conf_system->remark);
        }
        foreach ($obj_conf as $str_conf_cmd => $obj_conf_methods) {
            if($str_conf_cmd != "system") {
                $this->output->line(" " . $str_conf_cmd, "yellow");
                foreach ($obj_conf_methods as $str_conf_method_name => $obj_conf_method) {
                    $this->output->out("  " . $str_conf_cmd . ":" . $str_conf_method_name . "\t\t", "green");
                    $this->output->line($obj_conf_method->remark);
                }
            }
        }
    }


}