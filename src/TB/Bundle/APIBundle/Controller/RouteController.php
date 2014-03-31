<?php

namespace TB\Bundle\APIBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use TB\Bundle\APIBundle\Util\ApiException;
use Symfony\Component\HttpFoundation\Request;

class RouteController extends AbstractRestController
{
    /**
     * @Route("/route/{id}", requirements={"id" = "\d+"})
     * @Method("GET")
     */
    public function getRoute($id)
    {
        $postgis = $this->get('postgis');
        $route = $postgis->readRoute($id);
        $output = array('usermsg' => 'success', "value" => json_decode('{"route": '.$route->ToJSON().'}'));
        
        return $this->getRestResponse($output);
    }
    
    /**
     * @Route("/route/{id}", requirements={"id" = "\d+"})
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

        return $this->getRestResponse($output);
    }
    
    /**
     * @Route("/route/{id}", requirements={"id" = "\d+"})
     * @Method("DELETE")
     */
    public function deleteRoute($id)
    {
        $postgis = $this->get('postgis');
        $route = $postgis->deleteRoute($id);
        
        $output = array('usermsg' => 'success', "value" => $id);

        return $this->getRestResponse($output);
    }
    
    /**
     * @Route("/routes/user/{userId}", requirements={"userId" = "\d+"})
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
        $routes = $postgis->readRoutes($userId, 10, null, null, true);
        $json_routes = array();
        foreach ($routes as $route) {
            $json_routes[] = $route->toJSON();
        }
        
        $output = array('usermsg' => 'success', "value" => json_decode('{"routes": ['. implode(',', $json_routes).']}'));

        return $this->getRestResponse($output);
    }
    
   
    /**
     * Get Routes created by an authenticated user
     *
     * Optional query string parameters to filter the result: 
     * route_type_id int 
     * route_category_id int     
     * publish boolean
     * count int (default 10)
     *
     * @Route("/routes/my")
     * @Method("GET")
     */
    public function getRoutesByAuthenticatedUser(Request $request)
    {
        if (!$request->headers->has('Trailburning-User-ID')) {
            throw new ApiException('Header Trailburning-User-ID is not set', 400);
        }
        
        $userId = $request->headers->get('Trailburning-User-ID');
        $user = $this->getDoctrine()
            ->getRepository('TBFrontendBundle:User')
            ->findOneById($userId);

        if (!$user) {
            throw $this->createNotFoundException(
                sprintf('User with id "%s" not found', $userId)
            );
        }
        
        
        $route_type_id = $request->query->get('route_type_id', null);
        $route_category_id = $request->query->get('route_category_id', null);
        if ($request->query->get('publish') === 'true') {
            $publish = true;
        } elseif ($request->query->get('publish') === 'false') { 
            $publish = false;
        } else {
            $publish = null;
        }
        
        $count = $request->query->get('count', null);
        
        $postgis = $this->get('postgis');
        $routes = $postgis->readRoutes($userId, $count, $route_type_id, $route_category_id, $publish);
        $json_routes = array();
        foreach ($routes as $route) {
            $json_routes[] = $route->toJSON();
        }
        
        $output = array('usermsg' => 'success', "value" => json_decode('{"routes": ['. implode(',', $json_routes).']}'));

        return $this->getRestResponse($output);
    }
    
}
