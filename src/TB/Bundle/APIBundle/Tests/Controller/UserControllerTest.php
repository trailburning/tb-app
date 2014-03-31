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
class UserControllerTest extends AbstractApiTestCase
{
    
    /**
     * Test the GET /route/{id} action
     */
    public function testPutUserFollow()
    {   
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\UserProfileData',
        ]);
        
        // Get Route from DB with the slug "grunewald"..
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $user = $em
            ->getRepository('TBFrontendBundle:User')
            ->findOneByName('mattallbeury');
        if (!$user) {
            $this->fail('Missing User with name "mattallbeury" in test DB');
        }
        
        $userToFollow = $em
            ->getRepository('TBFrontendBundle:User')
            ->findOneByName('paultran');
        if (!$userToFollow) {
            $this->fail('Missing User to follow with name "paultran" in test DB');
        }
        
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
        foreach ($user->getIfollow() as $iFollow) {
            if ($iFollow->getId() == $userToFollow->getId()) {
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
     * Test the GET /route/{id} action
     */
    public function testPutUserUnfollow()
    {   
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\UserProfileData',
        ]);
        
        // Get Route from DB with the slug "grunewald"..
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $user = $em
            ->getRepository('TBFrontendBundle:User')
            ->findOneByName('mattallbeury');
        if (!$user) {
            $this->fail('Missing User with name "mattallbeury" in test DB');
        }
        
        $userToUnfollow = $em
            ->getRepository('TBFrontendBundle:User')
            ->findOneByName('paultran');
        if (!$userToUnfollow) {
            $this->fail('Missing User to follow with name "paultran" in test DB');
        }
        
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
        $user->addIFollow($userToUnfollow);
        
        $em->persist($user);
        $em->flush();
        
        $client = $this->createClient();
        $crawler = $client->request('PUT', '/v1/user/' . $userToUnfollow->getId() . '/unfollow', [], [], ['HTTP_Trailburning_User_ID' => $user->getId()]);
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode());
        $this->assertJsonResponse($client);
        
        $em->flush();
        
        // check if user is following
        $isFollowing = false;
        foreach ($user->getIfollow() as $iFollow) {
            if ($iFollow->getId() == $userToUnfollow->getId()) {
                $isFollowing = true;
                break;
            }
        }
        
        if ($isFollowing === true) {
            $this->fail('User is still following');
        }
    }
    
}
