<?php

namespace TB;

function handleException(\Exception $e) {
  $slim = \Slim\Slim::getInstance();
  $res = $slim->response();
  $res['Content-Type'] = 'application/json';
  $res->status(500);
  $res->body($e);
}

function handleApiException(\TB\ApiException $e) {
  $slim = \Slim\Slim::getInstance();
  $res = $slim->response();
  $res['Content-Type'] = 'application/json';
  $res->status($e->getCode());
  $res->body($e);
}

?>
