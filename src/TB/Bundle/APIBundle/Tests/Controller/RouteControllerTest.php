<?php

namespace TB\Bundle\APIBundle\Tests\Controller;

use TB\Bundle\APIBundle\Tests\AbstractApiTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\ApplicationTester;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 *
 */
class RouteControllerTest extends AbstractApiTestCase
{
    
    /**
     * Test the PUT /route/{routeId}/like action
     */
    public function testPutRouteLike()
    {   
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteData',
        ]);
        
        // Get Route from DB with the slug "grunewald"..
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $user = $em
            ->getRepository('TBFrontendBundle:User')
            ->findOneByName('mattallbeury');
        if (!$user) {
            $this->fail('Missing User with name "mattallbeury" in test DB');
        }
        
        $route = $em
            ->getRepository('TBFrontendBundle:Route')
            ->findOneBySlug('grunewald');
        if (!$route) {
            $this->fail('Missing Route slug "grunewald" in test DB');
        }
        
        // Test Trailburning-User-ID not set
        $client = $this->createClient();
        $crawler = $client->request('PUT', '/v1/route/' . $route->getId() . '/like');
        $this->assertEquals(Response::HTTP_BAD_REQUEST,  $client->getResponse()->getStatusCode());
        $this->assertJsonResponse($client);
        
        // Test not existing user
        $crawler = $client->request('PUT', '/v1/route/' . $route->getId() . '/like', [], [], ['HTTP_Trailburning_User_ID' => 999999999]);
        $this->assertEquals(Response::HTTP_NOT_FOUND,  $client->getResponse()->getStatusCode());
        $this->assertJsonResponse($client);
                
        // Test not existing route to like
        $crawler = $client->request('PUT', '/v1/route/999999999/like', [], [], ['HTTP_Trailburning_User_ID' => $user->getId()]);
        $this->assertEquals(Response::HTTP_NOT_FOUND,  $client->getResponse()->getStatusCode());
        $this->assertJsonResponse($client);
        
        // Test route like
        $client = $this->createClient();
        $crawler = $client->request('PUT', '/v1/route/' . $route->getId() . '/like', [], [], ['HTTP_Trailburning_User_ID' => $user->getId()]);
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode());
        $this->assertJsonResponse($client);
        
        $em->flush();
        
        // check if user is following
        $isLiking = false;
        foreach ($route->getUserLikes() as $likingUser) {
            if ($likingUser->getId() == $user->getId()) {
                $isLiking = true;
                break;
            }
        }
        
        if ($isLiking === false) {
            $this->fail('User does not like the Route');
        }
        
        // Test user like already liking route
        $client = $this->createClient();
        $crawler = $client->request('PUT', '/v1/route/' . $route->getId() . '/like', [], [], ['HTTP_Trailburning_User_ID' => $user->getId()]);
        $this->assertEquals(Response::HTTP_BAD_REQUEST,  $client->getResponse()->getStatusCode());
        $this->assertJsonResponse($client);
    }
    
    /**
     * Test if PUT /route/{routeId}#/like dispatches the tb.route_like event
     */
    public function testPutRouteLikeDispatchesEvent()
    {   
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteData',
        ]);
        
        // Get Route from DB with the slug "grunewald"..
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $user = $em
            ->getRepository('TBFrontendBundle:User')
            ->findOneByName('mattallbeury');
        if (!$user) {
            $this->fail('Missing User with name "mattallbeury" in test DB');
        }
        
        $route = $em
            ->getRepository('TBFrontendBundle:Route')
            ->findOneBySlug('grunewald');
        if (!$route) {
            $this->fail('Missing Route with slug "grunewald" in test DB');
        }
        
        // set flag to false
        $this->eventDispatched = false;
        
        $client = $this->createClient();
        
        //  get the event dispatcher and add a listener for the tb.route_publish event
        $dispatcher = $client->getContainer()->get('event_dispatcher'); 
        $dispatcher->addListener('tb.route_like', function ($event, $eventName, $dispatcher) {
            // this part gets executed when the event is dispatched
            $this->assertInstanceOf('TB\Bundle\FrontendBundle\Event\RouteLikeEvent', $event,
                'The RouteLikeEvent was created');
            $this->assertEquals('grunewald', $event->getRoute()->getSlug(), 
                'The Route was passed to the RouteLikeEvent event');
            $this->assertEquals('mattallbeury', $event->getUser()->getName(), 
                'The User was passed to the RouteLikeEvent event');
            
            // set flag to true, it means the event was dispatched
            $this->eventDispatched = true;
        });
        
        // execute route like request
        $crawler = $client->request('PUT', '/v1/route/' . $route->getId() . '/like', [], [], ['HTTP_Trailburning_User_ID' => $user->getId()]);
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode());
        
        // test fails when the event gets not dispatched
        $this->assertTrue($this->eventDispatched, 'The tb.route_like Event was successfully dispatched');
    }
    
    /**
     * Test the PUT /route/{routeId}#/undolike action
     */
    public function testPutRouteUndoLike()
    {   
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteData',
        ]);
        
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $user = $em
            ->getRepository('TBFrontendBundle:User')
            ->findOneByName('mattallbeury');
        if (!$user) {
            $this->fail('Missing User with name "mattallbeury" in test DB');
        }
        
        $route = $em
            ->getRepository('TBFrontendBundle:Route')
            ->findOneBySlug('grunewald');
        if (!$route) {
            $this->fail('Missing Route with slug "grunewald" in test DB');
        }
        
        // Test Trailburning-User-ID not set
        $client = $this->createClient();
        $crawler = $client->request('PUT', '/v1/route/' . $route->getId() . '/undolike');
        $this->assertEquals(Response::HTTP_BAD_REQUEST,  $client->getResponse()->getStatusCode());
        $this->assertJsonResponse($client);
        
        // Test not existing user
        $crawler = $client->request('PUT', '/v1/route/' . $route->getId() . '/undolike', [], [], ['HTTP_Trailburning_User_ID' => 999999999]);
        $this->assertEquals(Response::HTTP_NOT_FOUND,  $client->getResponse()->getStatusCode());
        $this->assertJsonResponse($client);
        
        // Test not existing route to like
        $crawler = $client->request('PUT', '/v1/route/999999999/undolike', [], [], ['HTTP_Trailburning_User_ID' => $user->getId()]);
        $this->assertEquals(Response::HTTP_NOT_FOUND,  $client->getResponse()->getStatusCode());
        $this->assertJsonResponse($client);
        
        // Test route unfollow not existing like
        $client = $this->createClient();
        $crawler = $client->request('PUT', '/v1/route/' . $route->getId() . '/undolike', [], [], ['HTTP_Trailburning_User_ID' => $user->getId()]);
        $this->assertEquals(Response::HTTP_BAD_REQUEST,  $client->getResponse()->getStatusCode());
        $this->assertJsonResponse($client);
        
        // Test route undolike
        $route->addUserLike($user);
        
        $em->persist($route);
        $em->flush();
        
        $client = $this->createClient();
        $crawler = $client->request('PUT', '/v1/route/' . $route->getId() . '/undolike', [], [], ['HTTP_Trailburning_User_ID' => $user->getId()]);
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode());
        $this->assertJsonResponse($client);
        
        $em->flush();
        
        // check if user is following
        $isLiking = false;
        foreach ($route->getUserLikes() as $likingUser) {
            if ($likingUser->getId() == $user->getId()) {
                $isLiking = true;
                break;
            }
        }
        
        if ($isLiking === true) {
            $this->fail('User is still liking Route');
        }
    }
    
    /**
     *  Test if PUT /route/{routeId}#/undolike dispatches the tb.route_undolike event
     */
    public function testPutUserUnfollowDispatchesEvent()
    {   
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteData',
        ]);
        
        // Get Route from DB with the slug "grunewald"..
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $user = $em
            ->getRepository('TBFrontendBundle:User')
            ->findOneByName('mattallbeury');
        if (!$user) {
            $this->fail('Missing User with name "mattallbeury" in test DB');
        }
        
        $route = $em
            ->getRepository('TBFrontendBundle:Route')
            ->findOneBySlug('grunewald');
        if (!$route) {
            $this->fail('Missing Route to like with slug "grunewald" in test DB');
        }
                
        // create following user
        $route->addUserLike($user);
        
        $em->persist($route);
        $em->flush();
        
        // set flag to false
        $this->eventDispatched = false;
        
        $client = $this->createClient();
        
        //  get the event dispatcher and add a listener for the tb.route_undolike event
        $dispatcher = $client->getContainer()->get('event_dispatcher'); 
        $dispatcher->addListener('tb.route_undolike', function ($event, $eventName, $dispatcher) {
            // this part gets executed when the event is dispatched
            $this->assertInstanceOf('TB\Bundle\FrontendBundle\Event\RouteUndoLikeEvent', $event,
                'The RouteUndoLikeEvent was created');
            $this->assertEquals('grunewald', $event->getRoute()->getSlug(), 
                'The unfollowing User was passed to the UserUnfollowEvent event');
            $this->assertEquals('mattallbeury', $event->getUser()->getName(), 
                'The User was passed to the RouteUndoLikeEvent event');
            
            // set flag to true, it means the event was dispatched
            $this->eventDispatched = true;
        });

        $crawler = $client->request('PUT', '/v1/route/' . $route->getId() . '/undolike', [], [], ['HTTP_Trailburning_User_ID' => $user->getId()]);
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode());
        
        // test fails when the event gets not dispatched
        $this->assertTrue($this->eventDispatched, 'The tb.route_undolike Event was successfully dispatched');

    }
    
    /**
     * Test the GET /route/{id} action
     */
    public function testGetRoute()
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
        
        // Get same Route from API
        $client = $this->createClient();
        $crawler = $client->request('GET', '/v1/route/' . $route->getId());
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode());
        
        $this->assertJsonResponse($client);
    }
    
    public function testGetRoute404()
    {
        $this->loadFixtures([]);
        // Check if Route exists
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $route = $em
            ->getRepository('TBFrontendBundle:Route')
            ->findOneById(1);

        if ($route !== null) {
            $this->fail('Route with id "1" exists in test DB');
        }
        
        // Get Route from the API that does not exist
        $client = $this->createClient();
        $crawler = $client->request('GET', '/v1/route/1');
        // Check HTTP Status Code
        $this->assertEquals(Response::HTTP_NOT_FOUND,  $client->getResponse()->getStatusCode());
        // Verify JSON Response
        $this->assertJsonResponse($client);
        // Check user message
        $jsonObj = json_decode($client->getResponse()->getContent());
        $this->assertEquals('Route with id "1" does not exist', $jsonObj->usermsg);
        
        $this->assertJsonResponse($client);
    }
    
    /**
     * Test the DELETE /route/{id} action
     */
    public function testDeleteRoute()
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
        
        // Delete that Route per API
        $client = $this->createClient();
        $crawler = $client->request('DELETE', '/v1/route/' . $route->getId());
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode());

        $this->assertJsonResponse($client);

        // Verify that if Route was deleted from the DB
        $route = $em
            ->getRepository('TBFrontendBundle:Route')
            ->findOneBySlug('grunewald');

        if ($route) {
            $this->fail('Route was not deleted');
        }
    }
    
    public function testDeleteRoute404()
    {
        $this->loadFixtures([]);
        // Check if Route exists
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $route = $em
            ->getRepository('TBFrontendBundle:Route')
            ->findOneById(1);

        if ($route !== null) {
            $this->fail('Route with id "1" exists in test DB');
        }
        
        // Delete Route from the API that does not exist
        $client = $this->createClient();
        $crawler = $client->request('DELETE', '/v1/route/1');
        // Check HTTP Status Code
        $this->assertEquals(Response::HTTP_NOT_FOUND,  $client->getResponse()->getStatusCode());
        // Verify JSON Response
        $this->assertJsonResponse($client); 
        // Check user message
        $jsonObj = json_decode($client->getResponse()->getContent());
        $this->assertEquals('Failed to delete non existing route with id "1"', $jsonObj->usermsg);
        
        $this->assertJsonResponse($client);
    }
    
    /**
     * Test the GET /route/user/{id} action
     */
    public function testGetRoutesByUser()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteData',
        ]);
        
        // Get User from DB with the slug "mattallbeury"..
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $user = $em
            ->getRepository('TBFrontendBundle:User')
            ->findOneByName('mattallbeury');
        
        if (!$user) {
            $this->fail('Missing User with name "mattallbeury" in test DB');
        }
        
        $client = $this->createClient();
        $crawler = $client->request('GET', '/v1/routes/user/' . $user->getId());
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode());
          
        $this->assertJsonResponse($client);
    }
    
    /**
     * Test 404 Response for not existing User
     */
    public function testGetRoutesByUser404()
    {
        $this->loadFixtures([]);
        
        // Check if User exists
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $user = $em
            ->getRepository('TBFrontendBundle:User')
            ->findOneById(1);
        
        if ($user !== null) {
            $this->fail('User with id "1" exists in test DB');
        }
        
        $client = $this->createClient();
        $crawler = $client->request('GET', '/v1/routes/user/1');

        // Check HTTP Status Code
        $this->assertEquals(Response::HTTP_NOT_FOUND,  $client->getResponse()->getStatusCode());
        // Verify JSON Response
        $this->assertJsonResponse($client); 
        // Check user message
        $jsonObj = json_decode($client->getResponse()->getContent());
        $this->assertEquals('User with id "1" does not exist', $jsonObj->usermsg);
        
        $this->assertJsonResponse($client);
    }
    
    /**
     * Test the PUT /route/{id} action
     */
    public function testPutRoute()
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
        
        // update some fields
        $obj = new \stdClass();
        $obj->name = 'updated name';
        $obj->region = 'updated region';
        $json = json_encode($obj);
        
        // Get same Route from API
        $client = $this->createClient();
        $crawler = $client->request('PUT', '/v1/route/1', array('json' => $json));
        
        $this->assertJsonResponse($client);
    }
    
    /**
     * Test the GET /route/my action
     */
    public function testGetRoutesByAuthenticatedUser()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteData',
        ]);
        
        // Get User from DB with the slug "mattallbeury"..
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $user = $em
            ->getRepository('TBFrontendBundle:User')
            ->findOneByName('mattallbeury');
        
        if (!$user) {
            $this->fail('Missing User with name "mattallbeury" in test DB');
        }
        
        $client = $this->createClient();
        $crawler = $client->request('GET', '/v1/routes/my', [], [], ['HTTP_Trailburning_User_ID' => $user->getId()]);
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode());
          
        $this->assertJsonResponse($client);
    }

    /**
     * Test the GET /route/my action without Authentification
     */
    public function testGetRoutesByAuthenticatedUserNoHeader()
    {
        $this->loadFixtures([]);
                
        $client = $this->createClient();
        $crawler = $client->request('GET', '/v1/routes/my');
        $this->assertEquals(Response::HTTP_BAD_REQUEST,  $client->getResponse()->getStatusCode(), 
            'Response returns Status Code 400');
          
        $this->assertJsonResponse($client);  
    }
    
    /**
     * Test the GET /routes/search action
     */
    public function testGetSearchRoutes()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteData',
        ]);
        
        $client = $this->createClient();
        $crawler = $client->request('GET', '/v1/routes/search');
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode());
          
        $this->assertJsonResponse($client);
    }

}
