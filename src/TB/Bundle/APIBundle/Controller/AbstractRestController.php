<?php 

namespace TB\Bundle\APIBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Response;

class AbstractRestController extends Controller
{
    protected function getRestResponse($output, $statusCode = 200)
    {
        $response = new Response(json_encode($output));
        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode($statusCode);
        
        return $response;
    }
}