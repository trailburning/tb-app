<?php

namespace TB\Bundle\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class CampaignController extends Controller
{
    
    
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
        
        $breadcrumb[] = [
            'name' => 'campaign',
            'label' => trim($campaign->getTitle()), 
            'params' => ['slug' => $campaign->getSlug()],
        ];
        
        return array(
            'campaign' => $campaign,
            'breadcrumb' => $breadcrumb,
        );
    }
    
}
