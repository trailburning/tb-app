<?php 

namespace TB\Bundle\FrontendBundle\Tests\Util;

use TB\Bundle\FrontendBundle\Tests\AbstractFrontendTest;
use TB\Bundle\FrontendBundle\Util\MediaImporter;
use CrEOF\Spatial\PHP\Types\Geometry\Point;

class MediaImporterTest extends AbstractFrontendTest
{
    
    public function testGetGeometryPointFromExifNoGpsImage()
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $mediaImporter = new MediaImporter($em);
        
        $exiftags = exif_read_data(realpath(__DIR__ . '/../../DataFixtures/Media/no_metadata.jpg'));
        $result = $mediaImporter->getGeometryPointFromExif($exiftags);
        $this->assertNull($result, 'getGeometryPointFromExif() returns null for image without GPS metadata');
    }
    
    public function testGetGeometryPointFromExif()
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $mediaImporter = new MediaImporter($em);
        
        $exiftags = exif_read_data(realpath(__DIR__ . '/../../DataFixtures/Media/gps_example/IMG_3255.JPG'));

        $result = $mediaImporter->getGeometryPointFromExif($exiftags);
        $this->assertInstanceOf('CrEOF\Spatial\PHP\Types\Geometry\Point', $result,
            'getGeometryPointFromExif() returns a Point Object');
        $this->assertEquals(144.9545, $result->getLongitude(),
            'Test correct Longitude value');
        $this->assertEquals(-37.8231666667, $result->getLatitude(),
            'Test correct Latitude value');    
    }
    
    /**
     * Test getTimezone()
     */
    public function testGetTimezone()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteData'
        ]);
        
        $mediaImporter = $this->getContainer()->get('tb.media.importer');
        $route = $this->getRoute('grunewald');
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
        
        $mediaImporter = $this->getContainer()->get('tb.media.importer');
        
        $route = $this->getRoute('grunewald');
        
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
        
        $mediaImporter = $this->getContainer()->get('tb.media.importer');
        
        $route = $this->getRoute('grunewald');

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
     * Test getNearestRoutePointByGeoTime()
     */
    public function testGetNearestRoutePointByGeoPoint()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteData',           
        ]);
        
        // Get Route from DB with the slug "grunewald"..
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $mediaImporter = new MediaImporter($em);
        
        $route = $this->getRoute('grunewald');
        
        $point = new Point(13.249617, 52.501565, 4326);
        $routePoint = $mediaImporter->getNearestRoutePointByGeoPoint($route, $point);
        $this->assertInstanceOf('TB\Bundle\FrontendBundle\Entity\RoutePoint', $routePoint, 
            'MediaImporter::getNearestRoutePointByGeoTime() returns a RoutePoint object');            
        $this->assertEquals(69, $routePoint->getPointNumber(), 
            'MediaImporter::getNearestRoutePointByGeoTime() returns the expected RoutePoint object');
            
        $point = new Point(13.192097, 52.478326, 4326);
        $routePoint = $mediaImporter->getNearestRoutePointByGeoPoint($route, $point);
        $this->assertEquals(616, $routePoint->getPointNumber(), 
            'MediaImporter::getNearestRoutePointByGeoTime() returns the expected RoutePoint object');
    }
    
    
    /**
     * Test getFirstRoutePoint()
     */
    public function testGetFirstRoutePoint()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteData',           
        ]);
        
        $mediaImporter = $this->getContainer()->get('tb.media.importer');
        
        $route = $this->getRoute('grunewald');

        $routePoint = $mediaImporter->getFirstRoutePoint($route);
        $this->assertInstanceOf('TB\Bundle\FrontendBundle\Entity\RoutePoint', $routePoint, 
            'MediaImporter::getFirstRoutePoint() returns a RoutePoint object');
        $this->assertEquals(1, $routePoint->getPointNumber(), 
            'MediaImporter::getFirstRoutePoint() returns the expected RoutePoint object');
    }
}