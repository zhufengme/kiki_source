<?php
include_once '../core/system/application.php';

function load_lib ($lib_name) {

    $libs = \application::config("app", "libs");

    if(!property_exists($libs, $lib_name)) {
        throw new Exception("lib $lib_name not defined");
        return;
    }

    $lib = $libs->{$lib_name};

    $filename = PFW_LIBS_PATH . DIRECTORY_SEPARATOR . $lib;
    echo $filename;
    require_once PFW_LIBS_PATH . DIRECTORY_SEPARATOR . $lib;


    return;
}

return;

$s = json_encode($_result);

echo $s;