<?php 

namespace TB\Bundle\ApiBundle\Tests\Util;

use TB\Bundle\APIBundle\Tests\AbstractApiTest;
use TB\Bundle\FrontendBundle\Entity\GpxFile;
use TB\Bundle\APIBundle\Util\ApiException;

class PostgisTest extends AbstractApiTest
{

    public function testWriteRoute()
    {
        $postgis = $this->getContainer()->get('postgis');
        $route = $this->importRoute(realpath(__DIR__ . '/../../DataFixtures/GPX/example.gpx'));
        $routeId = $postgis->writeRoute($route);
        $this->assertGreaterThan(0, $routeId, 'The Route was written to the DB and a valid primary key returned');
    }
    
    public function testWriteRouteThrowsException()
    {
        $postgis = $this->getContainer()->get('postgis');
        $route = $this->importRoute(realpath(__DIR__ . '/../../DataFixtures/GPX/invalid_linestring.gpx'));
        try {
            $routeId = $postgis->writeRoute($route);
        } catch (ApiException $e) {
            $this->assertEquals('Problem with GPX file, not a valid Trail', $e->getMessage(),
                'The Exception contains the correct message text');
        }
        
        // Test that no Route was inserted and transaction rollback was done
        $result = $postgis->query('SELECT COUNT(id) FROM routes');
        $row = $result->fetch(\PDO::FETCH_ASSOC);
        $this->assertEquals(0, $row['count'], 'No Route was inserted');
        
        // Test that gpx_files record gets deleted
        $result = $postgis->query('SELECT COUNT(id) FROM gpx_files');
        $row = $result->fetch(\PDO::FETCH_ASSOC);
        $this->assertEquals(0, $row['count'], 'gpx_files record was deleted');
    }
    
