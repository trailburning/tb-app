<?php 

namespace TB\Bundle\FrontendBundle\Tests\Entity;

use TB\Bundle\FrontendBundle\Tests\AbstractFrontendTest;

class CampaignRouteTest extends AbstractFrontendTest
{
        
    /**
     * 
     */
    public function testExportAsActivity()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\CampaignData',
        ]);
            
        $campaign = $this->getCampaign('urbantrails-london');
        $campaignRoute = $campaign->getCampaignRoutes()[0];
        
        $expected = [
            'url' => '/campaign/urbantrails-london/trail/ttm',
            'objectType' => 'campaignTrail',
            'id' => ['campaign_id' => $campaignRoute->getCampaignId(), 'route_id' => $campaignRoute->getRouteId()],
            'displayName' => 'Thames Trail Marathon',
        ];
        
        $this->assertEquals($expected, $campaignRoute->exportAsActivity(),
            'CampaignRoute::exportAsActivity() returns the expected data array');
    }
    
}