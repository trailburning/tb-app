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
            $jsonRouteCategories[] = $routeCategory->toJSON();
        }
        
        $output = array('usermsg' => 'success', 'value' => json_decode('{"route_types": ['. implode(',', $jsonRouteCategories).']}'));

        return $this->getRestResponse($output);
    }
}