    public function testSearchRoutes()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteData',
        ]); 
        $postgis = $this->getContainer()->get('postgis');
        $params = [];
        $routes = $postgis->searchRoutes($params, 1, 0, $count);
        $this->assertInternalType('array', $routes, 
            'searchRoutes returns an array of Routes');   
        $this->assertEquals(1, count($routes),
            'searchRoutes returns one route');
        $this->assertEquals(3, $count,
            'the total number of results is 3');
            
        // Limit search to a radius around a point
        $params = ['radius' => 20, 'long' => 13.2, 'lat' => 52.5];
        $routes = $postgis->searchRoutes($params, 1, 0, $count);
        $this->assertInternalType('array', $routes, 
            'searchRoutes returns an array of Routes');   
        $this->assertEquals(1, count($routes),
            'searchRoutes returns one route');
        $this->assertEquals(2, $count,
            'the total number of results is 2');            
            
        // Order results nearest to a point
        $params = ['order' => 'distance', 'long' => 13.2, 'lat' => 52.5];
        $routes = $postgis->searchRoutes($params, 1, 0, $count);
        $this->assertInternalType('array', $routes, 
            'searchRoutes returns an array of Routes');   
        $this->assertEquals(1, count($routes),
            'searchRoutes returns one route');
        $this->assertEquals(3, $count,
            'the total number of results is 3');            
    }
    
    public function testSearchCampaignRoutes()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\CampaignData',
        ]); 
        $postgis = $this->getContainer()->get('postgis');
        
        $campaign = $this->getCampaign('urbantrails-london');
        
        $params = ['campaign_id' => $campaign->getId()];
        $routes = $postgis->searchRoutes($params, 10, 0, $count);
        $this->assertInternalType('array', $routes, 
            'searchRoutes returns an array of Routes');   
        $this->assertEquals(1, count($routes),
            'searchRoutes returns one route');
        $this->assertEquals(1, $count,
            'the total number of results is 1');
    }
    
    protected function importRoute($file)
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\UserProfileData',
        ]); 
        $importer = $this->getContainer()->get('tb.gpxfile.importer');
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $user = $this->getUser('mattallbeury');
        $routes = $importer->parse(file_get_contents($file));
        $route = $routes[0];
        
        $gpxFile = new GpxFile();    
        $gpxFile->setPath('example.gpx');
        
        $em->persist($gpxFile);
        $em->flush();
        
        $route->setGpxFileId($gpxFile->getId());
        $route->setUserId($user->getId());
        
        return $route;
    }
    
    public function testRelatedRoutes()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteData',
        ]); 
        $postgis = $this->getContainer()->get('postgis');
        $route = $this->getRoute('grunewald');
        $routes = $postgis->relatedRoutes($route->getId());
        $this->assertInternalType('array', $routes, 
            'relatedRoutes returns an array of Routes');   
        $this->assertEquals(1, count($routes),
            'relatedRoutes returns one route');
    }
    
    protected function validateSearchParams()
    {
        $postgis = $this->getContainer()->get('postgis');
        
        $params = ['invalid' => 'value'];
        try {
            $postgis->validateSearchParams($params);
            $this->fail('No Exception was thrown for invalid parameter');
        } catch (ApiException $e) {
            $this->pass('ApiException was thrown for invalid parameter');
        } finally {
            $this->fail('No ApiException was thrown for invalid parameter');
        }
        
        $params = ['invalid' => 'value'];
        try {
            $postgis->validateSearchParams($params);
            $this->fail('No Exception was thrown for valid parameter');
        } catch (\Exception $e) {
            $this->pass('An Exception was thrown for valid parameter');
        }
        
        $params = ['order' => 'invalidValue'];
        try {
            $postgis->validateSearchParams($params);
            $this->fail('No Exception was thrown for invalid parameter value');
        } catch (ApiException $e) {
            $this->pass('ApiException was thrown for invalid parameter value');
        } finally {
            $this->fail('No ApiException was thrown for invalid parameter value');
        }
        
        $params = ['order' => 'date'];
        try {
            $postgis->validateSearchParams($params);
            $this->fail('No Exception was thrown for valid parameter value');
        } catch (\Exception $e) {
            $this->pass('An Exception was thrown for valid parameter value');
        }
        
        $params = ['order' => 'distance'];
        try {
            $postgis->validateSearchParams($params);
            $this->fail('No Exception was thrown for missing parameter dependency');
        } catch (ApiException $e) {
            $this->pass('ApiException was thrown for missing parameter dependency');
        } finally {
            $this->fail('No ApiException was thrown for missing parameter dependency');
        }
        
        $params = ['order' => 'distance', 'lat' => 51, 'long' => 13];
        try {
            $postgis->validateSearchParams($params);
            $this->fail('No Exception was thrown for valid search parameter');
        } catch (\Exception $e) {
            $this->pass('An Exception was thrown for valid search parameter');
        }
        
        $params = ['radius' => 50];
        try {
            $postgis->validateSearchParams($params);
            $this->fail('No Exception was thrown for missing parameter dependency');
        } catch (ApiException $e) {
            $this->pass('ApiException was thrown for missing parameter dependency');
        } finally {
            $this->fail('No ApiException was thrown for missing parameter dependency');
        }
        
        $params = ['radius' => 50, 'lat' => 51, 'long' => 13];
        try {
            $postgis->validateSearchParams($params);
            $this->fail('No Exception was thrown for valid search parameter');
        } catch (\Exception $e) {
            $this->pass('An Exception was thrown for valid search parameter');
        }
    }
    
    public function testUpdateRegionArea()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RegionData',
        ]); 
        $postgis = $this->getContainer()->get('postgis');
        $region = $this->getRegion('london');
        $gml = file_get_contents(realpath(__DIR__ . '/../../DataFixtures/GML/london.gml'));
        $result = $postgis->updateRegionArea($region->getId(), $gml);
        
        $this->assertTrue($result);
    }
    
    public function testRelatedCampaigns()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\CampaignData',
        ]); 
        $postgis = $this->getContainer()->get('postgis');
        $route = $this->getRoute('london');
        $campaigns = $postgis->relatedCampaigns($route->getId());
        $this->assertInternalType('array', $campaigns, 
            'relatedCampaigns returns an array of Campaigns');   
        $this->assertEquals(1, count($campaigns),
            'relatedCampaigns returns one Campaigns');
    }
}    