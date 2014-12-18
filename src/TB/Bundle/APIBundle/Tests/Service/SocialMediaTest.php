<?php 

namespace TB\Bundle\APIBundle\Tests\Service;

use TB\Bundle\APIBundle\Tests\AbstractApiTest;

class SocialMediaTest extends AbstractApiTest
{
    
    public function testSearch()
    {
        $socialMedia = $this->getContainer()->get('tb.socialmedia');

        $results = $socialMedia->search('6amclub', 4);

        $this->assertInternalType('array', $results);
        $this->assertEquals(4, count($results));
    }
    
    public function testTimeline() 
    {
        $socialMedia = $this->getContainer()->get('tb.socialmedia');
        
        $results = $socialMedia->timeline('trailburning', 4);

        $this->assertInternalType('array', $results);
        $this->assertEquals(4, count($results));
    }
}    