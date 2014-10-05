<?php 

namespace TB\Bundle\FrontendBundle\Tests\Entity;

use TB\Bundle\FrontendBundle\Tests\AbstractFrontendTest;
use TB\Bundle\FrontendBundle\Entity\Media;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Gaufrette\File;
use CrEOF\Spatial\PHP\Types\Geometry\Point;

class MediaTest extends AbstractFrontendTest
{
    
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
        
        $route = $this->getRoute('grunewald');
        
        $mediaImporter = $this->getContainer()->get('tb.media.importer');
        
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
        
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $route = $this->getRoute('grunewald');
        
        $mediaImporter = $this->getContainer()->get('tb.media.importer');
        
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
        $route = $this->getRoute('grunewald');
        
        $filesystem = $this->getContainer()->get('trail_media_files_filesystem');
        $media = new Media();

        $filepath = realpath(__DIR__ . '/../../DataFixtures/Media/grunewald/P5250773.jpg');
        $file = new UploadedFile($filepath, 'P5250773.jpg');
        $media->setFile($file);
        $media->setRoute($route);
        $filename = $media->upload($filesystem);
        
        $this->assertTrue($filesystem->has($filename), 
            'The file exists on the provided filesystem');
        $this->assertRegExp('/\/' . $route->getId() . '\/[\d\w]+\.jpg/', $filename,
            'The files path retuned by the upload() method');    
        $this->assertRegExp('/\/' . $route->getId() . '\/[\d\w]+\.jpg/', $media->getPath(),
            'The files path on the provided filesystem was set to the media object');
        $this->assertEquals('P5250773.jpg', $media->getFilename(),
            'The files original name was set to the media objecs');
    }
    
    public function testExport()
    {
        $media = new Media();
        $media->setId(1);
        $media->setFilename('file.jpg');
        $media->setPath('/path/file.jpg');
        $media->setCoords(new Point(13.257437, 52.508006, 4326));
        $media->setTags(['key' => 'val']);
        
        $expectedJson = '{
            "id":' . $media->getId() . ',
            "filename":"file.jpg",
            "mimetype":"image\/jpeg",
            "versions":[
                {
                    "path":"\/path\/file.jpg",
                    "size":0
                }
            ],
            "coords":{
                "long":13.257437,
                "lat":52.508006
            },
            "tags":{
                "key":"val"
            }
        }';
        
        $this->assertJsonStringEqualsJsonString($expectedJson, json_encode($media->export()),
            'Media::export() returns the expected data array');
    }
    
}