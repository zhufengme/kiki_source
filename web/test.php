<?php
include_once '../core/system/application.php';
print_r(application::config("app"));


die;


$_result = array(
    "libs" => array (


        'log' => "ezlog.class.php",
        'db' => "database.class.php",
    ),

);

$s= json_encode($_result);

echo $s;