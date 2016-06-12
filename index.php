<?php
use src\RequestSize;
require_once( __DIR__ . '/autoload.php' );
//set_error_handler("custom_warning_handler", E_WARNING);
try {
    if (isset($_GET['url'])) $argv[1] = $_GET['url'];
     new RequestSize($argv);

}
catch(Exception $e) {
    echo $e->getMessage()."\n";
}



function custom_warning_handler($errno, $errstr) {
    throw new \Exception("warning: ".$errstr);
}
