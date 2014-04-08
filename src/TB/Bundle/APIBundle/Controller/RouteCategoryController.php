<?php

namespace TB\Bundle\APIBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class RouteCategoryController extends AbstractRestController
{
    
    /**
     * @Route("/route_category/list")
     * @Method("GET")
     */
    public function getListAction()
    {
        $routeCategories = $this->getDoctrine()
            ->getRepository('TBFrontendBundle:RouteCategory')
            ->findAll();
        
        $jsonRouteCategories = [];
        
        foreach ($routeCategories as $routeCategory) {
            $jsonRouteCategories[] = $routeCategory->export();
        }
        
        $output = ['usermsg' => 'success', 'value' => ['route_types' => $jsonRouteCategories]];

        return $this->getRestResponse($output);
    }
}
