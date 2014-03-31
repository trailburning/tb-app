<?php 

namespace TB\Bundle\APIBundle\Tests\Entity;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use TB\Bundle\FrontendBundle\Entity\Route;
use TB\Bundle\FrontendBundle\Entity\GpxFile;
use CrEOF\Spatial\PHP\Types\Geometry\Point;

class RouteTest extends WebTestCase
{
    
    protected static function getKernelClass()
    {
        require_once self::getPhpUnitXmlDir() . '/frontend/AppKernel.php';

        return 'AppKernel';
    }
    
    public function testToJSON()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteData',
        ]); 

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $route = $em
            ->getRepository('TBFrontendBundle:Route')
            ->findOneBySlug('grunewald');
        
        if (!$route) {
            $this->fail('Missing Route with slug "grunewald" in test DB');
        }
        
        $json = $route->toJSON();
        
        $obj = json_decode($json);
        if ($obj === null) {
            $this->fail('Route::toJSON() returns invalid JSON');
        }
        
        $this->assertObjectHasAttribute('id', $obj, 
            'JSON object contrains attribute "id"');
        $this->assertObjectHasAttribute('name', $obj, 
            'JSON object contrains attribute "name"');
        $this->assertObjectHasAttribute('slug', $obj, 
            'JSON object contrains attribute "slug"');
        $this->assertObjectHasAttribute('region', $obj, 
            'JSON object contrains attribute "region"');
        $this->assertObjectHasAttribute('length', $obj, 
            'JSON object contrains attribute "length"');
        $this->assertObjectHasAttribute('about', $obj, 
            'JSON object contrains attribute "about"');
        $this->assertObjectHasAttribute('centroid', $obj, 
            'JSON object contrains attribute "centroid"');
        $this->assertObjectHasAttribute('type', $obj, 
            'JSON object contrains attribute "type"');
        $this->assertObjectHasAttribute('category', $obj, 
            'JSON object contrains attribute "category"');
        $this->assertObjectHasAttribute('tags', $obj, 
            'JSON object contrains attribute "tags"');
        $this->assertObjectHasAttribute('route_points', $obj, 
            'JSON object contrains attribute "route_points"');
    }
    
    /**
     * Test update of entity from JSON object
     */
    public function testUpdateFromJSON()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteData',
        ]);        
        
        // get Route to update
        // Get Route from DB with the slug "grunewald"..
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $route = $em
            ->getRepository('TBFrontendBundle:Route')
            ->findOneBySlug('grunewald');
        if (!$route) {
            $this->fail('Missing Route with slug "grunewald" in test DB');
        }
        
        // Get RouteType for Ttst
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $routeType = $em
            ->getRepository('TBFrontendBundle:RouteType')
            ->findOneByName('Ultra Marathon');
        if (!$routeType) {
            $this->fail('Missing RouteType with name "Ultra Marathon" in test DB');
        }
        
        // Get RouteCategory for Ttst
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $routeCategory = $em
            ->getRepository('TBFrontendBundle:RouteCategory')
            ->findOneByName('Mountain');
        
        if (!$route) {
            $this->fail('Missing RouteCategory with name "Ultra Marathon" in test DB');
        }
        
        $obj = new \stdClass();
        $obj->name = 'updated name';
        $obj->region = 'updated region';
        $obj->about = 'updated about';
        $obj->publish = false;
        $obj->route_type_id =  $routeType->getId();
        $obj->route_category_id = $routeCategory->getId();
        
        $route->updateFromJSON(json_encode($obj));
        
        $em->persist($route);
        $em->flush();
        
        $this->assertEquals('updated name', $route->getName());
        $this->assertEquals('updated region', $route->getRegion());
        $this->assertEquals('updated about', $route->getAbout());
        $this->assertEquals(false, $route->getPublish());
        $this->assertEquals($routeType->getId(), $route->getRouteTypeId());
        $this->assertEquals($routeCategory->getId(), $route->getRouteCategoryId());
    }
    
    /**
     * Test that an ApiException is thrown for invalid JSON object 
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Invalid JSON data
     */
    public function testUpdateFromInvalidJSON()
    {
        $route = new Route();
        $route->updateFromJSON('invalid JSON string');
    }
    
    public function testSlug()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\UserProfileData',
        ]); 
 
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $user = $em
            ->getRepository('TBFrontendBundle:User')
            ->findOneByName('mattallbeury');
        
        if (!$user) {
            $this->fail('Missing User with name "mattallbeury" in test DB');
        }
        
        $gpxFile = new GpxFile();
        $gpxFile->setPath('path');
        
        $em->persist($gpxFile);
        $em->flush();
 
        $route = new Route();
        $route->setUser($user);
        $route->setName('name');
        $route->setRegion('region');
        $route->setGpxFile($gpxFile);
        
        $em->persist($route);
        $em->flush();
        
        $this->assertEquals('name-region', $route->getSlug(), 
            'The slug field was set with the value from name and region');
    }
 
    /**
     * @expectedException Exception
     * @@expectedExceptionMessage Before publishing a Route, the name field must be set
     */
    public function testSetPublishThrowsException()
    {
        $route = new Route();
        $route->setPublish(true);
    }
    
    /**
     */
    public function testSetPublish()
    {
        $route = new Route();
        $route->setName('name');
        $route->setPublish(true);
        
        $this->assertTrue($route->getPublish(),
            'publish was set to "true"');
    }
    
}