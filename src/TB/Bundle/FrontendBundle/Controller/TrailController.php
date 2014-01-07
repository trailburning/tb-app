<?php

namespace TB\Bundle\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class TrailController extends Controller
{
    /**
     * @Route("/trail/{trailSlug}", name="trail")
     * @Route("/editorial/{editorialSlug}/trail/{trailSlug}", name="editorial_trail")
     * @Route("/event/{eventSlug}/trail/{trailSlug}", name="event_trail")
     * @Template()
     */
    public function trailAction($trailSlug, $editorialSlug = null, $eventSlug = null)
    {   
        $breadcrumb = array();
        $editorial = null;
        $event = null;
        
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
            
            // breadcrumb to editorail page
            $breadcrumb[] = [
                'name' => 'editorial',
                'label' => $editorial->getName(), 
                'params' => ['slug' => $event->getSlug()],
            ];
        } elseif ($eventSlug !== null) {
            
            $query = $this->getDoctrine()->getManager()
                ->createQuery('
                    SELECT e FROM TBFrontendBundle:Event e
                    JOIN e.routes r
                    WHERE e.slug = :eventSlug
                    AND r.id = :trailId')
                ->setParameter('eventSlug', $eventSlug)
                ->setParameter('trailId', $trail->getId());
            try {
                $event = $query->getSingleResult();
            } catch (\Doctrine\ORM\NoResultException $e) {
                throw $this->createNotFoundException(
                    sprintf('Event %s not found or no relation to Trail %s', $eventSlug, $trailSlug)
                );
            }
            
            // breadcrumb to event page
            $breadcrumb[] = [
                'name' => 'event',
                'label' => trim($event->getTitle() . ' ' . $event->getTitle2()), 
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
        } elseif ($eventSlug !== null) {
            $breadcrumb[] = [
                'name' => 'event_trail',
                'label' => $trail->getName(), 
                'params' => ['trailSlug' => $trail->getSlug(), 'eventSlug' => $event->getSlug()],
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
        );
    }
}
