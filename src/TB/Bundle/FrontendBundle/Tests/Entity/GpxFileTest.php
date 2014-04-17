<?php 

namespace TB\Bundle\FrontendBundle\Tests\Entity;

use TB\Bundle\FrontendBundle\Tests\AbstractFrontendTest;
use TB\Bundle\FrontendBundle\Entity\GpxFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Gaufrette\File;

class GpxFileTest extends AbstractFrontendTest
{
    
    /**
     * Test the filesystem with uploading, and deleting a file
     */
    public function testFilesystem()
    {
        $filesystem = $this->getContainer()->get('gpx_files_filesystem');
        $file = new File('testfile', $filesystem);
        $file->setContent('Hello World');
        
        $this->assertGreaterThan(0, $filesystem->size('testfile'));
        $this->assertTrue($filesystem->has('testfile'));
        
        $filesystem->delete('testfile');
        $this->assertFalse($filesystem->has('testfile'));
    }
    
    public function testUpload()
    {
        $filesystem = $this->getContainer()->get('gpx_files_filesystem');
        $gpxFile = new GpxFile();
        
        $filepath = realpath(__DIR__ . '/../../DataFixtures/GPX/example.gpx');
        
        $file = new UploadedFile($filepath, 'example.gpx');
        
        $gpxFile->setFile($file);
        $filename = $gpxFile->upload($filesystem);
        
        // check if file exists on the provided filesystem
        $this->assertTrue($filesystem->has($filename));
        $this->assertEquals($filename, $gpxFile->getPath());
    }
}