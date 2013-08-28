<?php
ini_set('display_errors','1');
error_reporting(E_ALL);

$_SERVER['DOCUMENT_ROOT'] = '/app';

// Assigning ./ as root for phpunit
$api_root = '/app/api';
$conf_path = $api_root.'/config/';

$include_path = get_include_path();
$include_path .= PATH_SEPARATOR.$api_root.'/lib/';
$include_path .= PATH_SEPARATOR.$api_root.'/vendor/';
$include_path .= PATH_SEPARATOR.$api_root.'/templates/';
set_include_path($include_path);

// Allow third party sites to make AJAX calls to this API
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');

// Strtotime should read datetime without timezone info as relative to UTC, and not current
// server timezone
date_default_timezone_set('UTC');
?>
