<?php

namespace TB\Bundle\APIBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use TB\Bundle\APIBundle\Util\ApiException;
use Symfony\Component\HttpFoundation\Request;
use TB\Bundle\FrontendBundle\Event\RouteLikeEvent;
use TB\Bundle\FrontendBundle\Event\RouteUndoLikeEvent;

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
        $output = ['usermsg' => 'success', 'value' => ['route' => $route->export()]];
        
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
        $routesExport = [];
        foreach ($routes as $route) {
            $routesExport[] = $route->export();
        }
        
        $output = ['usermsg' => 'success', "value" => ['routes' => $routesExport]];

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
        $routesExport = [];
        foreach ($routes as $route) {
            $routesExport[] = $route->export();
        }
        
        $output = ['usermsg' => 'success', 'value' => ['routes' => $routesExport]];

        return $this->getRestResponse($output);
    }
    
    /**
     * @Route("/routes/search")
     * @Method("GET")
     */
    public function getRoutesSearch(Request $request)
    {
        $limit = $request->query->get('limit', 10);
        $offset = $request->query->get('offset', 0);
        
        $postgis = $this->get('postgis');
        $routes = $postgis->searchRoutes($limit, $offset, $count);
        $routesExport = [];
        foreach ($routes as $route) {
            $routesExport[] = $route->export();
        }
        
        $output = ['usermsg' => 'success', "value" => [
            'routes' => $routesExport,
            'totalCount' => $count,    
        ]];

        return $this->getRestResponse($output);
    }
    
    /**
     * Like a Trail
     *
     * @Route("/route/{routeId}/like", requirements={"userIdToFollow" = "\d+"})
     * @Method("PUT")
     */
    public function putRouteLike($routeId)
    {
        $request = $this->getRequest();
        if (!$request->headers->has('Trailburning-User-ID')) {
            throw new ApiException('Header Trailburning-User-ID is not set', 400);
        }
        $userId = $request->headers->get('Trailburning-User-ID');
        $user = $this->getDoctrine()
            ->getRepository('TBFrontendBundle:User')
            ->findOneById($userId);

        if (!$user) {
            throw new ApiException(sprintf('User with id "%s" does not exist', $userId), 404);
        }

        $route = $this->getDoctrine()
            ->getRepository('TBFrontendBundle:Route')
            ->findOneById($routeId);

        if (!$route) {
            throw new ApiException(sprintf('Route with id "%s" does not exist', $routeId), 404);
        }
        
        //check if user is already following
        if ($route->hasUserLike($user)) {
            throw new ApiException(sprintf('User %s already likes Trail %s', $user->getId(), $route->getId()), 400);
        }

        $route->addUserLike($user);
        
        $em = $this->getDoctrine()->getManager();
        $em->persist($route);
        $em->flush();
        
        // dispatch tb.route_like event
        $event = new RouteLikeEvent($route, $user);
        $dispatcher = $this->container->get('event_dispatcher'); 
        $dispatcher->dispatch('tb.route_like', $event);
        
        $output = array('usermsg' => 'success');
        
        return $this->getRestResponse($output);
    }
    
    /**
     * Unfollow a user
     *
     * @Route("/route/{routeId}/undolike", requirements={"userIdToUnfollow" = "\d+"})
     * @Method("PUT")
     */
    public function putRouteUndoLike($routeId)
    {
        $request = $this->getRequest();
        if (!$request->headers->has('Trailburning-User-ID')) {
            throw new ApiException('Header Trailburning-User-ID is not set', 400);
        }
        $userId = $request->headers->get('Trailburning-User-ID');
        $user = $this->getDoctrine()
            ->getRepository('TBFrontendBundle:User')
            ->findOneById($userId);

        if (!$user) {
            throw new ApiException(sprintf('User with id "%s" does not exist', $userId), 404);
        }
        
        $route = $this->getDoctrine()
            ->getRepository('TBFrontendBundle:Route')
            ->findOneById($routeId);

        if (!$route) {
            throw new ApiException(sprintf('Route with id "%s" does not exist', $routeId), 404);
        }
        
        //check if User is likes the Route
        if (!$route->hasUserLike($user)) {
            throw new ApiException(sprintf('User %s does not like Route %s', $user->getId(), $route->getId()), 400);
        }
        
        $route->removeUserLike($user);
        
        $em = $this->getDoctrine()->getManager();
        $em->persist($route);
        $em->flush();
        
        // dispath tb.route_undolike event
        $event = new RouteUndoLikeEvent($route, $user);
        $dispatcher = $this->container->get('event_dispatcher'); 
        $dispatcher->dispatch('tb.route_undolike', $event);
        
        $output = array('usermsg' => 'success');
        
        return $this->getRestResponse($output);
    }
    
    /**
     * @Route("/route/{routeId}/attribute/{attributeId}")
     * @Method("PUT")
     */
    public function putRouteAttribute($routeId, $attributeId)
    {
        $request = $this->getRequest();
        
        $route = $this->getDoctrine()
            ->getRepository('TBFrontendBundle:Route')
            ->findOneById($routeId);

        if (!$route) {
            throw new ApiException(sprintf('Route with id "%s" does not exist', $routeId), 404);
        }
        
        $attribute = $this->getDoctrine()
            ->getRepository('TBFrontendBundle:Attribute')
            ->findOneById($attributeId);

        if (!$attribute) {
            throw new ApiException(sprintf('Attribute with id "%s" does not exist', $attributeId), 404);
        }
        
        // Only add Attribute when the Route not already has this Attribute to prevebt errors in the Client
        if (!$route->hasAttribute($attribute)) {
            $route->addAttribute($attribute);
            $em = $this->getDoctrine()->getManager();
            $em->persist($route);
            $em->flush();
        }
        
        $output = array('usermsg' => 'success');

        return $this->getRestResponse($output);
    }
    
    /**
     * @Route("/route/{routeId}/attribute/{attributeId}")
     * @Method("DELETE")
     */
    public function deleteRouteAttribute($routeId, $attributeId)
    {
        $request = $this->getRequest();
        
        $route = $this->getDoctrine()
            ->getRepository('TBFrontendBundle:Route')
            ->findOneById($routeId);

        if (!$route) {
            throw new ApiException(sprintf('Route with id "%s" does not exist', $routeId), 404);
        }
        
        $attribute = $this->getDoctrine()
            ->getRepository('TBFrontendBundle:Attribute')
            ->findOneById($attributeId);

        if (!$attribute) {
            throw new ApiException(sprintf('Attribute with id "%s" does not exist', $attributeId), 404);
        }
        
        $route->removeAttribute($attribute);
        
        $em = $this->getDoctrine()->getManager();
        $em->persist($route);
        $em->flush();
        
        $output = array('usermsg' => 'success');

        return $this->getRestResponse($output);
    }
    
}
