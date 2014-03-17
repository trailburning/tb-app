<?php 

namespace TB\Bundle\FrontendBundle\Tests\Entity;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use TB\Bundle\FrontendBundle\Util\MediaImporter;

class MediaImporterTest extends WebTestCase
{
    
    protected static function getKernelClass()
    {
        require_once self::getPhpUnitXmlDir() . '/frontend/AppKernel.php';

        return 'AppKernel';
    }
    
    /**
     * Test getTimezone()
     */
    public function testGetTimezone()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteData'
        ]);
        
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $mediaImporter = new MediaImporter($em);
        
        // Get Route from DB with the slug "grunewald"..
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $route = $em
            ->getRepository('TBFrontendBundle:Route')
            ->findOneBySlug('grunewald');
        if (!$route) {
            $this->fail('Missing Route with slug "grunewald" in test DB');
        }        

        $timezone = $mediaImporter->getRouteTimezone($route);
        
        $this->assertEquals('Europe/Berlin', $timezone, 'Route::getTimezone() return the correct timezone "Europe/Berlin"');
    }
    
    
    /**
     * Test getTimezoneOffset();
     */
    public function testGetTimezoneOffset()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteData',           
        ]);
        
        // Get Route from DB with the slug "grunewald"..
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $mediaImporter = new MediaImporter($em);
        
        $route = $em
            ->getRepository('TBFrontendBundle:Route')
            ->findOneBySlug('grunewald');
        if (!$route) {
            $this->fail('Missing Route with slug "grunewald" in test DB');
        }
        
        $this->assertEquals(7200, $mediaImporter->getRouteTimezoneOffset($route), 
            'The datetimezone offset from this Route to UTC is 7200');
    }
    
    /**
     * Test getNearestRoutePointByTime()
     */
    public function testGetNearestRoutePointByTime()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteData',           
        ]);
        
        // Get Route from DB with the slug "grunewald"..
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $mediaImporter = new MediaImporter($em);
        
        $route = $em
            ->getRepository('TBFrontendBundle:Route')
            ->findOneBySlug('grunewald');
        if (!$route) {
            $this->fail('Missing Route with slug "grunewald" in test DB');
        }

        $routePoint = $mediaImporter->getNearestRoutePointByTime($route, 1376731062);
        $this->assertInstanceOf('TB\Bundle\FrontendBundle\Entity\RoutePoint', $routePoint, 
            'MediaImporter::getNearestRoutePointByTime() returns a RoutePoint object');
        $this->assertEquals(427, $routePoint->getPointNumber(), 
            'MediaImporter::getNearestRoutePointByTime() returns the expected RoutePoint object');
        
        $routePoint = $mediaImporter->getNearestRoutePointByTime($route, 1376730897);
        $this->assertEquals(394, $routePoint->getPointNumber(), 
            'MediaImporter::getNearestRoutePointByTime() returns the expected RoutePoint object');
            
        $routePoint = $mediaImporter->getNearestRoutePointByTime($route, 1376731050);
        $this->assertEquals(427, $routePoint->getPointNumber(), 
            'MediaImporter::getNearestRoutePointByTime() returns the expected RoutePoint object');
    }
    
    
    /**
     * Test getFirstRoutePoint()
     */
    public function testGetFirstRoutePoint()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteData',           
        ]);
        
        // Get Route from DB with the slug "grunewald"..
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $mediaImporter = new MediaImporter($em);
        
        $route = $em
            ->getRepository('TBFrontendBundle:Route')
            ->findOneBySlug('grunewald');
        if (!$route) {
            $this->fail('Missing Route with slug "grunewald" in test DB');
        }

        $routePoint = $mediaImporter->getFirstRoutePoint($route);
        $this->assertInstanceOf('TB\Bundle\FrontendBundle\Entity\RoutePoint', $routePoint, 
            'MediaImporter::getFirstRoutePoint() returns a RoutePoint object');
        $this->assertEquals(1, $routePoint->getPointNumber(), 
            'MediaImporter::getFirstRoutePoint() returns the expected RoutePoint object');
    }
}