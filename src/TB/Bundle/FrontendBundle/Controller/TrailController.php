<?php

namespace TB\Bundle\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class TrailController extends Controller
{
    /**
     * @Route("/trail/{slug}")
     * @Template()
     */
    public function trailAction($slug)
    {   
        $route = $this->getDoctrine()
            ->getRepository('TBFrontendBundle:Route')
            ->findOneBySlug($slug);

        if (!$route) {
            throw $this->createNotFoundException(
                sprintf('User %s not found', $slug)
            );
        }
        
        return array('route' => $route, 'user' => $route->getUser());
    }
}
