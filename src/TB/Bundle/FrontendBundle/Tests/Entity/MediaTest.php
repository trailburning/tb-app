<?php 

namespace TB\Bundle\APIBundle\Tests\Entity;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use TB\Bundle\FrontendBundle\Entity\Media;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Gaufrette\File;
use CrEOF\Spatial\PHP\Types\Geometry\Point;

class MediaTest extends WebTestCase
{
    
    protected static function getKernelClass()
    {
        require_once self::getPhpUnitXmlDir() . '/frontend/AppKernel.php';

        return 'AppKernel';
    }
    
    /**
     * Test the filesystem with uploading, and deleting a file
     */
    public function testFilesystem()
    {
        $filesystem = $this->getContainer()->get('trail_media_files_filesystem');
        $file = new File('testfile', $filesystem);
        $file->setContent('Hello World');
        
        $this->assertGreaterThan(0, $filesystem->size('testfile'));
        $this->assertTrue($filesystem->has('testfile'));
        
        $filesystem->delete('testfile');
        $this->assertFalse($filesystem->has('testfile'));
    }
    
    public function testReadMetadata()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteData',
        ]);        
        
        // Get Route from DB with the slug "grunewald"..
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $route = $em
            ->getRepository('TBFrontendBundle:Route')
            ->findOneBySlug('grunewald');
        if (!$route) {
            $this->fail('Missing Route with slug "grunewald" in test DB');
        }
        
        $mediaImporter = $this->getContainer()->get('media_importer');
        
        $filepath = realpath(__DIR__ . '/../../DataFixtures/Media/grunewald/P5250773.jpg');
        $file = new UploadedFile($filepath, 'P5250773.jpg');
        
        $media = new Media();
        $media->setRoute($route);
        $media->setFile($file);
        $media->readMetadata($mediaImporter);
        
        $this->assertEquals('58447', $media->getTags()['filesize'], 
            'the filesize was extracted from image metadata');
        $this->assertEquals('1369470154', $media->getTags()['datetime'],
            'the datetime was extracted from image metadata');
        $this->assertEquals(640, $media->getTags()['width'], 
            'the width was extracted from image metadata');
        $this->assertEquals(480, $media->getTags()['height'],
            'the height was extracted from image metadata');
        $this->assertEquals(60.1, $media->getTags()['altitude'],
            'the altitude was calculated from the nearest RoutePoint');            
        $this->assertEquals(13.257437, $media->getCoords()->getLongitude(),
            'the coords longitude was calculated from the nearest RoutePoint');
        $this->assertEquals(52.508006, $media->getCoords()->getLatitude(),
            'the coords latitude was calculated from the nearest RoutePoint');            
    }
    
    /**
     * Test Image with no DateTime metadata
     */
    public function testReadMetadataNoDateTimeImage()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteData',
        ]);        
        
        // Get Route from DB with the slug "grunewald"..
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $route = $em
            ->getRepository('TBFrontendBundle:Route')
            ->findOneBySlug('grunewald');
        if (!$route) {
            $this->fail('Missing Route with slug "grunewald" in test DB');
        }
        
        $mediaImporter = $this->getContainer()->get('media_importer');
        
        $filepath = realpath(__DIR__ . '/../../DataFixtures/Media/no_metadata.jpg');
        $file = new UploadedFile($filepath, 'P5250773.jpg');
        
        $media = new Media();
        $media->setRoute($route);
        $media->setFile($file);
        $media->readMetadata($mediaImporter);
        
        $this->assertEquals(1376721677, $media->getTags()['datetime'],
            'the datetime was extracted from image metadata');
    }
    
    public function testUpload()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteData',
        ]);        
        
        // Get Route from DB with the slug "grunewald"..
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $route = $em
            ->getRepository('TBFrontendBundle:Route')
            ->findOneBySlug('grunewald');
        if (!$route) {
            $this->fail('Missing Route with slug "grunewald" in test DB');
        }
        
        $filesystem = $this->getContainer()->get('trail_media_files_filesystem');
        $media = new Media();

        $filepath = realpath(__DIR__ . '/../../DataFixtures/Media/grunewald/P5250773.jpg');
        $file = new UploadedFile($filepath, 'P5250773.jpg');
        $media->setFile($file);
        $media->setRoute($route);
        $filename = $media->upload($filesystem);
        
        $this->assertTrue($filesystem->has(str_replace('trailburning-media', '', $filename)), 
            'The file exists on the provided filesystem');
        $this->assertRegExp('/\/' . $route->getId() . '\/[\d\w]+\.jpg/', $filename,
            'The files path retuned by the upload() method');    
        $this->assertRegExp('/trailburning-media\/' . $route->getId() . '\/[\d\w]+\.jpg/', $media->getPath(),
            'The files path on the provided filesystem was set to the media object');
        $this->assertEquals('P5250773.jpg', $media->getFilename(),
            'The files original name was set to the media objecs');
    }
    
    public function testToJSON()
    {
        $media = new Media();
        $media->setFilename('file.jpg');
        $media->setPath('path/file.jpg');
        $media->setPath('path/file.jpg');
        $media->setCoords(new Point(13.257437, 52.508006, 4326));
        $media->setTags(['key' => 'val']);
        $obj = json_decode($media->toJSON());
        
        $expected = new \stdClass();
        $expected->id = '';
        $expected->filename = 'file.jpg';
        $expected->mimetype = 'image/jpeg';
        $versions = new \stdClass();
        $versions->path = 'path/file.jpg';
        $versions->size = 0;    
        $expected->versions = array($versions);
        $coords = new \stdClass();
        $coords->long = 13.257437;
        $coords->lat = 52.508006;
        $expected->coords = $coords;
        $tags = new \stdClass();
        $tags->key = 'val';
        $expected->tags = $tags;
        
        $this->assertNotNull($obj, 'A valid JSON sting was returned by Media::toJSON()');
        $this->assertEquals($expected, $obj, 'The returned JSON object has all expected fields');
    }
    
}