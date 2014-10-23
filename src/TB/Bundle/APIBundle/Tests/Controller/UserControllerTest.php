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
class UserControllerTest extends AbstractApiTestCase
{
    
    /**
     * Test the PUT /user/{id}#/follow action
     */
    public function testPutUserFollow()
    {   
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\UserProfileData',
        ]);
        
        // Get Route from DB with the slug "grunewald"..
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $user = $this->getUser('mattallbeury');
        
        $userToFollow = $this->getUser('paultran');
        
        // Test Trailburning-User-ID not set
        $client = $this->createClient();
        $crawler = $client->request('PUT', '/v1/user/' . $userToFollow->getId() . '/follow');
        $this->assertEquals(Response::HTTP_BAD_REQUEST,  $client->getResponse()->getStatusCode());
        $this->assertJsonResponse($client);
        
        // Test not existing user
        $crawler = $client->request('PUT', '/v1/user/' . $userToFollow->getId() . '/follow', [], [], ['HTTP_Trailburning_User_ID' => 999999999]);
        $this->assertEquals(Response::HTTP_NOT_FOUND,  $client->getResponse()->getStatusCode());
        $this->assertJsonResponse($client);
        
        // Test user not allowed to follow itself
        $crawler = $client->request('PUT', '/v1/user/' . $userToFollow->getId() . '/follow', [], [], ['HTTP_Trailburning_User_ID' => $userToFollow->getId()]);
        $this->assertEquals(Response::HTTP_BAD_REQUEST,  $client->getResponse()->getStatusCode());
        $this->assertJsonResponse($client);
        
        // Test not existing user to follow
        $crawler = $client->request('PUT', '/v1/user/999999999/follow', [], [], ['HTTP_Trailburning_User_ID' => $user->getId()]);
        $this->assertEquals(Response::HTTP_NOT_FOUND,  $client->getResponse()->getStatusCode());
        $this->assertJsonResponse($client);
        
        // Test user follow
        $client = $this->createClient();
        $crawler = $client->request('PUT', '/v1/user/' . $userToFollow->getId() . '/follow', [], [], ['HTTP_Trailburning_User_ID' => $user->getId()]);
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode());
        $this->assertJsonResponse($client);
        
        $em->flush();
        
        // check if user is following
        $isFollowing = false;
        foreach ($user->getUserIFollow() as $userIFollow) {
            if ($userIFollow->getId() == $userToFollow->getId()) {
                $isFollowing = true;
                break;
            }
        }
        
        if ($isFollowing === false) {
            $this->fail('User is not following');
        }
        
        // Test user follow existing follower
        $client = $this->createClient();
        $crawler = $client->request('PUT', '/v1/user/' . $userToFollow->getId() . '/follow', [], [], ['HTTP_Trailburning_User_ID' => $user->getId()]);
        $this->assertEquals(Response::HTTP_BAD_REQUEST,  $client->getResponse()->getStatusCode());
        $this->assertJsonResponse($client);
    }
    
    /**
     * Test if PUT /user/{id}#/follow dispatches the tb.user_follow event
     */
    public function testPutUserFollowDispatchesEvent()
    {   
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\UserProfileData',
        ]);
        
        $user = $this->getUser('mattallbeury');
        $userToFollow = $this->getUser('paultran');
        
        // set flag to false
        $this->eventDispatched = false;
        
        $client = $this->createClient();
        
        //  get the event dispatcher and add a listener for the tb.user_follow event
        $dispatcher = $client->getContainer()->get('event_dispatcher'); 
        $dispatcher->addListener('tb.user_follow', function ($event, $eventName, $dispatcher) {
            // this part gets executed when the event is dispatched
            $this->assertInstanceOf('TB\Bundle\FrontendBundle\Event\UserFollowEvent', $event,
                'The UserFollowEvent was created');
            $this->assertEquals('mattallbeury', $event->getFollowingUser()->getName(), 
                'The following User was passed to the UserFollowEvent event');
            $this->assertEquals('paultran', $event->getFollowedUser()->getName(), 
                'The User who gets followed was passed to the UserFollowEvent event');
            
            // set flag to true, it means the event was dispatched
            $this->eventDispatched = true;
        });
        
        // execute user follow request
        $crawler = $client->request('PUT', '/v1/user/' . $userToFollow->getId() . '/follow', [], [], ['HTTP_Trailburning_User_ID' => $user->getId()]);
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode());
        
        // test fails when the event gets not dispatched
        $this->assertTrue($this->eventDispatched, 'The tb.user_follow Event was successfully dispatched');
    }
    
    /**
     * Test the PUT /user/{id}#/unfollow action
     */
    public function testPutUserUnfollow()
    {   
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\UserProfileData',
        ]);
        
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $user = $this->getUser('mattallbeury');
        $userToUnfollow = $this->getUser('paultran');
        
        // Test Trailburning-User-ID not set
        $client = $this->createClient();
        $crawler = $client->request('PUT', '/v1/user/' . $userToUnfollow->getId() . '/unfollow');
        $this->assertEquals(Response::HTTP_BAD_REQUEST,  $client->getResponse()->getStatusCode());
        $this->assertJsonResponse($client);
        
        // Test not existing user
        $crawler = $client->request('PUT', '/v1/user/' . $userToUnfollow->getId() . '/unfollow', [], [], ['HTTP_Trailburning_User_ID' => 999999999]);
        $this->assertEquals(Response::HTTP_NOT_FOUND,  $client->getResponse()->getStatusCode());
        $this->assertJsonResponse($client);
        
        // Test user not allowed to unfollow itself
        $crawler = $client->request('PUT', '/v1/user/' . $userToUnfollow->getId() . '/unfollow', [], [], ['HTTP_Trailburning_User_ID' => $userToUnfollow->getId()]);
        $this->assertEquals(Response::HTTP_BAD_REQUEST,  $client->getResponse()->getStatusCode());
        $this->assertJsonResponse($client);
        
        // Test not existing user to unfollow
        $crawler = $client->request('PUT', '/v1/user/999999999/unfollow', [], [], ['HTTP_Trailburning_User_ID' => $user->getId()]);
        $this->assertEquals(Response::HTTP_NOT_FOUND,  $client->getResponse()->getStatusCode());
        $this->assertJsonResponse($client);
        
        // Test user unfollow not existing follower
        $client = $this->createClient();
        $crawler = $client->request('PUT', '/v1/user/' . $userToUnfollow->getId() . '/unfollow', [], [], ['HTTP_Trailburning_User_ID' => $user->getId()]);
        $this->assertEquals(Response::HTTP_BAD_REQUEST,  $client->getResponse()->getStatusCode());
        $this->assertJsonResponse($client);
        
        // Test user unfollow
        $user->addUserIFollow($userToUnfollow);
        
        $em->persist($user);
        $em->flush();
        
        $client = $this->createClient();
        $crawler = $client->request('PUT', '/v1/user/' . $userToUnfollow->getId() . '/unfollow', [], [], ['HTTP_Trailburning_User_ID' => $user->getId()]);
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode());
        $this->assertJsonResponse($client);
        
        $em->flush();
        
        // check if user is following
        $isFollowing = false;
        foreach ($user->getUserIFollow() as $userIFollow) {
            if ($userIFollow->getId() == $userToUnfollow->getId()) {
                $isFollowing = true;
                break;
            }
        }
        
        if ($isFollowing === true) {
            $this->fail('User is still following');
        }
    }
    
    /**
     *  Test if PUT /user/{id}#/unfollow dispatches the tb.user_unfollow event
     */
    public function testPutUserUnfollowDispatchesEvent()
    {   
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\UserProfileData',
        ]);
        
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $user = $this->getUser('mattallbeury');
        $userToUnfollow = $this->getUser('paultran');
                
        // create following user
        $user->addUserIFollow($userToUnfollow);
        
        $em->persist($user);
        $em->flush();
        
        // set flag to false
        $this->eventDispatched = false;
        
        $client = $this->createClient();
        
        //  get the event dispatcher and add a listener for the tb.user_unfollow event
        $dispatcher = $client->getContainer()->get('event_dispatcher'); 
        $dispatcher->addListener('tb.user_unfollow', function ($event, $eventName, $dispatcher) {
            // this part gets executed when the event is dispatched
            $this->assertInstanceOf('TB\Bundle\FrontendBundle\Event\UserUnfollowEvent', $event,
                'The UserUnfollowEvent was created');
            $this->assertEquals('mattallbeury', $event->getUnfollowingUser()->getName(), 
                'The unfollowing User was passed to the UserUnfollowEvent event');
            $this->assertEquals('paultran', $event->getUnfollowedUser()->getName(), 
                'The User who gets unfollowed was passed to the UserUnfollowEvent event');
            
            // set flag to true, it means the event was dispatched
            $this->eventDispatched = true;
        });

        $crawler = $client->request('PUT', '/v1/user/' . $userToUnfollow->getId() . '/unfollow', [], [], ['HTTP_Trailburning_User_ID' => $user->getId()]);
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode());
        
        // test fails when the event gets not dispatched
        $this->assertTrue($this->eventDispatched, 'The tb.user_unfollow Event was successfully dispatched');

    }
        
    /**
     * Test the PUT /user/{id}#/follow action
     */
    public function testPutUserActivityViewd()
    {   
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\UserProfileData',
        ]);
        
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $user = $this->getUser('mattallbeury');

        $this->assertEquals(null, $user->getActivityLastViewed(),
            'The activityLastViewed field is empty');
        
        // Test Trailburning-User-ID not set
        $client = $this->createClient();
        $crawler = $client->request('PUT', '/v1/user/activity/viewed');
        $this->assertEquals(Response::HTTP_BAD_REQUEST,  $client->getResponse()->getStatusCode());
        $this->assertJsonResponse($client);
        
        // Test user follow
        $client = $this->createClient();
        $crawler = $client->request('PUT', '/v1/user/activity/viewed', [], [], ['HTTP_Trailburning_User_ID' => $user->getId()]);
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode());
        $this->assertJsonResponse($client);
        
        $em->refresh($user);
        
        $this->assertNotNull($user->getActivityLastViewed(),
            'The activityLastViewed field was set to NOW');
    }    
    
}
