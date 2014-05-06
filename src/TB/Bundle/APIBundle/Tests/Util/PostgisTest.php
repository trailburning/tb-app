<?php 

namespace TB\Bundle\ApiBundle\Tests\Util;

use TB\Bundle\APIBundle\Tests\AbstractApiTestCase;
use TB\Bundle\FrontendBundle\Entity\GpxFile;
use TB\Bundle\APIBundle\Util\ApiException;

class PostgisTest extends AbstractApiTestCase
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
        $routes = $postgis->searchRoutes(1, 0, $count);
        $this->assertInternalType('array', $routes, 
            'searchRoutes returns an array of Routes');   
        $this->assertEquals(1, count($routes),
            'searchRoutes returns one route');
        $this->assertEquals(2, $count,
            'the total number of results is 2');
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
    
}    