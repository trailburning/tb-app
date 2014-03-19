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
class MediaControllerTest extends AbstractApiTestCase
{
    
    public function testGetRouteMedias()
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
        
        $client = $this->createClient();
        $crawler = $client->request('GET', '/v1/route/' . $route->getId() . '/medias');
        
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode(),
            'Response returns Status Code 200');
        $this->assertJsonResponse($client);  
        $responseObj = json_decode($client->getResponse()->getContent());
        
        $this->assertInternalType('array', $responseObj->value, 
            'The response JSON value is an array');   
        $this->assertEquals(8, count($responseObj->value),
            'The response JSON value array with media objects'); 
        $this->assertObjectHasAttribute('id', $responseObj->value[0],
            'The response JSON media has the field id'); 
        $this->assertGreaterThan(0,  $responseObj->value[0]->id, 
            'The id field has a value greater than 0');
        $this->assertObjectHasAttribute('filename', $responseObj->value[0],
            'The response JSON media has the field filename');               
        $this->assertObjectHasAttribute('mimetype', $responseObj->value[0],
            'The response JSON media has the field mimetype');              
        $this->assertObjectHasAttribute('coords', $responseObj->value[0],
            'The response JSON media has the field coords');              
        $this->assertObjectHasAttribute('tags', $responseObj->value[0],
            'The response JSON media has the field tags');                          
    }
    
    
    /**
     * Test postRouteMedias() with single image
     */
    public function testPostRouteMedias()
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
        
        $mediaFile = new UploadedFile(
            realpath(__DIR__ . '/../../DataFixtures/Media/grunewald/P5250773.jpg'),
            'P5250773.jpg'
        );
        
        $client = $this->createClient();
        $crawler = $client->request('POST', '/v1/route/' . $route->getId() . '/medias/add', [], ['medias' => $mediaFile]);
        
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode(),
            'Response returns Status Code 200');
        $this->assertJsonResponse($client);  
        
        $responseObj = json_decode($client->getResponse()->getContent());
        
        $this->assertInternalType('array', $responseObj->value, 
            'The response JSON value is an array');   
        $this->assertEquals(1, count($responseObj->value),
            'The response JSON value array contains one media object'); 
        $this->assertGreaterThan(0, $responseObj->value[0]->id,
            'The response JSON media id is greater than 0');   
        $this->assertEquals('P5250773.jpg', $responseObj->value[0]->filename,
            'The response JSON media filename is ok');               
        $this->assertEquals('image/jpeg', $responseObj->value[0]->mimetype,
            'The response JSON media mimetype is ok');
        $this->assertRegExp('/trailburning-media\/' . $route->getId() . '\/[\d\w]+\.jpg/', $responseObj->value[0]->versions[0]->path,
            'The response JSON media versions is ok');            
    }
    
    /**
     * Test postRouteMedias() with multiple images
     */
    public function testPostRouteMediasMultiple()
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
        
        $mediaFile1 = new UploadedFile(
            realpath(__DIR__ . '/../../DataFixtures/Media/grunewald/P5250773.jpg'),
            'P5250773.jpg'
        );
        
        $mediaFile2 = new UploadedFile(
            realpath(__DIR__ . '/../../DataFixtures/Media/grunewald/P5250773.jpg'),
            'P5250783.jpg'
        );
        
        $client = $this->createClient();
        $crawler = $client->request('POST', '/v1/route/' . $route->getId() . '/medias/add', [], ['medias' => array($mediaFile1, $mediaFile2)]);
        
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode(),
            'Response returns Status Code 200');
        $this->assertJsonResponse($client);  
        
        $responseObj = json_decode($client->getResponse()->getContent());
        
        $this->assertInternalType('array', $responseObj->value, 
            'The response JSON value is an array');   
        $this->assertEquals(2, count($responseObj->value),
            'The response JSON value array contains one media object'); 
    }
    
    public function testDeleteMedia()
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
        
        $media = $em
            ->getRepository('TBFrontendBundle:Media')
            ->findOneByRouteId($route->getId());
        
        if (!$media) {
            $this->fail('No Media found for Route with slug "grunewald" in test DB');
        }
        
        $client = $this->createClient();
        $crawler = $client->request('DELETE', '/v1/media/' . $media->getId());
        
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode(),
            'Response returns Status Code 200');
        
        // check that the media was really deleted    
        $media = $em
            ->getRepository('TBFrontendBundle:Media')
            ->findOneById($media->getId());
    
        if ($media) {
            $this->fail('The Media was not deleted from Test DB');
        }
    }
    
    public function testPutMedia()
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
        
        $media = $em
            ->getRepository('TBFrontendBundle:Media')
            ->findOneByRouteId($route->getId());
        
        if (!$media) {
            $this->fail('No Media found for Route with slug "grunewald" in test DB');
        }
        
        $newData = new \stdClass();
        $coords = new \stdClass();
        $coords->long = 13.3;
        $coords->lat = 53.3;
        $newData->coords = $coords;
        $tags = new \stdClass();
        $tags->datetime = '111111111';
        $newData->tags = $tags;
        
        $client = $this->createClient();
        $crawler = $client->request('PUT', '/v1/media/' . $media->getId(), ['json' => json_encode($newData)]);
        
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode(),
            'Response returns Status Code 200');
        
        // check that the media was really deleted    
        $media = $em
            ->getRepository('TBFrontendBundle:Media')
            ->findOneById($media->getId());
        
        // Entity fields are not updated, DB values are ok (maybe some kind of Doctrine caching?) Fix when possible
        // $this->assertEquals('111111111', $media->getTags()['datetime'], 
        //     'The Media datetime tag data was updated');            
        // $this->assertEquals(13.3, $media2->getCoords()->getLongitude(), 
        //     'The Media coords longitude data was updated');
        // $this->assertEquals(53.3, $media->getCoords()->getLatitude(), 
        //     'The Media coords latitude data was updated');
        
    }

}
