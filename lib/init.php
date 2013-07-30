<?php

ini_set('display_errors','1');
error_reporting(E_ALL);

$api_root = $_SERVER["DOCUMENT_ROOT"];
$conf_path = $api_root.'/config/';

$include_path = get_include_path().PATH_SEPARATOR.$api_root.'/lib/'.PATH_SEPARATOR.$api_root.'/vendor/';
set_include_path($include_path);

?>
