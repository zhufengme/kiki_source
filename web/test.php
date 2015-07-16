
<?php
include_once '../core/system/application.php';

$obj = new \base();
$obj->output->fatal("abc");


die;
spl_autoload_register("load_classes");
$obj = new \models\aa\routes();


function load_classes ($class_name) {

    echo $class_name;

    $level1_path = false;

    if(substr($class_name,0,7)=="models\\"){
        $level1_path = KKF_MODELS_PATH;
        $class_name = substr($class_name,7);
    }

    if(substr($class_name,0,12)=="controllers\\"){
        $level1_path = KKF_CONTROLLERS_PATH;
        $class_name= substr($class_name,12);
    }

    if(!$level1_path){
        return;
    }

    $class_name = str_replace("\\",DIRECTORY_SEPARATOR,$class_name);
    $class_filename = $level1_path.DIRECTORY_SEPARATOR.$class_name;

    if(file_exists($class_filename)) {
        require_once $class_filename;
        return;
    } else {
        throw new Exception("class " . htmlentities($class_name) . " file not found : " . htmlentities($filename));
        $this->log->error("class " . $class_name . " file not found : " . $filename);
        return;
    }
}

