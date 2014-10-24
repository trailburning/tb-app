<?php 

namespace TB\Bundle\FrontendBundle\Tests\Entity;

use TB\Bundle\FrontendBundle\Tests\AbstractFrontendTest;
use TB\Bundle\FrontendBundle\Entity\Campaign;
use TB\Bundle\FrontendBundle\Entity\CampaignGroup;

class CampaignTest extends AbstractFrontendTest
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
        
        $expected = [
            'url' => '/campaign/urbantrails-london',
            'objectType' => 'campaign',
            'id' => $campaign->getId(),
            'displayName' => 'Urban Trails London',
        ];
        
        $this->assertEquals($expected, $campaign->exportAsActivity(),
            'Campaign::exportAsActivity() returns the expected data array');
    }
    
}