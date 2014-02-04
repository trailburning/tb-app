<?php

namespace TB\Bundle\APIBundle\Tests\Controller;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\ApplicationTester;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 *
 */
class GpxFileControllerTest extends WebTestCase
{
    
    protected $environment = 'test_api';
    
    /**
     * 
     */
    public function testImport()
    {
        
        $client = $this->createClient();
        $crawler = $client->request('GET', '/v1/import/gpx');
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode());   
        
    }
    
    
    /**
     * 
     */
    public function testPostImport()
    {
        $gpxfile = new UploadedFile(
            realpath(__DIR__) . '/../../DataFixtures/GpxFile/example.gpx',
            'example.gpx',
            123
        );
        
        $client = $this->createClient();

        $crawler = $client->request('POST', '/v1/import/gpx', array('test' => 'test'), array('gpxfile' => $gpxfile));
        
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode()); 
        
        
    }

}
