<?php

namespace TB\Bundle\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
     * @Route("/editorial/{editorialSlug}/trail/{trailSlug}")    
     */
    public function legacyEditorialTrailAction($trailSlug, $editorialSlug)
    { 
        return $this->redirect($this->generateUrl('editorial_trail', ['trailSlug' => $trailSlug, 'editorialSlug' => $editorialSlug]), 301);
    }
    
    /**
     * @Route("/trail/{id}", requirements={"id" = "\d+"}, name="trail_id")
     */
    public function trailIdAction($id)
    {
        $query = $this->getDoctrine()->getManager()
            ->createQuery('
                SELECT r FROM TBFrontendBundle:Route r
                WHERE r.slug != :slug 
                AND r.id = :trailId')
            ->setParameter('slug', '')    
            ->setParameter('trailId', $id);
        try {
            $trail = $query->getSingleResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            throw $this->createNotFoundException(
                sprintf('Trail not found')
            );
        }
       
        return $this->redirect($this->generateUrl('trail', ['trailSlug' => $trail->getSlug()]), 301);
    }
    
    /**
     * @Route("/trail/{trailSlug}.gpx", name="trail_gpx")
     */
    public function trailGPXAction($trailSlug)
    {   
        $gpxGenerator = $this->container->get('tb.gpx_generator');
        
        $trail = $this->getDoctrine()
            ->getRepository('TBFrontendBundle:Route')
            ->findOneBySlug($trailSlug);

        if (!$trail) {
            throw $this->createNotFoundException(
                sprintf('Trail %s not found', $trailSlug)
            );
        }
        
        $xml = $gpxGenerator->generateXML($trail);
        $response = new Response($xml);
        $response->headers->set('Content-Type', 'application/gpx+xml');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s.gpx"', $trail->getSlug()));
        $response->setStatusCode(200);
        
        return $response;
    }
    
    /**
     * @Route("/trail/{trailSlug}", name="trail")
     * @Route("/inspire/{editorialSlug}/trail/{trailSlug}", name="editorial_trail")
     * @Route("/campaign/{campaignSlug}/trail/{trailSlug}", name="campaign_trail")
     * @Template()
     */
    public function trailAction($trailSlug, $editorialSlug = null, $campaignSlug = null)
    {   
        $editorial = null;
        $event = null;
        $campaign = null;
        $campaigns = null;
        $eventTrails = null;
        $editorialTrails = null;
        $relatedTrails = null;
        
        $trail = $this->getDoctrine()
            ->getRepository('TBFrontendBundle:Route')
            ->findOneBySlug($trailSlug);

        if (!$trail) {
            throw $this->createNotFoundException(
                sprintf('Trail %s not found', $trailSlug)
            );
        }
        
        if ($campaignSlug !== null) {
            // Get the one Campaign that is referenced in the URL
            $query = $this->getDoctrine()->getManager()
                ->createQuery('
                    SELECT c FROM TBFrontendBundle:Campaign c
                    JOIN c.campaignRoutes r
                    WHERE c.slug = :campaignSlug
                    AND r.routeId = :routeId')
                ->setParameter('campaignSlug', $campaignSlug)
                ->setParameter('routeId', $trail->getId());
            try {
                $campaign = $query->getSingleResult();
            } catch (\Doctrine\ORM\NoResultException $e) {
                throw $this->createNotFoundException(
                    sprintf('Campaign %s not found or no relation to Trail %s', $campaignSlug, $trailSlug)
                );
            }
        } else {
            // Get all Campaigns that this Route is part of
            $campaigns = $this->getDoctrine()->getManager()
                ->createQuery('
                    SELECT c FROM TBFrontendBundle:Campaign c
                    JOIN c.campaignRoutes r
                    WHERE r.routeId = :routeId')
                ->setParameter('routeId', $trail->getId())
                ->getResult();
        }
        
        if ($editorialSlug !== null) {
            $query = $this->getDoctrine()->getManager()
                ->createQuery('
                    SELECT e FROM TBFrontendBundle:Editorial e
                    JOIN e.editorialRoutes er
                    WHERE e.slug = :editorialSlug
                    AND er.routeId = :routeId')
                ->setParameter('editorialSlug', $editorialSlug)
                ->setParameter('routeId', $trail->getId());
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
                    JOIN r.editorialRoutes er
                    WHERE er.editorialId=:editorialId
                    AND r.id!=:routeId                    
                    ORDER BY r.id')
                ->setParameter('editorialId', $editorial->getId())
                ->setParameter('routeId', $trail->getId());
            $editorialTrails = $query->getResult();  
        } else {
            $postgis = $this->get('postgis');
            $relatedTrails = $postgis->relatedRoutes($trail->getId());
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
        
        // User who like this trail
        $routeLikes = $this->getDoctrine()->getManager()
            ->createQuery('
                SELECT u FROM TBFrontendBundle:User u
                JOIN u.routeLikes rl
                WHERE rl.routeId=:routeId
                ORDER BY rl.date DESC')
            ->setParameter('routeId', $trail->getId())
            ->setMaxResults(20)
            ->getResult();
        
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
        } elseif ($campaign != null) {
            // case 3: a campaign is linked to the trail, add link to the campaign
            if ($campaign->getCampaignGroup()) {
                $title = sprintf('%s %s', $campaign->getCampaignGroup()->getName(), $campaign->getTitle());
            } else {
                $title = $campaign->getTitle();
            }   
            
            $breadcrumb = [[
                'name' => 'campaign',
                'label' => $title, 
                'params' => ['slug' => $campaign->getSlug()],
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
                
        return [
            'trail' => $trail, 
            'user' => $trail->getUser(), 
            'editorial' => $editorial, 
            'event' => $event,
            'campaign' => $campaign,
            'campaigns' => $campaigns,
            'breadcrumb' => $breadcrumb,
            'eventTrails' => $eventTrails,
            'editorialTrails' => $editorialTrails,
            'relatedTrails' => $relatedTrails,
            'routeLikes' => $routeLikes,
        ];
    }

    /**
     * @Route("/trailmaker/{id}", requirements={"id" = "\d+"}, defaults={"id" = 0}, name="trailmaker")
     * @Template()
     */    
    public function trailmakerAction($id)
    {
        // when editing a trail, check if trail exists and if the current user is allowed to edit the trail
        if ($id !== 0) {
            $route = $this->getRouteAndCheckAccess($id);
        }
        
        return [
            'id' => $id,
        ];
    }
    
    /**
     * @Route("/trailmaker/{id}/publish", requirements={"id" = "\d+"}, name="publish_trail")
     * @Template()
     */    
    public function publishTrailAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $route = $this->getRouteAndCheckAccess($id);
        $route->setPublish(true);
        $em->persist($route);
        $em->flush();
        
        return $this->redirect($this->generateUrl('profile', ['name' => $this->getUser()->getName()]), 301);
    }
    
    /**
     * @Route("/trailmaker/{id}/unpublish", requirements={"id" = "\d+"}, name="unpublish_trail")
     * @Template()
     */    
    public function unpublishTrailAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $route = $this->getRouteAndCheckAccess($id);
        $route->setPublish(false);
        $em->persist($route);
        $em->flush();
        
        return $this->redirect($this->generateUrl('profile', ['name' => $this->getUser()->getName()]), 301);
    }
    
    /**
     * @throws NotFoundHttpException when the Route does not exist
     * @throws AccessDeniedException when the user has no right to edit this Route
     * @return Route
     */
    protected function getRouteAndCheckAccess($id)
    {
        
        $route = $this->getDoctrine()
            ->getRepository('TBFrontendBundle:Route')
            ->findOneById($id);

        if (!$route) {
            throw $this->createNotFoundException(
                sprintf('Route not found')
            );
        }
        
        // User with ROLE_ADMIN is allowed to edit every Route
        // if ($this->securityContext->isGranted('ROLE_ADMIN')) ) {
        //     
        //     return $route;
        // }
        
        // All other user are only allowed to edit their own Routes
        if ($route->getUserId() != $this->getUser()->getId()) {
            throw new AccessDeniedException('You are not allowed to edit this trail');
        }
        
        return $route;
    }
    
    /**
     * @Route("/trails", name="trails")
     * @Template()
     */    
    public function trailsAction(Request $request)
    {
        
        return [];
    }
    
    /**
     * @Template()
     */    
    public function homepageTrailsAction()
    {
        $query = $this->getDoctrine()->getManager()
            ->createQuery('
                SELECT r FROM TBFrontendBundle:Route r
                WHERE r.publish = true AND r.approved = true
                ORDER BY r.publishedDate DESC')
            ->setMaxResults(3);
        $trails = $query->getResult();  
        
        return [
            'trails' => $trails,
        ];
    }
    
    /**
     * @Template()
     */    
    public function homepageUserTrailsAction()
    {
        if (!$this->getUser()) {
            throw new \Exception('For this action the user is required to login');
        }
        $trails = [];
        $followingIds = [];
        foreach ($this->getUser()->getUserIFollow() as $userIFollow) {
            $followingIds[] = $userIFollow->getId();
        }    
        
        if (count($followingIds) > 0) {
            $query = $this->getDoctrine()->getManager()
                ->createQuery('
                    SELECT r FROM TBFrontendBundle:Route r
                    WHERE r.publish = true
                    AND r.userId IN (:following)
                    ORDER BY r.publishedDate DESC')
                ->setParameter('following', $followingIds);
            $trails = $query->setMaxResults(3)->getResult();  
        }
        
        return [
            'trails' => $trails,
        ];
    }
    
    /**
     * @Route("/map/trails", name="map_trails")
     * @Route("/map/trails/trail/{routeSlug}", name="map_trails_trail")
     * @Route("/map/trails/region/{regionSlug}", name="map_trails_region")
     * @Template()
     */    
    public function mapTrailsAction($routeSlug = null, $regionSlug = null)
    {
        $route = null;
        if ($routeSlug) {
            $route = $this->getDoctrine()
                ->getRepository('TBFrontendBundle:Route')
                ->findOneBySlug($routeSlug);
            
            if (!$route) {
                throw $this->createNotFoundException(
                    sprintf('Trail %s not found', $routeSlug)
                );
            }
        }
        
        $region = null;
        if ($regionSlug) {
            $region = $this->getDoctrine()
                ->getRepository('TBFrontendBundle:Region')
                ->findOneBySlug($regionSlug);
            
            if (!$region) {
                throw $this->createNotFoundException(
                    sprintf('Region %s not found', $regionSlug)
                );
            }
        }
        
        return [
            'route' => $route,
            'region' => $region,
        ];
    }
}
