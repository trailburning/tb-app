<?php 

namespace TB\Bundle\APIBundle\Tests\Service;

use TB\Bundle\APIBundle\Tests\AbstractApiTest;

class GpxFileImporterTest extends AbstractApiTest
{

    /**
     * 
     */
    public function testParse()
    {
        $this->loadFixtures([]); 
        $importer = $this->getContainer()->get('tb.gpxfile.importer');
        $routes = $importer->parse(file_get_contents(realpath(__DIR__ . '/../../DataFixtures/GPX/example.gpx')));        
        $this->assertEquals(1, count($routes), '1 Route was found in .gpx file');
        $this->assertEquals(2, count($routes[0]->getRoutePoints()), '2 RoutePoints were found');
    }
    
}    