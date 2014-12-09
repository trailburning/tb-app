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
class CampaignControllerTest extends AbstractApiTest
{
    
    /**
     * Test the PUT /campaign/{id}#/follow action
     */
    public function testPutCampaignFollow()
    {   
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\CampaignData',
        ]);
        
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        
        $user = $this->getUser('paultran');
        $campaign = $this->getCampaign('urbantrails-london');
        
        // Test Trailburning-User-ID not set
        $client = $this->createClient();
        $crawler = $client->request('PUT', '/v1/campaign/' . $campaign->getId() . '/follow');
        $this->assertEquals(Response::HTTP_BAD_REQUEST,  $client->getResponse()->getStatusCode());
        $this->assertJsonResponse($client);
        
        // Test not existing user
        $crawler = $client->request('PUT', '/v1/campaign/' . $campaign->getId() . '/follow', [], [], ['HTTP_Trailburning_User_ID' => 999999999]);
        $this->assertEquals(Response::HTTP_NOT_FOUND,  $client->getResponse()->getStatusCode());
        $this->assertJsonResponse($client);
        
        // Test not existing campaign to follow
        $crawler = $client->request('PUT', '/v1/campaign/999999999/follow', [], [], ['HTTP_Trailburning_User_ID' => $user->getId()]);
        $this->assertEquals(Response::HTTP_NOT_FOUND,  $client->getResponse()->getStatusCode());
        $this->assertJsonResponse($client);
        
        // Test campaign follow
        $client = $this->createClient();
        $crawler = $client->request('PUT', '/v1/campaign/' . $campaign->getId() . '/follow', [], [], ['HTTP_Trailburning_User_ID' => $user->getId()]);
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode());
        $this->assertJsonResponse($client);
        
        $em->flush();
        
        // check if user is following
        $isFollowing = false;
        foreach ($user->getCampaignsIFollow() as $campaignIFollow) {
            if ($campaignIFollow->getId() == $campaign->getId()) {
                $isFollowing = true;
                break;
            }
        }
        
        if ($isFollowing === false) {
            $this->fail('User is not following');
        }
        
        // Test user follow existing follower
        $client = $this->createClient();
        $crawler = $client->request('PUT', '/v1/campaign/' . $campaign->getId() . '/follow', [], [], ['HTTP_Trailburning_User_ID' => $user->getId()]);
        $this->assertEquals(Response::HTTP_BAD_REQUEST,  $client->getResponse()->getStatusCode());
        $this->assertJsonResponse($client);
    }
    
    /**
     * Test if PUT /campaign/{id}#/follow dispatches the tb.campaign_follow event
     */
    public function testPutCampaignFollowDispatchesEvent()
    {   
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\CampaignData',
        ]);
        
        $user = $this->getUser('paultran');
        $campaign = $this->getCampaign('urbantrails-london');
        
        // set flag to false
        $this->eventDispatched = false;
        
        $client = $this->createClient();
        
        //  get the event dispatcher and add a listener for the tb.campaign_follow event
        $dispatcher = $client->getContainer()->get('event_dispatcher'); 
        $dispatcher->addListener('tb.campaign_follow', function ($event, $eventName, $dispatcher) {
            // this part gets executed when the event is dispatched
            $this->assertInstanceOf('TB\Bundle\FrontendBundle\Event\CampaignFollowEvent', $event,
                'The CampaignFollowEvent was created');
            $this->assertEquals('paultran', $event->getUser()->getName(), 
                'The following User was passed to the CampaignFollowEvent event');
            $this->assertEquals('London', $event->getCampaign()->getTitle(), 
                'The Campaign who gets followed was passed to the CampaignFollowEvent event');
            
            // set flag to true, it means the event was dispatched
            $this->eventDispatched = true;
        });
        
        // execute user follow request
        $crawler = $client->request('PUT', '/v1/campaign/' . $campaign->getId() . '/follow', [], [], ['HTTP_Trailburning_User_ID' => $user->getId()]);
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode());
        
        // test fails when the event gets not dispatched
        $this->assertTrue($this->eventDispatched, 'The tb.campaign_follow Event was successfully dispatched');
    }
    
    /**
     * Test the PUT /campaign/{id}#/unfollow action
     */
    public function testPutCampaignUnfollow()
    {   
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\CampaignData',
        ]);
        
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $user = $this->getUser('paultran');
        $campaign = $this->getCampaign('urbantrails-london');
        
        // Test Trailburning-User-ID not set
        $client = $this->createClient();
        $crawler = $client->request('PUT', '/v1/campaign/' . $campaign->getId() . '/unfollow');
        $this->assertEquals(Response::HTTP_BAD_REQUEST,  $client->getResponse()->getStatusCode());
        $this->assertJsonResponse($client);
        
        // Test not existing user
        $crawler = $client->request('PUT', '/v1/campaign/' . $campaign->getId() . '/unfollow', [], [], ['HTTP_Trailburning_User_ID' => 999999999]);
        $this->assertEquals(Response::HTTP_NOT_FOUND,  $client->getResponse()->getStatusCode());
        $this->assertJsonResponse($client);
        
        // Test not existing campaign to unfollow
        $crawler = $client->request('PUT', '/v1/campaign/999999999/unfollow', [], [], ['HTTP_Trailburning_User_ID' => $user->getId()]);
        $this->assertEquals(Response::HTTP_NOT_FOUND,  $client->getResponse()->getStatusCode());
        $this->assertJsonResponse($client);
        
        // Test user unfollow not existing follower
        $client = $this->createClient();
        $crawler = $client->request('PUT', '/v1/campaign/' . $campaign->getId() . '/unfollow', [], [], ['HTTP_Trailburning_User_ID' => $user->getId()]);
        $this->assertEquals(Response::HTTP_BAD_REQUEST,  $client->getResponse()->getStatusCode());
        $this->assertJsonResponse($client);
        
        // Test campaign unfollow
        $user->addCampaignsIFollow($campaign);
        
        $em->persist($user);
        $em->flush();
        
        $client = $this->createClient();
        $crawler = $client->request('PUT', '/v1/campaign/' . $campaign->getId() . '/unfollow', [], [], ['HTTP_Trailburning_User_ID' => $user->getId()]);
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode());
        $this->assertJsonResponse($client);
        
        $em->flush();
        
        // check if user is following
        $isFollowing = false;
        foreach ($user->getCampaignsIFollow() as $campaignIFollow) {
            if ($campaignIFollow->getId() == $campaign->getId()) {
                $isFollowing = true;
                break;
            }
        }
        
        if ($isFollowing === true) {
            $this->fail('User is still following');
        }
    }
    
    /**
     *  Test if PUT /campaign/{id}#/unfollow dispatches the tb.user_unfollow event
     */
    public function testPutCampaignUnfollowDispatchesEvent()
    {   
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\CampaignData',
        ]);
        
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $user = $this->getUser('paultran');
        $campaign = $this->getCampaign('urbantrails-london');
                
        // create following user
        $user->addCampaignsIFollow($campaign);
        
        $em->persist($user);
        $em->flush();
        
        // set flag to false
        $this->eventDispatched = false;
        
        $client = $this->createClient();
        
        //  get the event dispatcher and add a listener for the tb.campaign_unfollow event
        $dispatcher = $client->getContainer()->get('event_dispatcher'); 
        $dispatcher->addListener('tb.campaign_unfollow', function ($event, $eventName, $dispatcher) {
            // this part gets executed when the event is dispatched
            $this->assertInstanceOf('TB\Bundle\FrontendBundle\Event\CampaignUnfollowEvent', $event,
                'The CampaignUnfollowEvent was created');
            $this->assertEquals('paultran', $event->getUser()->getName(), 
                'The unfollowing User was passed to the CampaignUnfollowEvent event');
            $this->assertEquals('London', $event->getCampaign()->getTitle(), 
                'The Campaign who gets unfollowed was passed to the CampaignUnfollowEvent event');
            
            // set flag to true, it means the event was dispatched
            $this->eventDispatched = true;
        });

        $crawler = $client->request('PUT', '/v1/campaign/' . $campaign->getId() . '/unfollow', [], [], ['HTTP_Trailburning_User_ID' => $user->getId()]);
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode());
        
        // test fails when the event gets not dispatched
        $this->assertTrue($this->eventDispatched, 'The tb.campaign_unfollow Event was successfully dispatched');

    }
    
}
