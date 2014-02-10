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
            $jsonRouteTypes[] = $routeType->toJSON();
        }
        
        $output = array('usermsg' => 'success', 'value' => json_decode('{"route_types": ['. implode(',', $jsonRouteTypes).']}'));

        return $this->getRestResponse($output);
    }
}
