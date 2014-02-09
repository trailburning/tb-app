<?php

namespace TB\Bundle\APIBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ExceptionController extends Controller
{

    public function showAction(Request $request, $exception)
    {
        $output = array('usermsg' => $exception->getMessage(), "value" => null);

        $response = new Response();
        $response->setContent(json_encode($output));
        $response->setStatusCode($exception->getCode());
        $response->headers->set('Content-Type', 'application/json');
        
        return $response;
    }
    
}
