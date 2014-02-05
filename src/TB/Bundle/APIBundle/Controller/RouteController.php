<?php

namespace TB\Bundle\APIBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Symfony\Component\HttpFoundation\Response;

class RouteController extends Controller
{
    /**
     * @Route("/route/{id}")
     * @Method("GET")
     */
    public function getRoute($id)
    {
        $postgis = $this->get('postgis');
        $route = $postgis->readRoute($id);
        
        $output = array('usermsg' => 'success', "value" => json_decode('{"route": '.$route->ToJSON().'}'));
        $response = new Response(json_encode($output));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
    
    /**
     * @Route("/route/{id}")
     * @Method("DELETE")
     */
    public function deleteRoute($id)
    {
        $postgis = $this->get('postgis');
        $route = $postgis->deleteRoute($id);
        
        $output = array('usermsg' => 'success', "value" => $id);
        $response = new Response(json_encode($output));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
    
    /**
     * @Route("/routes/user/{userId}")
     * @Method("GET")
     */
    public function getRoutesByUser($userId)
    {
        $postgis = $this->get('postgis');
        $routes = $postgis->readRoutes($userId, 10);
        $json_routes = array();
        foreach ($routes as $route) {
            $json_routes[] = $route->toJSON();
        }
        
        $output = array('usermsg' => 'success', "value" => json_decode('{"routes": ['. implode(',', $json_routes).']}'));
        $response = new Response(json_encode($output));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
    
}
