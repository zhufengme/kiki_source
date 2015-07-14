<?php
/**
 * Created by PhpStorm.
 * User: zhufengme
 * Date: 15/7/10
 * Time: 下午6:15
 */

$_result = array (

    'libs' => array (

        'log' => 'ezlog.class.php' ,
        'db' => 'database.class.php' ,
    ) ,
);


echo json_encode($_result);

$s= json_encode($_result);

print_r(json_decode($s));

$obj = json_decode($s);

foreach($obj->libs as $key => $value){
    echo $key;
    echo $value;
}
