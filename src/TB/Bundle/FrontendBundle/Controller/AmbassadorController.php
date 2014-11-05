<?php

namespace TB\Bundle\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class AmbassadorController extends Controller
{
    
    /**
     * @Route("/ambassadors", name="ambassadors")
     * @Template()
     */
    public function ambassadorsAction()
    {
        $breadcrumb = array();
        
        $query = $this->getDoctrine()->getManager()
            ->createQuery('
                SELECT u FROM TBFrontendBundle:UserProfile u
                WHERE u.isAmbassador=true
                ORDER BY r.id')
            ->setParameter('campaignId', $campaign->getId());
        $ambassadors = $query->getResult();
        
        foreach ($ambassadors as $ambassador) {
            $query = $this->getDoctrine()->getManager()
                ->createQuery('
                    SELECT r FROM TBFrontendBundle:Route r
                    WHERE r.publish=true AND r.approved=true AND r.userId=:userId
                    ORDER BY r.publishedDate DESC')
                ->setParameter('userId', $ambassador->getId());
            $latestRoute = $query->getOneOrNullResult();
            if ($latestRoute) {
                $ambassador->addRoute($latestRoute);
            }
        }
        
        $breadcrumb[] = [
            'name' => 'campaign',
            'label' => trim($campaign->getTitle()), 
            'params' => ['slug' => $campaign->getSlug()],
        ];
        
        return array(
            'campaign' => $campaign,
            'routes' => $routes,
            'breadcrumb' => $breadcrumb,
        );
    }
    
}
