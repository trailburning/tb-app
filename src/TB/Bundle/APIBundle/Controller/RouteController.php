<?php

namespace TB\Bundle\APIBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class RouteController extends Controller
{
    /**
     * @Route("/route/{id}", name="get_route")
     * @Method("GET")
     */
    public function getRouteAction($id)
    {
        
        $postgis = $this->get('postgis');
        $route = $postgis->readRoute($id);
        
        $output = array("value" => '{"route": '.$route->ToJSON().'}', 'usermsg' => 'success');
        $response = new Response(json_encode($output));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
    
    /**
     * @Route("/route/{id}", name="delete_route")
     * @Method("DELETE")
     */
    public function deleteRouteAction($id)
    {
        $postgis = $this->get('postgis');
        $route = $postgis->deleteRoute($id);
        
        $output = array("value" => $id, 'usermsg' => 'success');
        $response = new Response(json_encode($output));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
    
    /**
     * @Route("/routes/user/{userId}", name="get_routes_by_user")
     * @Method("GET")
     */
    public function getRoutesByUser($userId)
    {
        $postgis = $this->get('postgis');
        $routes = $db->readRoutes($userId, 10);
        
        $output = array("value" => '{"routes": ['. implode(',', $json_routes).']}', 'usermsg' => 'success');
        $response = new Response(json_encode($output));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
    
}
