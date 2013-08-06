<?php

namespace TB;

function handleException($e) {
  $slim = \Slim\Slim::getInstance();
  $res = $slim->response();
  $res['Content-Type'] = 'application/json';
 
  try {
    throw $e;
  }
  catch (\TB\ApiException $e) {
    $res->status($e->getCode());
    $res->body($e);
  }
  catch (\Exception $e) {
    $res->status(500);
    $res->body('{"message": '.json_encode($e));
  }
}

?>
