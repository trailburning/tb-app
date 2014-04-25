<?php

namespace TB\Bundle\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class EventController extends Controller
{
    
    /**
     * @Route("/events/{slug}")   
     * @Route("/events/{slug}/") 
     */
    public function legacyEventAction($slug)
    { 
        return $this->redirect($this->generateUrl('event', ['slug' => $slug]), 301);
    }
    
    /**
     * @Route("/event/{slug}", name="event")
     * @Template()
     */
    public function eventAction($slug)
    {
        $breadcrumb = array();
        
        $event = $this->getDoctrine()
            ->getRepository('TBFrontendBundle:Event')
            ->findOneBySlug($slug);

        if (!$event) {
            throw $this->createNotFoundException(
                sprintf('Event %s not found', $slug)
            );
        }
        
        $query = $this->getDoctrine()->getManager()
            ->createQuery('
                SELECT r FROM TBFrontendBundle:Route r
                JOIN r.eventRoutes e
                WHERE e.eventId=:eventId
                ORDER BY r.id')
            ->setParameter('eventId', $event->getId());
        $routes = $query->getResult();
        
        $breadcrumb[] = [
            'name' => 'event',
            'label' => trim($event->getTitle() . ' ' . $event->getTitle2()), 
            'params' => ['slug' => $event->getSlug()],
        ];
        
        return array(
            'event' => $event,
            'breadcrumb' => $breadcrumb,
            'routes' => $routes,
        );
    }
    
    /**
     * @Route("/events", name="events")
     * @Template()
     */    
    public function eventsAction(Request $request)
    {
        
        return [];
    }
    
    /**
     * @Template()
     */
    public function homepageEventsAction()
    {
        $query = $this->getDoctrine()->getManager()
            ->createQuery('
                SELECT e FROM TBFrontendBundle:Event e
                WHERE e.homepageOrder IS NOT NULL
                ORDER BY e.homepageOrder ASC');
        $events = $query->getResult();  
        
        return [
            'events' => $events,
        ];
    }
}
