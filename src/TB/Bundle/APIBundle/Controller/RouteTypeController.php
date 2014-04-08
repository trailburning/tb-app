<?php

namespace TB\Bundle\APIBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class RouteTypeController extends AbstractRestController
{
    
    /**
     * @Route("/route_type/list")
     * @Method("GET")
     */
    public function getListAction()
    {
        $routeTypes = $this->getDoctrine()
            ->getRepository('TBFrontendBundle:RouteType')
            ->findAll();
        
        $jsonRouteTypes = [];
        
        foreach ($routeTypes as $routeType) {
            $jsonRouteTypes[] = $routeType->export();
        }
        
        $output = ['usermsg' => 'success', 'value' => ['route_types' => $jsonRouteTypes]];

        return $this->getRestResponse($output);
    }
}
