<?php 

namespace TB\Bundle\FrontendBundle\Tests\Entity;

use TB\Bundle\FrontendBundle\Tests\AbstractFrontendTest;
use TB\Bundle\FrontendBundle\Entity\RouteType;

class RouteTypeTest extends AbstractFrontendTest
{
    
    /**
     * Test export of entity
     */
    public function testJsonSerialize()
    {
        $this->loadFixtures(
            ['TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteTypeData']
        );        
        
        // Get RouteType from DB with the name "grunewald"..
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $routeType = $em
            ->getRepository('TBFrontendBundle:RouteType')
            ->findOneByName('Marathon');
        if (!$routeType) {
            $this->fail('Missing RouteType with name "Marathon" in test DB');
        }
        
        $expectedJson = '{
            "id":' . $routeType->getId() . ',
            "name":"Marathon"
        }';
        
        $this->assertJsonStringEqualsJsonString($expectedJson, json_encode($routeType->export()),
            'RouteType::export() returns the expected data array');
    }
}