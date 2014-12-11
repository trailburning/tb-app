<?php 

namespace TB\Bundle\FrontendBundle\Tests\Service;

use TB\Bundle\FrontendBundle\Tests\AbstractFrontendTest;
use TB\Bundle\FrontendBundle\Service\Timezone;


class TimezoneTest extends AbstractFrontendTest
{
    
    /**
     * Test getTimezone()
     */
    public function testGetTimezoneForGeoPoint()
    {   
        $timezone = new Timezone($this->getContainer()->get('http_client'));
        
        $result = $timezone->getTimezoneForGeoPoint(13.257437, 52.508006, 1376728877);
        $this->assertEquals('Europe/Berlin', $result, 'Route::getTimezone() return the correct timezone "Europe/Berlin"');
    }
    
}