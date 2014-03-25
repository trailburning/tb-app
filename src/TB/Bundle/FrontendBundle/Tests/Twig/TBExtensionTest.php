<?php 

namespace TB\Bundle\FrontendBundle\Tests\Entity;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use TB\Bundle\FrontendBundle\Util\MediaImporter;
use CrEOF\Spatial\PHP\Types\Geometry\Point;

class TBExtensionTest extends WebTestCase
{
    
    protected static function getKernelClass()
    {
        require_once self::getPhpUnitXmlDir() . '/frontend/AppKernel.php';

        return 'AppKernel';
    }
    
    public function setUp()
    {
        $this->extension = $this->getContainer()->get('tb.twig.tb_extension');
    }
    
    public function testUrlTruncateFilter()
    {
        $this->assertEquals('test.de/test', $this->extension->urlTruncateFilter('http://www.test.de/test'),
            'urlTruncateFilter() truncates http:// and www.');
        $this->assertEquals('test.de/test', $this->extension->urlTruncateFilter('https://test.de/test'),
            'urlTruncateFilter() truncates https://');
    }
    
    public function testUrlShareableFilter()
    {
        $this->assertEquals('www.test.de%2Ftest', $this->extension->urlShareableFilter('http://www.test.de/test'),
            'urlTruncateFilter() truncates http://');
        $this->assertEquals('test.de%2Ftest', $this->extension->urlShareableFilter('https://test.de/test'),
            'urlTruncateFilter() truncates https://');
    }
    
    public function testDimensionFormatFilter()
    {
        $this->assertEquals('1’000 m', $this->extension->dimensionFormatFilter(1000, 'm'),
            'thausands separator is included, unit is added');
        $this->assertEquals('10 km', $this->extension->dimensionFormatFilter(1000, 'km', 100),
            'value is relative to the base passed to the function');
        $this->assertEquals('1’001 m', $this->extension->dimensionFormatFilter(1000.5678, 'm'),
            'value is rounded');
    }
    
    public function testExtractEntity()
    {
        // setup Entity mock
        $entity = $this->getMock('Entity', array('getFirstField', 'getSecondField'), array(), '', false);
        $entity->expects($this->exactly(1))
                ->method('getFirstField')
                ->will($this->returnValue('firstValue'));
        $entity->expects($this->exactly(1))
                ->method('getSecondField')
                ->will($this->returnValue('secondValue'));
        
        $expected = ['firstField' => 'firstValue', 'secondField' => 'secondValue'];
        $this->assertEquals($expected, $this->extension->extractEntity($entity, ['firstField', 'secondField']),
            'extractEntity returns an array of values for the requested fields');
    }
    
}