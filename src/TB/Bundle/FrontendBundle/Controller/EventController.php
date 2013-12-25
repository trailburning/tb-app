<?php

namespace TB\Bundle\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class EventController extends Controller
{
    /**
     * @Route("/event/:slug", name="event")
     * @Template()
     */
    public function eventAction()
    {
        $event = $this->getDoctrine()
            ->getRepository('TBFrontendBundle:Event')
            ->findOneBySlug($slug);

        if (!$event) {
            throw $this->createNotFoundException(
                sprintf('Event %s not found', $slug)
            );
        }
    }

}
