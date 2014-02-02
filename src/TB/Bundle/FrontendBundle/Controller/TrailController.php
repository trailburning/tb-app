<?php

namespace TB\Bundle\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class TrailController extends Controller
{
    
    /**
     * @Route("/trails/{trailSlug}")
     * @Route("/trails/{trailSlug}/")
     * @Route("/events/{eventSlug}/{trailSlug}")
     * @Route("/events/{eventSlug}/{trailSlug}/")    
     */
    public function legacyTrailAction($trailSlug)
    { 
        return $this->redirect($this->generateUrl('trail', ['trailSlug' => $trailSlug]), 301);
    }
    
    /**
     * @Route("/trail/{trailSlug}", name="trail")
     * @Route("/editorial/{editorialSlug}/trail/{trailSlug}", name="editorial_trail")
     * @Template()
     */
    public function trailAction($trailSlug, $editorialSlug = null, $eventSlug = null)
    {   
        $editorial = null;
        $event = null;
        $eventTrails = null;
        $editorialTrails = null;
        
        $trail = $this->getDoctrine()
            ->getRepository('TBFrontendBundle:Route')
            ->findOneBySlug($trailSlug);

        if (!$trail) {
            throw $this->createNotFoundException(
                sprintf('Trail %s not found', $trailSlug)
            );
        }
        
        if ($editorialSlug !== null) {
            $query = $this->getDoctrine()->getManager()
                ->createQuery('
                    SELECT e FROM TBFrontendBundle:Editorial e
                    JOIN e.routes r
                    WHERE e.slug = :editorialSlug
                    AND r.id = :trailId')
                ->setParameter('editorialSlug', $editorialSlug)
                ->setParameter('trailId', $trail->getId());
            try {
                $editorial = $query->getSingleResult();
            } catch (\Doctrine\ORM\NoResultException $e) {
                throw $this->createNotFoundException(
                    sprintf('Editorial %s not found or no relation to Trail %s', $editorialSlug, $trailSlug)
                );
            }
            
            $query = $this->getDoctrine()->getManager()
                ->createQuery('
                    SELECT r FROM TBFrontendBundle:Route r
                    JOIN r.editorials e
                    WHERE e.id=:editorialId
                    AND r.id!=:routeId                    
                    ORDER BY r.id')
                ->setParameter('editorialId', $editorial->getId())
                ->setParameter('routeId', $trail->getId());
            $editorialTrails = $query->getResult();  
        } 
        
        if (count($trail->getEventRoutes()) > 0) {
            $event = $trail->getEventRoutes()[0]->getEvent();
            $query = $this->getDoctrine()->getManager()
                ->createQuery('
                    SELECT r FROM TBFrontendBundle:Route r
                    JOIN r.eventRoutes e
                    WHERE e.eventId=:eventId
                    AND r.id!=:routeId
                    ORDER BY r.id')
                ->setParameter('eventId', $event->getId())
                ->setParameter('routeId', $trail->getId());
            $eventTrails = $query->getResult();  
        } 
        
        // Build the Breadcrumb for three different cases
        if ($editorial !== null) {
            // case 1: editorial is part of the url, add link to the editorial
            $breadcrumb = [[
                'name' => 'editorial',
                'label' => $editorial->getTitle(), 
                'params' => ['slug' => $editorial->getSlug()],
            ],[
                'name' => 'editorial_trail',
                'label' => $trail->getName(), 
                'params' => ['trailSlug' => $trail->getSlug(), 'editorialSlug' => $editorial->getSlug()],
            ]];
        } elseif ($event != null) {
            // case 2: an event is linked to the trail, add link to the event
            $breadcrumb = [[
                'name' => 'event',
                'label' => trim($event->getTitle() . ' ' . $event->getTitle2()), 
                'params' => ['slug' => $event->getSlug()],
            ],[
                'name' => 'trail',
                'label' => $trail->getName(), 
                'params' => ['trailSlug' => $trail->getSlug()],
            ]];
        } else {
            // case 2: none of the above, add link to the profile that created that event
            $breadcrumb = [[
                'name' => 'profile',
                'label' =>  $trail->getUser()->getTitle(), 
                'params' => ['name' => $trail->getUser()->getName()],
            ],[
                'name' => 'trail',
                'label' => $trail->getName(), 
                'params' => ['trailSlug' => $trail->getSlug()],
            ]];
        }
                
        return array(
            'trail' => $trail, 
            'user' => $trail->getUser(), 
            'editorial' => $editorial, 
            'event' => $event,
            'breadcrumb' => $breadcrumb,
            'eventTrails' => $eventTrails,
            'editorialTrails' => $editorialTrails,
        );
    }
}
