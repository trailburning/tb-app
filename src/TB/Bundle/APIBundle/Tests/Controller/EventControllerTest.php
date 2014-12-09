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
class EventControllerTest extends AbstractApiTest
{
    
    /**
     * Test the GET /events/search action
     */
    public function testGetEventsSearch()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\EventData',
        ]);
        
        $client = $this->createClient();
        $crawler = $client->request('GET', '/v1/events/search');
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode());
          
        $this->assertJsonResponse($client);
    }

}
