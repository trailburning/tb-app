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
                'url' => '/profile/ashmei',
                'objectType' => 'brand',
                'id' => $user->getId(),
                'displayName' => 'ashmei',
                'image' => [
                    'url' => 'https://s3-eu-west-1.amazonaws.com/trailburning-assets/images/profile/ashmei/avatar.jpg',
                ],
            ],
            'verb' => 'accept',
            'object' => [
                'url' => '/campaign/urbantrails-london/trail/ttm',
                'objectType' => 'campaignTrail',
                'id' => [
                    'campaign_id' => $campaign->getId(),
                    'route_id' => $route->getId(),
                ],
                'displayName' => 'Thames Trail Marathon',
            ],
            'target' => [
                'url' => '/campaign/urbantrails-london',
                'objectType' => 'campaign',
                'id' => $campaign->getId(),
                'displayName' => 'London',
            ],
        ];
        
        $this->assertEquals($expected, $activity->export(),
            'CampaignRouteAcceptActivity::export() returns the expected data array');
    }
    
}