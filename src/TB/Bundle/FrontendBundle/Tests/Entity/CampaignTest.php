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
    
    public function testExport()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\CampaignData',
        ]);
            
        $campaign = $this->getCampaign('urbantrails-london');
        
        $expected = [
            'id' => $campaign->getId(),
            'title' => 'Urban Trails London',
            'slug' => 'urbantrails-london',
            'logo' => 'images/campaign/urbantrails-london/logo_urbantrails_london.png',
            'image' => 'images/campaign/urbantrails-london/image.jpg',
            'text' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut feugiat accumsan leo eget porttitor. Donec blandit, dui nec aliquam mattis, leo augue adipiscing purus, quis iaculis eros purus ac nisi.',
            'synopsis' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut feugiat accumsan leo eget porttitor. Donec blandit, dui nec aliquam mattis, leo augue adipiscing purus, quis iaculis eros purus ac nisi. Etiam neque magna, consectetur eget suscipit vitae, convallis a metus.',
        ];
        
        $this->assertEquals($expected, $campaign->export(),
            'Campaign::export() returns the expected data array');
    }
    
}