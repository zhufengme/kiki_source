<?php
namespace controllers;

class system_commands extends \console{

    public function sample(){
        $this->output->line("this is a sample console application.","blue");
        return;
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
        $this->output->line(".env file has been updated.");
        $this->output->line("The current key is : $str_app_key","red");

        return;

    }


    public function make_model(){
        $str_class_name = null;

        if(!array_key_exists(2,$this->argv)){
            $str_class_name = $this->stdin("Please input model class name:");

        }else{
            $str_class_name = $this->argv[2];
        }

        $str_class_name = trim($str_class_name);

        if(!\helper::is_vaild_name($str_class_name)){
            $this->output->line("Class name not vaild.","red");
            return;
        }

        $str_src_filename = KKF_VENDOR_PATH . DIRECTORY_SEPARATOR . "model.class.php.tpl";
        $str_target_filename = KKF_MODELS_PATH . DIRECTORY_SEPARATOR . $str_class_name . ".class.php";

        if(file_exists($str_target_filename)){
            $is_overwrite = $this->confirm("Class file exist,overwrite it?",false);
            if(!$is_overwrite){
                $this->output->line("Class file not be created.","yellow");
                return;
            }
        }

        $str_file_content = file_get_contents($str_src_filename);
        $str_file_content = str_replace("{name}",$str_class_name,$str_file_content);

        file_put_contents($str_target_filename,$str_file_content);

        $this->output->line("Class file $str_target_filename created.");

        return;
    }



    public function make_rest(){
        $str_class_name = null;

        if(!array_key_exists(2,$this->argv)){
            $str_class_name = $this->stdin("Please input REST class name:");

        }else{
            $str_class_name = $this->argv[2];
        }

        $str_class_name = trim($str_class_name);

        if(!\helper::is_vaild_name($str_class_name)){
            $this->output->line("Class name not vaild.","red");
            return;
        }

        $str_src_filename = KKF_VENDOR_PATH . DIRECTORY_SEPARATOR . "rest.class.php.tpl";
        $str_target_filename = KKF_CONTROLLERS_PATH . DIRECTORY_SEPARATOR . $str_class_name . ".class.php";

        if(file_exists($str_target_filename)){
            $is_overwrite = $this->confirm("Class file exist,overwrite it?",false);
            if(!$is_overwrite){
                $this->output->line("Class file not be created.","yellow");
                return;
            }
        }

        $str_file_content = file_get_contents($str_src_filename);
        $str_file_content = str_replace("{name}",$str_class_name,$str_file_content);

        file_put_contents($str_target_filename,$str_file_content);

        $this->output->line("Class file $str_target_filename created.");

        return;
    }

    public function make_web(){
        $str_class_name = null;

        if(!array_key_exists(2,$this->argv)){
            $str_class_name = $this->stdin("Please input web class name:");

        }else{
            $str_class_name = $this->argv[2];
        }

        $str_class_name = trim($str_class_name);

        if(!\helper::is_vaild_name($str_class_name)){
            $this->output->line("Class name not vaild.","red");
            return;
        }

        $str_src_filename = KKF_VENDOR_PATH . DIRECTORY_SEPARATOR . "web.class.php.tpl";
        $str_target_filename = KKF_CONTROLLERS_PATH . DIRECTORY_SEPARATOR . $str_class_name . ".class.php";

        if(file_exists($str_target_filename)){
            $is_overwrite = $this->confirm("Class file exist,overwrite it?",false);
            if(!$is_overwrite){
                $this->output->line("Class file not be created.","yellow");
                return;
            }
        }

        $str_file_content = file_get_contents($str_src_filename);
        $str_file_content = str_replace("{name}",$str_class_name,$str_file_content);

        file_put_contents($str_target_filename,$str_file_content);

        $this->output->line("Class file $str_target_filename created.");

        return;
    }

    public function make_console(){
        $str_class_name = null;

        if(!array_key_exists(2,$this->argv)){
            $str_class_name = $this->stdin("Please input console class name:");

        }else{
            $str_class_name = $this->argv[2];
        }

        $str_class_name = trim($str_class_name);

        if(!\helper::is_vaild_name($str_class_name)){
            $this->output->line("Class name not vaild.","red");
            return;
        }

        $str_src_filename = KKF_VENDOR_PATH . DIRECTORY_SEPARATOR . "console.class.php.tpl";
        $str_target_filename = KKF_CONTROLLERS_PATH . DIRECTORY_SEPARATOR . $str_class_name . ".class.php";

        if(file_exists($str_target_filename)){
            $is_overwrite = $this->confirm("Class file exist,overwrite it?",false);
            if(!$is_overwrite){
                $this->output->line("Class file not be created.","yellow");
                return;
            }
        }

        $str_file_content = file_get_contents($str_src_filename);
        $str_file_content = str_replace("{name}",$str_class_name,$str_file_content);

        file_put_contents($str_target_filename,$str_file_content);

        $this->output->line("Class file $str_target_filename created.");
        $this->output->line("Please then modify console.json to add this method.");

        return;


    }

}