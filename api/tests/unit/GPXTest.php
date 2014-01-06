<?php
use Codeception\Util\Stub;
use TBAPI\importers\GPXImporter;

class GPXTest extends \Codeception\TestCase\Test
{
   /**
    * @var \CodeGuy
    */
    protected $codeGuy;

    protected function _before()
    {
        $this->GPXImporter = new GPXImporter();
    }

    protected function _after()
    {
    }

    public function testParse()
    {
        
        $data = $this->getGPXFile('example.gpx');
        $routes = $this->GPXImporter->parse($data);
        
        $this->assertInternalType('array', $routes);
        $this->assertEquals(1, count($routes));
        
        $route = $routes[0];
        $this->assertInstanceOf('TBAPI\entities\Route', $route);
        $this->assertEquals(null, $route->getGpxFileId());
        $this->assertEquals('trailburning test example', $route->getName());
        $this->assertEquals(null, $route->getRegion());
        $this->assertEquals(null, $route->getSlug());
        $this->assertInternalType('array', $route->getTags());
        $this->assertEquals(0, count($route->getTags()));

        $routePoints = $route->getRoutePoints();
        $this->assertInternalType('array', $routePoints);
        $this->assertEquals(2, count($routePoints));
        
        $routePoint = $routePoints[0];
        $this->assertInternalType('array', $routePoint->getCoords());
        $this->assertEquals(2, count($routePoint->getCoords()));
        $this->assertEquals('13.257499169558287', $routePoint->getCoords()['long']);
        $this->assertEquals('52.50761015340686', $routePoint->getCoords()['lat']);      
        $this->assertInternalType('array', $routePoint->getTags());
        $this->assertEquals(2, count($routePoint->getTags()));
        $this->assertEquals('59.79999923706055', $routePoint->getTags()['altitude']);
        $this->assertEquals(1369469693, $routePoint->getTags()['datetime']);    
        
        $routePoint = $routePoints[1];
        $this->assertInternalType('array', $routePoint->getCoords());
        $this->assertEquals(2, count($routePoint->getCoords()));
        $this->assertEquals('13.257480561733246', $routePoint->getCoords()['long']);
        $this->assertEquals('52.50763764604926', $routePoint->getCoords()['lat']);      
        $this->assertInternalType('array', $routePoint->getTags());
        $this->assertEquals(2, count($routePoint->getTags()));
        $this->assertEquals('59.79999923706055', $routePoint->getTags()['altitude']);
        $this->assertEquals(1369469699, $routePoint->getTags()['datetime']);    
        
    }
    
    public function testParseEmptyEle()
    {
        
        $data = $this->getGPXFile('empty_ele.gpx');
        $routes = $this->GPXImporter->parse($data);     
        
        $this->assertInternalType('array', $routes);
        $this->assertEquals(1, count($routes));
        
        $route = $routes[0];
        $routePoints = $route->getRoutePoints();
        $this->assertInternalType('array', $routePoints);
        $this->assertEquals(3, count($routePoints));
        
        $routePoint = $routePoints[0];
        $this->assertEquals(2, count($routePoint->getTags()));
        $this->assertNull($routePoint->getTags()['altitude']);
        $this->assertEquals(1369469693, $routePoint->getTags()['datetime']);    
        
        $routePoint = $routePoints[1];
        $this->assertEquals(2, count($routePoint->getTags()));
        $this->assertEquals('59.79999923706055', $routePoint->getTags()['altitude']);
        $this->assertEquals(1369469699, $routePoint->getTags()['datetime']);    

        $routePoint = $routePoints[2];
        $this->assertEquals(2, count($routePoint->getTags()));
        $this->assertNull($routePoint->getTags()['altitude']);
        $this->assertEquals(1369469703, $routePoint->getTags()['datetime']);    
        
    }
    
    protected function getGPXFile($name)
    {
        $dataDir = realpath(__DIR__ . '/../_data/gpx');
        $gpxFile = $dataDir . '/' . $name;

        if (!file_exists($gpxFile)) {
            throw new Exception(sprintf('missing GMX file: %s', $gpxFile));
        }
        
        return file_get_contents($gpxFile);
    }

}