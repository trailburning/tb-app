<?php 

namespace TB\Bundle\APIBundle\Tests\Entity;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use TB\Bundle\FrontendBundle\Entity\RouteType;

class RouteCategoryTest extends WebTestCase
{
    
    protected static function getKernelClass()
    {
        require_once self::getPhpUnitXmlDir() . '/frontend/AppKernel.php';

        return 'AppKernel';
    }
    
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