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
    switch (intval($e->getCode())) {
      case 400:
      case 404:
        $slim->log->notice($e->__toString());
        break;
      case 500:
        $slim->log->error($e->__toString());
        break;
    }
    $res->status($e->getCode());
    $res->body($e);
  }
  catch (\Exception $e) {
    $slim->getLog()->error($e->__toString());
    $res->status(500);
    $res->body('{"message": "An exception as occured", "value": '.json_encode($e->__toString()).'}');
  }
}

?>
