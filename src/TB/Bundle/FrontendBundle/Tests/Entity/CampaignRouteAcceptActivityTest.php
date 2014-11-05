<?php 

namespace TB\Bundle\FrontendBundle\Tests\Entity;

use TB\Bundle\FrontendBundle\Tests\AbstractFrontendTest;
use TB\Bundle\FrontendBundle\Entity\CampaignRouteAcceptActivity;

class CampaignRouteAcceptActivityTest extends AbstractFrontendTest
{
        
    /**
     * 
     */
    public function testExport()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\CampaignData',
        ]);
            
        $campaign = $this->getCampaign('urbantrails-london');
        $route = $campaign->getCampaignRoutes()[0]->getRoute();
        $user = $route->getUser();
        
        $activity = new CampaignRouteAcceptActivity($user, $route, $campaign);
        
        $expected = [
            'published' => NULL,
            'actor' => [
                'url' => '/profile/mattallbeury',
                'objectType' => 'person',
                'id' => $user->getId(),
                'displayName' => 'Matt Allbeury',
                'image' => [
                    'url' => 'http://assets.trailburning.com/images/profile/mattallbeury/avatar_ma.png',
                ],
            ],
            'verb' => 'accept',
            'object' => [
                'url' => '/campaign/urbantrails-london/trail/london',
                'objectType' => 'campaignTrail',
                'id' => [
                    'campaign_id' => $campaign->getId(),
                    'route_id' => $route->getId(),
                ],
                'displayName' => 'London Bridge to Canary Wharf',
            ],
            'target' => [
                'url' => '/campaign/urbantrails-london',
                'objectType' => 'campaign',
                'id' => $campaign->getId(),
                'displayName' => 'Urban Trails London',
            ],
        ];
        
        $this->assertEquals($expected, $activity->export(),
            'CampaignRouteAcceptActivity::export() returns the expected data array');
    }
    
}