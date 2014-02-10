<?php

namespace TB\Bundle\APIBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Symfony\Component\HttpFoundation\Response;
use TB\Bundle\APIBundle\Util\ApiException;

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
     * @Method("PUT")
     */
    public function putRoute($id)
    {
        $request = $this->getRequest();
        if (!$request->request->has('json')) {
            throw new ApiException('Missing JSON object in request data', 400);
        }
        
        $route = $this->getDoctrine()
            ->getRepository('TBFrontendBundle:Route')
            ->findOneById($id);

        if (!$route) {
            throw new ApiException(sprintf('Route with id "%s" does not exist', $id), 404);
        }
        
        try {
            $route->updateFromJSON($request->request->get('json'));
        } catch (\Exception $e) {
            throw new ApiException($e->getMessage(), 400);
        }
        
        $em = $this->getDoctrine()->getManager();
        $em->persist($route);
        $em->flush();
        
        $output = array('usermsg' => 'success', "value" => $id);
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
        $user = $this->getDoctrine()
            ->getRepository('TBFrontendBundle:User')
            ->findOneById($userId);

        if (!$user) {
            throw new ApiException(sprintf('User with id "%s" does not exist', $userId), 404);
        }
        
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
