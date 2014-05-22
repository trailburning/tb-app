<?php

namespace TB\Bundle\APIBundle\Tests\Controller;

use TB\Bundle\APIBundle\Tests\AbstractApiTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\ApplicationTester;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;

/**
 *
 */
class ActivityControllerTest extends AbstractApiTestCase
{
    
    /**
     * Test the GET /activity/feed action
     */
    public function testGetActivityByUser()
    {   
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\ActivityStreamData',
        ]);

        // $user = $this->getUser('paultran');        
        // $user2 = $this->getUser('mattallbeury');
        // 
        // // Test Trailburning-User-ID not set
        // $client = $this->createClient();
        // $crawler = $client->request('GET', '/v1/activity/feed');
        // $this->assertEquals(Response::HTTP_BAD_REQUEST,  $client->getResponse()->getStatusCode());
        // $this->assertJsonResponse($client);
        // 
        // $client = $this->createClient();
        // $crawler = $client->request('GET', '/v1/activity/feed', [], [], ['HTTP_Trailburning_User_ID' => $user->getId()]);
        // 
        // $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode());
        // $this->assertJsonResponse($client);
        
    }
    
}
