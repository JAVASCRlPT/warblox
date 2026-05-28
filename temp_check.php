<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
$s = file_get_contents('http://127.0.0.1:8000/login');
if ($s === false) {
    echo "GET FAILED\n";
    exit(1);
}
preg_match('/name=\\"_token\\" value=\\"([^\\"]+)\\"/i', $s, $m);
var_export($m);
