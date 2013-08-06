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
    $res->body('{"message": "An exception as occured", "value": '.json_encode($e).'}');
  }
  catch (\Exception $e) {
    $res->status(500);
    $res->body('{"message": "An exception as occured", "value": '.json_encode($e).'}');
  }
}

?>
