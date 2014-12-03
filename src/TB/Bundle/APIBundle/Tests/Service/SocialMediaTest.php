<?php 

namespace TB\Bundle\ApiBundle\Tests\Service;

use TB\Bundle\APIBundle\Tests\AbstractApiTest;

class SocialMediaTest extends AbstractApiTest
{
    
    public function testSearch() 
    {
        $socialMedia = $this->getContainer()->get('tb.socialmedia');
        
        $result = $socialMedia->search('trailburning');

        $this->assertInternalType('array', $result);
        $this->assertEquals(3, count($result));
    }
}    