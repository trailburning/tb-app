<?php

namespace TB\Bundle\APIBundle\Tests\Controller;

use Liip\FunctionalTestBundle\Test\WebTestCase;
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

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $user = $em
            ->getRepository('TBFrontendBundle:User')
            ->findOneByName('paultran');
        if (!$user) {
            $this->fail('Missing User with name "mattallbeury" in test DB');
        }
        
        $user2 = $em
            ->getRepository('TBFrontendBundle:User')
            ->findOneByName('mattallbeury');
        if (!$user) {
            $this->fail('Missing User with name "mattallbeury" in test DB');
        }
        
        // Test Trailburning-User-ID not set
        $client = $this->createClient();
        $crawler = $client->request('GET', '/v1/activity/feed');
        $this->assertEquals(Response::HTTP_BAD_REQUEST,  $client->getResponse()->getStatusCode());
        $this->assertJsonResponse($client);
        
        $client = $this->createClient();
        $crawler = $client->request('GET', '/v1/activity/feed', [], [], ['HTTP_Trailburning_User_ID' => $user->getId()]);
        
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode());
        $this->assertJsonResponse($client);
        
    }
    
}
