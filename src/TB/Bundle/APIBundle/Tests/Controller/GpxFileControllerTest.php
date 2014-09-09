<?php

namespace TB\Bundle\APIBundle\Tests\Controller;

use TB\Bundle\APIBundle\Tests\AbstractApiTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\ApplicationTester;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use TB\Bundle\APIBundle\Util\ApiException;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 *
 */
class GpxFileControllerTest extends AbstractApiTestCase
{    
    
    /**
     * 
     */
    public function testPostImport()
    {
        
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteData',
        ]);
        
        $user = $this->getUser('mattallbeury');
        
        $gpxfile = new UploadedFile(
            realpath(__DIR__ . '/../../DataFixtures/GPX/example.gpx'),
            'example.gpx'
        );
        
        $client = $this->createClient();
        $crawler = $client->request('POST', '/v1/import/gpx', [], ['gpxfile' => $gpxfile], ['HTTP_Trailburning_User_ID' => $user->getId()]);
        
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode(),
            'Response returns Status Code 200');
        $this->assertJsonResponse($client);  
        
        $responseObj = json_decode($client->getResponse()->getContent());
        $this->assertEquals('GPX successfully imports', $responseObj->usermsg,
            'usermsg of JSON response is ok');
        $this->assertGreaterThan(0, count($responseObj->value->route_ids),
            'route_ids array is greater than 0');
    }
    
    public function testPostImportBrokenHstore()
    {
        
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteData',
        ]);
        
        $user = $this->getUser('mattallbeury');
        
        $gpxfile = new UploadedFile(
            realpath(__DIR__ . '/../../DataFixtures/GPX/broken/broken.gpx'),
            'broken.gpx'
        );
        
        $client = $this->createClient();
        $crawler = $client->request('POST', '/v1/import/gpx', [], ['gpxfile' => $gpxfile], ['HTTP_Trailburning_User_ID' => $user->getId()]);
        
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode(),
            'Response returns Status Code 200');
        $this->assertJsonResponse($client);  
        
        $responseObj = json_decode($client->getResponse()->getContent());
                
        $routeId = $responseObj->value->route_ids[0];
        
        $mediaFile = new UploadedFile(
            realpath(__DIR__ . '/../../DataFixtures/Media/grunewald/P5250773.jpg'),
            'P5250773.jpg'
        );
        
        $client = $this->createClient();
        $crawler = $client->request('POST', '/v1/route/' . $routeId . '/medias/add', [], ['medias' => $mediaFile]);
        
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode(),
            'Response returns Status Code 200');
        $this->assertJsonResponse($client);  

    }
    
    public function testPostImportNoDatetime()
    {
        
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteData',
        ]);
        
        $user = $this->getUser('mattallbeury');
        
        $gpxfile = new UploadedFile(
            realpath(__DIR__ . '/../../DataFixtures/GPX/no_datetime.gpx'),
            'no_datetime.gpx'
        );
        
        $client = $this->createClient();
        $crawler = $client->request('POST', '/v1/import/gpx', [], ['gpxfile' => $gpxfile], ['HTTP_Trailburning_User_ID' => $user->getId()]);
        $this->assertEquals(Response::HTTP_BAD_REQUEST,  $client->getResponse()->getStatusCode(),
            'Response returns Status Code 400');
        $this->assertJsonResponse($client);  
        
    }
    
    

}
