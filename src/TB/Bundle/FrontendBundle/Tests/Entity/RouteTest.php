<?php 

namespace TB\Bundle\FrontendBundle\Tests\Entity;

use TB\Bundle\FrontendBundle\Tests\AbstractFrontendTest;
use TB\Bundle\FrontendBundle\Entity\Route;
use TB\Bundle\FrontendBundle\Entity\Media;
use TB\Bundle\FrontendBundle\Entity\GpxFile;
use TB\Bundle\FrontendBundle\Entity\RouteLike;
use CrEOF\Spatial\PHP\Types\Geometry\Point;

class RouteTest extends AbstractFrontendTest
{
    
    /**
     * Test export of entity
     */
    public function testExport()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteData',
        ]); 

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $route = $this->getRoute('grunewald');
        
        $routeCategory = $em
            ->getRepository('TBFrontendBundle:RouteCategory')
            ->findOneByName('Park');
        if (!$routeCategory) {
            $this->fail('Missing RouteCategory with name "Park" in test DB');
        }
        
        $routeType = $em
            ->getRepository('TBFrontendBundle:RouteType')
            ->findOneByName('Marathon');
        if (!$routeType) {
            $this->fail('Missing RouteType with name "Marathon" in test DB');
        }
        
        $attribute = $this->getAttribute('run', 'activity');
        
        $expectedJson = '{
            "about": "The Grunewald is a forest located in the western side of Berlin on the east side of the river Havel.", 
            "attributes": [
                {
                    "id": ' . $attribute->getId() . ', 
                    "name": "run", 
                    "type": "activity"
                }
            ], 
            "category": {
                "id": ' . $routeCategory->getId() . ', 
                "name": "Park"
            }, 
            "centroid": [
                13.221316,
                52.489695
            ],
            "id": ' . $route->getId() . ', 
            "length": 11298, 
            "name": "Grunewald", 
            "region": "Berlin", 
            "route_points": [
                {
                    "coords": [
                        13.196279, 
                        52.477955
                    ], 
                    "tags": {
                        "altitude": 76.1, 
                        "datetime": 1376732751
                    }
                }, 
                {
                    "coords": [
                        13.196559, 
                        52.477397
                    ], 
                    "tags": {
                        "altitude": 74, 
                        "datetime": 1376732687
                    }
                }, 
                {
                    "coords": [
                        13.196252, 
                        52.471298
                    ], 
                    "tags": {
                        "altitude": 31.6, 
                        "datetime": 1376732355
                    }
                }, 
                {
                    "coords": [
                        13.192097, 
                        52.478326
                    ], 
                    "tags": {
                        "altitude": 30.7, 
                        "datetime": 1376732022
                    }
                }, 
                {
                    "coords": [
                        13.193966, 
                        52.485072
                    ], 
                    "tags": {
                        "altitude": 31.2, 
                        "datetime": 1376731754
                    }
                }, 
                {
                    "coords": [
                        13.203118, 
                        52.491101
                    ], 
                    "tags": {
                        "altitude": 61.2, 
                        "datetime": 1376731319
                    }
                }, 
                {
                    "coords": [
                        13.213987, 
                        52.490498
                    ], 
                    "tags": {
                        "altitude": 69.9, 
                        "datetime": 1376731062
                    }
                }, 
                {
                    "coords": [
                        13.221316, 
                        52.489695
                    ], 
                    "tags": {
                        "altitude": 58.8, 
                        "datetime": 1376730897
                    }
                }, 
                {
                    "coords": [
                        13.233876, 
                        52.48959
                    ], 
                    "tags": {
                        "altitude": 52.9, 
                        "datetime": 1376730612
                    }
                }, 
                {
                    "coords": [
                        13.231805, 
                        52.490537
                    ], 
                    "tags": {
                        "altitude": 51.4, 
                        "datetime": 1376730345
                    }
                }, 
                {
                    "coords": [
                        13.227167, 
                        52.496973
                    ], 
                    "tags": {
                        "altitude": 47.5, 
                        "datetime": 1376730055
                    }
                }, 
                {
                    "coords": [
                        13.248257, 
                        52.50296
                    ], 
                    "tags": {
                        "altitude": 87.3, 
                        "datetime": 1376729446
                    }
                }, 
                {
                    "coords": [
                        13.249617, 
                        52.501565
                    ], 
                    "tags": {
                        "altitude": 64.1, 
                        "datetime": 1376729222
                    }
                }, 
                {
                    "coords": [
                        13.257437, 
                        52.508006
                    ], 
                    "tags": {
                        "altitude": 70.6, 
                        "datetime": 1376729276
                    }
                }, 
                {
                    "coords": [
                        13.257437, 
                        52.508006
                    ], 
                    "tags": {
                        "altitude": 60.1, 
                        "datetime": 1376728877
                    }
                }
            ], 
            "slug": "grunewald", 
            "tags": {
                "ascent": 223.3, 
                "descent": 207.3
            }, 
            "type": {
                "id": ' . $routeType->getId() . ', 
                "name": "Marathon"
            }, 
            "user": {
                "avatar": "http://assets.trailburning.com/images/profile/mattallbeury/avatar_ma.png", 
                "name": "mattallbeury", 
                "title": "Matt Allbeury",
                "type": "user",
                "is_ambassador": false
            },
            "share_media": {
                "mimetype": "image/jpeg", 
                "path": "/cb88c97a09a59aa2452c0d0cdfdd2f4ccc211a53_share.jpg"
            },
            "rating": 0,
            "start": [
                13.257437,
                52.508006
            ]
        }';
        
        $this->assertJsonStringEqualsJsonString($expectedJson, json_encode($route->export()),
            'Route::export() returns the expected data');
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
        $route = $this->getRoute('grunewald');
        
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
        
        if (!$routeCategory) {
            $this->fail('Missing RouteCategory with name "Ultra Marathon" in test DB');
        }
        
        // Get a Media to set as favorite Media
        if (count($route->getMedias()) == 0) {
            $this->fail('Missing Media for Route in test DB');
        }
        $media = $route->getMedias()[0];
        
        $data = [
            'name' => 'updated name',
            'region' => 'updated region',
            'about' => 'updated about',
            'publish' => false,
            'route_type_id' => $routeType->getId(),
            'route_category_id' => $routeCategory->getId(),
            'media_id' => $media->getId(),
        ];   
        
        $route->updateFromJSON(json_encode($data));
        
        $em->persist($route);
        $em->flush();
        
        $this->assertEquals('updated name', $route->getName());
        $this->assertEquals('updated region', $route->getRegion());
        $this->assertEquals('updated about', $route->getAbout());
        $this->assertEquals(false, $route->getPublish());
        $this->assertEquals($routeType->getId(), $route->getRouteTypeId());
        $this->assertEquals($routeCategory->getId(), $route->getRouteCategoryId());
        $this->assertEquals($media->getId(), $route->getMediaId());
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
        $user = $this->getUser('mattallbeury');
        
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
     * @expectedExceptionMessage Before publishing a Route, the name field must be set
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
        
    /**
     * 
     */
    public function testExportAsActivity()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteData',
        ]);
            
        $route = $this->getRoute('grunewald');
        
        $expected = [
            'url' => '/trail/grunewald',
            'objectType' => 'trail',
            'id' => $route->getId(),
            'displayName' => 'Grunewald',
        ];
        
        $this->assertEquals($expected, $route->exportAsActivity(),
            'Route::exportAsActivity() returns the expected data array');
    }
    
    public function testHasUserLike()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteData',
        ]);
        
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $user = $this->getUser('mattallbeury');
        $route = $this->getRoute('grunewald');
        
        $this->assertFalse($route->hasUserLike($user), 'User does not like the Route');
        
        $routeLike = new RouteLike();
        $routeLike->setUser($user);
        $routeLike->setRoute($route);
        
        $em->persist($routeLike);
        $em->flush();
        $em->refresh($route);
        
        $this->assertTrue($route->hasUserLike($user), 'User does like the Route');
    }   
    
    public function testGetFavouriteMedia()
    {   
        $route = new Route();
        $media1 = new Media();
        $media2 = new Media();
        
        $this->assertNull($route->getFavouriteMedia(), 'Route::getFavouriteMedia() returns null for no Media set');
        
        $route->addMedia($media1);
        $this->assertSame($media1, $route->getFavouriteMedia(), 'Route::getFavouriteMedia() returns the  first Media'); 
    }
    
    public function testAttribute()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteData',
        ]);
        
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $attribute = $this->getAttribute('cycle', 'activity');
        $route = $this->getRoute('grunewald');
        
        $this->assertFalse($route->hasAttribute($attribute), 'Route does not have this Attribute');
        
        $route->addAttribute($attribute);
        $em->persist($route);
        $em->flush();
        
        $this->assertTrue($route->hasAttribute($attribute), 'Route does have this Attribute');
    }
    
}