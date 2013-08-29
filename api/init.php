<?php
ini_set('display_errors','1');
error_reporting(E_ALL);

die ($_SERVER['DOCUMENT_ROOT']);

if (array_key_exists('DOCUMENT_ROOT', $_SERVER) {
  $api_root = $_SERVER['DOCUMENT_ROOT'].'/api/';
else
  $api_root = '/app/api';

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
