<?php 

namespace TB\Bundle\FrontendBundle\Tests\Entity;

use TB\Bundle\FrontendBundle\Tests\AbstractFrontendTest;
use TB\Bundle\FrontendBundle\Entity\RouteType;

class RegionTest extends AbstractFrontendTest
{
    
    /**
     * Test export entity
     */
    public function testExport()
    {
        $this->loadFixtures(
            ['TB\Bundle\FrontendBundle\DataFixtures\ORM\RegionData']
        );        
        
        // Get RouteCategory from DB with the name "grunewald"..
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $region = $em
            ->getRepository('TBFrontendBundle:Region')
            ->findOneBySlug('grindelwald');
        if (!$region) {
            $this->fail('Missing Region with slug "grindelwald" in test DB');
        }
        
        $expectedJson = '{
            "about": "The lively holiday resort at the foot of the Eiger is closely linked with mountaineering. Time and time again top alpinists have continued to cause a sensation in the Eiger north face, but Grindelwald also offers a variety of possibilities for those seeking active recovery. \n\nGrindelwald has something to offer everyone. The village is lively, sporty and active, but as a contrast also offers secluded, idyllic corners to savour and linger.", 
            "id": ' . $region->getId() . ', 
            "image": "grindelwald.jpg", 
            "link": "http://www.grindelwald.ch", 
            "logo": "logo_grindelwald.png", 
            "name": "Grindelwald | Switzerland", 
            "slug": "grindelwald"
        }';
        
        $this->assertJsonStringEqualsJsonString($expectedJson, json_encode($region->export()),
            'Region::export() returns the expected data array');
    }
}