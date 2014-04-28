<?php 

namespace TB\Bundle\FrontendBundle\Tests\Util;

use TB\Bundle\FrontendBundle\Tests\AbstractFrontendTest;

class EventsFinderTest extends AbstractFrontendTest
{
    
    public function testSearch()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\EventData',
        ]);
        
        $finder = $this->getContainer()->get('tb.events.finder');

        $result = $finder->search(1, 0, $count);
        
        $this->assertEquals(2, $count, 'The $count was set');
        $this->assertEquals(1, count($result), 'The count of returned results is 1');
        $this->assertInternalType('array', $result, 'The result is an array');
    }
    
}