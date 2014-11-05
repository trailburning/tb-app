<?php

namespace TB\Bundle\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class CampaignController extends Controller
{
    
    /**
     * @Route("/urbantrails/london", name="campaign_urbantrails_london_redirect")
     * @Template()
     */
    public function urbantrailsLondonRedirectAction()
    {
        return $this->redirect($this->generateUrl('campaign', ['slug' => 'urbantrails-london']), 301);
    }
    
    /**
     * @Route("/campaign/{slug}", name="campaign")
     * @Template()
     */
    public function campaignAction($slug)
    {
        $breadcrumb = array();
        
        $campaign = $this->getDoctrine()
            ->getRepository('TBFrontendBundle:Campaign')
            ->findOneBySlug($slug);

        if (!$campaign) {
            throw $this->createNotFoundException(
                sprintf('Campaign %s not found', $slug)
            );
        }
        
        $query = $this->getDoctrine()->getManager()
            ->createQuery('
                SELECT r FROM TBFrontendBundle:Route r
                JOIN r.campaignRoutes c
                WHERE c.campaignId=:campaignId
                ORDER BY c.acceptedAt DESC')
            ->setParameter('campaignId', $campaign->getId())
            ->setMaxResults(2);
        $latestRoutes = $query->getResult();
        
        $query = $this->getDoctrine()->getManager()
            ->createQuery('
                SELECT r FROM TBFrontendBundle:Route r
                JOIN r.campaignRoutes c
                WHERE c.campaignId=:campaignId
                ORDER BY r.rating DESC')
            ->setParameter('campaignId', $campaign->getId())
            ->setMaxResults(3);
        $popularRoutes = $query->getResult();
        
        $query = $this->getDoctrine()->getManager()
            ->createQuery('
                SELECT COUNT(r) FROM TBFrontendBundle:Route r
                JOIN r.campaignRoutes c
                WHERE c.campaignId=:campaignId')
            ->setParameter('campaignId', $campaign->getId());
        $totalRoutesCount = $query->getSingleScalarResult();
        
        $breadcrumb[] = [
            'name' => 'campaign',
            'label' => trim($campaign->getTitle()), 
            'params' => ['slug' => $campaign->getSlug()],
        ];
        
        return array(
            'campaign' => $campaign,
            'latestRoutes' => $latestRoutes,
            'popularRoutes' => $popularRoutes,
            'totalRoutesCount' => $totalRoutesCount,
            'breadcrumb' => $breadcrumb,
        );
    }
    
}
