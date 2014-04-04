<?php 

namespace TB\Bundle\APIBundle\Tests\Entity;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use TB\Bundle\FrontendBundle\Entity\RouteType;

class RouteTypeTest extends WebTestCase
{
    
    protected static function getKernelClass()
    {
        require_once self::getPhpUnitXmlDir() . '/frontend/AppKernel.php';

        return 'AppKernel';
    }
    
    /**
     * Test update of entity from JSON object
     */
    public function testToJSON()
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
        
        $this->assertJsonStringEqualsJsonString($expectedJson, $routeType->jsonSerialize(),
            'RouteType::jsonSerialize() returns the expected JSON string');
    }
}