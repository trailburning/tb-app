<?php

namespace TB\Bundle\APIBundle\Tests\Controller;

use TB\Bundle\APIBundle\Tests\AbstractApiTest;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\ApplicationTester;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;


/**
 *
 */
class SocialMediaControllerTest extends AbstractApiTest
{
    
    public function testGetSearch()
    {
        $this->loadFixtures([]);
        
        $client = $this->createClient();
        $crawler = $client->request('GET', '/v1/socialmedia?term=trailrunning');
        
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode(),
            'Response returns Status Code 200');
        $this->assertJsonResponse($client);  
        $responseObj = json_decode($client->getResponse()->getContent());
        
        $this->assertInternalType('array', $responseObj->value, 
            'The response JSON value is an array');   
    }
}
