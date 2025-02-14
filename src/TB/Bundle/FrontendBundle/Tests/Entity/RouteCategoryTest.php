<?php 

namespace TB\Bundle\FrontendBundle\Tests\Entity;

use TB\Bundle\FrontendBundle\Tests\AbstractFrontendTest;
use TB\Bundle\FrontendBundle\Entity\RouteType;

class RouteCategoryTest extends AbstractFrontendTest
{
    
    /**
     * Test export entity
     */
    public function testExport()
    {
        $this->loadFixtures(
            ['TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteCategoryData']
        );        
        
        // Get RouteCategory from DB with the name "grunewald"..
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $routeCategory = $em
            ->getRepository('TBFrontendBundle:RouteCategory')
            ->findOneByName('Park');
        if (!$routeCategory) {
            $this->fail('Missing RouteType with name "Park" in test DB');
        }
        
        $expectedJson = '{
            "id":' . $routeCategory->getId() . ',
            "name":"Park"
        }';
        
        $this->assertJsonStringEqualsJsonString($expectedJson, json_encode($routeCategory->export()),
            'RouteCategory::export() returns the expected data array');
    }
}