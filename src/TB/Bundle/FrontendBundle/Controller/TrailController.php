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
        $breadcrumb = array();
        $editorial = null;
        $event = null;
        $relatedTrails = null;
        
        $trail = $this->getDoctrine()
            ->getRepository('TBFrontendBundle:Route')
            ->findOneBySlug($trailSlug);

        if (!$trail) {
            throw $this->createNotFoundException(
                sprintf('Trail %s not found', $trailSlug)
            );
        }
        
        if ($trail->getEvent() !== null) {
            $event = $trail->getEvent();
            $query = $this->getDoctrine()->getManager()
                ->createQuery('
                    SELECT r FROM TBFrontendBundle:Route r
                    WHERE r.eventId=:eventId
                    AND r.id!=:trailId
                    ORDER BY r.id')
                ->setParameter('eventId', $event->getId())
                ->setParameter('trailId', $trail->getId());
            $relatedTrails = $query->getResult();
            
            // breadcrumb to event page
            $breadcrumb[] = [
                'name' => 'event',
                'label' => trim($event->getTitle() . ' ' . $event->getTitle2()), 
                'params' => ['slug' => $event->getSlug()],
            ];
        } elseif ($editorialSlug !== null) {
            
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
            
            // breadcrumb to editorail page
            $breadcrumb[] = [
                'name' => 'editorial',
                'label' => $editorial->getName(), 
                'params' => ['slug' => $event->getSlug()],
            ];
        } else {
            // breadcrumb to profile page
            $breadcrumb[] = [
                'name' => 'profile',
                'label' =>  $trail->getUser()->getTitle(), 
                'params' => ['name' => $trail->getUser()->getName()],
            ];
        }
        
        if ($editorialSlug !== null) {
            $breadcrumb[] = [
                'name' => 'editorial_trail',
                'label' => $trail->getName(), 
                'params' => ['trailSlug' => $trail->getSlug(), 'editorialSlug' => $editorial->getSlug()],
            ];
        } else {
            $breadcrumb[] = [
                'name' => 'trail',
                'label' => $trail->getName(), 
                'params' => ['trailSlug' => $trail->getSlug()],
            ];
        }
        
        return array(
            'trail' => $trail, 
            'user' => $trail->getUser(), 
            'editorial' => $editorial, 
            'event' => $event,
            'breadcrumb' => $breadcrumb,
            'relatedTrails' => $relatedTrails,
        );
    }
}
