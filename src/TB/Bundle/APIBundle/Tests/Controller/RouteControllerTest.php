<?php

namespace TB\Bundle\APIBundle\Tests\Controller;

use TB\Bundle\APIBundle\Tests\AbstractApiTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\ApplicationTester;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use TB\Bundle\FrontendBundle\Entity\RouteLike;
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
        
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $user = $this->getUser('mattallbeury');
        $route = $this->getRoute('grunewald');
        
        // Test Trailburning-User-ID not set
        $client = $this->createClient();
        $client->request('PUT', '/v1/route/' . $route->getId() . '/like');
        $this->assertEquals(Response::HTTP_BAD_REQUEST,  $client->getResponse()->getStatusCode());
        $this->assertJsonResponse($client);
        
        // Test not existing user
        $client->request('PUT', '/v1/route/' . $route->getId() . '/like', [], [], ['HTTP_Trailburning_User_ID' => 999999999]);
        $this->assertEquals(Response::HTTP_NOT_FOUND,  $client->getResponse()->getStatusCode());
        $this->assertJsonResponse($client);
                
        // Test not existing route to like
        $client->request('PUT', '/v1/route/999999999/like', [], [], ['HTTP_Trailburning_User_ID' => $user->getId()]);
        $this->assertEquals(Response::HTTP_NOT_FOUND,  $client->getResponse()->getStatusCode());
        $this->assertJsonResponse($client);
        
        // Test route like
        $client = $this->createClient();
        $client->request('PUT', '/v1/route/' . $route->getId() . '/like', [], [], ['HTTP_Trailburning_User_ID' => $user->getId()]);
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode());
        $this->assertJsonResponse($client);
        
        $em->flush();
        
        // check if user is following
        $isLiking = false;
        foreach ($route->getRouteLikes() as $routeLike) {
            if ($routeLike->getUserId() == $user->getId()) {
                $isLiking = true;
                break;
            }
        }
        
        if ($isLiking === false) {
            $this->fail('User does not like the Route');
        }
        
        // Test user like already liking route
        $client = $this->createClient();
        $client->request('PUT', '/v1/route/' . $route->getId() . '/like', [], [], ['HTTP_Trailburning_User_ID' => $user->getId()]);
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
        
        $user = $this->getUser('mattallbeury');
        $route = $this->getRoute('grunewald');
        
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
        $client->request('PUT', '/v1/route/' . $route->getId() . '/like', [], [], ['HTTP_Trailburning_User_ID' => $user->getId()]);
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
        $user = $this->getUser('mattallbeury');
        $route = $this->getRoute('grunewald');
        
        // Test Trailburning-User-ID not set
        $client = $this->createClient();
        $client->request('PUT', '/v1/route/' . $route->getId() . '/undolike');
        $this->assertEquals(Response::HTTP_BAD_REQUEST,  $client->getResponse()->getStatusCode());
        $this->assertJsonResponse($client);
        
        // Test not existing user
        $client->request('PUT', '/v1/route/' . $route->getId() . '/undolike', [], [], ['HTTP_Trailburning_User_ID' => 999999999]);
        $this->assertEquals(Response::HTTP_NOT_FOUND,  $client->getResponse()->getStatusCode());
        $this->assertJsonResponse($client);
        
        // Test not existing route to like
        $client->request('PUT', '/v1/route/999999999/undolike', [], [], ['HTTP_Trailburning_User_ID' => $user->getId()]);
        $this->assertEquals(Response::HTTP_NOT_FOUND,  $client->getResponse()->getStatusCode());
        $this->assertJsonResponse($client);
        
        // Test route unfollow not existing like
        $client = $this->createClient();
        $client->request('PUT', '/v1/route/' . $route->getId() . '/undolike', [], [], ['HTTP_Trailburning_User_ID' => $user->getId()]);
        $this->assertEquals(Response::HTTP_BAD_REQUEST,  $client->getResponse()->getStatusCode());
        $this->assertJsonResponse($client);
        
        // Test route undolike
        $routeLike = new RouteLike();
        $routeLike->setRoute($route);
        $routeLike->setUser($user);
        
        $em->persist($routeLike);
        $em->flush();
        
        $client = $this->createClient();
        $client->request('PUT', '/v1/route/' . $route->getId() . '/undolike', [], [], ['HTTP_Trailburning_User_ID' => $user->getId()]);
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode());
        $this->assertJsonResponse($client);
        
        $em->flush();
        
        // check if user is following
        $isLiking = false;
        foreach ($route->getRouteLikes() as $routeLike) {
            if ($routeLike->getUserId() == $user->getId()) {
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
        
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $user = $this->getUser('mattallbeury');
        $route = $this->getRoute('grunewald');
                
        // create following user
        $routeLike = new RouteLike();
        $routeLike->setRoute($route);
        $routeLike->setUser($user);
        
        $em->persist($routeLike);
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

        $client->request('PUT', '/v1/route/' . $route->getId() . '/undolike', [], [], ['HTTP_Trailburning_User_ID' => $user->getId()]);
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
        
        $route = $this->getRoute('grunewald');
        
        // Get same Route from API
        $client = $this->createClient();
        $client->request('GET', '/v1/route/' . $route->getId());
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
        $client->request('GET', '/v1/route/1');
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
        $client->request('DELETE', '/v1/route/' . $route->getId());
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
        $client->request('DELETE', '/v1/route/1');
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
        
        $user = $this->getUser('mattallbeury');
        
        $client = $this->createClient();
        $client->request('GET', '/v1/routes/user/' . $user->getId());
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
        $client->request('GET', '/v1/routes/user/1');

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
        $client->request('PUT', '/v1/route/1', array('json' => $json));
        
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
        
        $user = $this->getUser('mattallbeury');
        
        $client = $this->createClient();
        $client->request('GET', '/v1/routes/my', [], [], ['HTTP_Trailburning_User_ID' => $user->getId()]);
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
        $client->request('GET', '/v1/routes/my');
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
        $client->request('GET', '/v1/routes/search');
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode());  
        $this->assertJsonResponse($client);
        
        
        // Limit search to a radius around a point
        $params = ['radius' => 20, 'long' => 13.2, 'lat' => 52.5];          
        $query = [];
        foreach ($params as $key => $value) {
            $query[] = $key . '=' . $value;    
        }
        $client->request('GET', '/v1/routes/search?' . implode('&', $query));
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode());  
        $this->assertJsonResponse($client);
            
            
        // Order results nearest to a point
        $params = ['order' => 'distance', 'long' => 13.2, 'lat' => 52.5];
        $query = [];
        foreach ($params as $key => $value) {
            $query[] = $key . '=' . $value;    
        }
        $client->request('GET', '/v1/routes/search?' . implode('&', $query));
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode());  
        $this->assertJsonResponse($client);
    }
    
    /**
     * Test the PUT /route/{routeId}/attribute/{attributeId} action
     */
    public function testPutRouteAttribute()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteData',
        ]);
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        
        $route = $this->getRoute('grunewald');
        $attribute = $this->getAttribute('cycle', 'activity');
        
        $hasNewAttribute = false;
        foreach ($route->getAttributes() as $routeAttribute) {
            if ($attribute->getId() == $routeAttribute->getId()) {
                $hasNewAttribute = true;
                break;
            }
        }
        $this->assertFalse($hasNewAttribute, 'The Route does not contain the Attribute to add');
        
        $client = $this->createClient();
        $client->request('PUT', '/v1/route/' . $route->getId() . '/attribute/' . $attribute->getId());
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode());
        $this->assertJsonResponse($client);
        
        $em->refresh($route);
        
        $hasNewAttribute = false;
        foreach ($route->getAttributes() as $routeAttribute) {
            if ($attribute->getId() == $routeAttribute->getId()) {
                $hasNewAttribute = true;
                break;
            }
        }
        $this->assertTrue($hasNewAttribute, 'The Attribute was added to the Route');
        
        // Test that adding existing Attribute returns no error
        $client->request('PUT', '/v1/route/' . $route->getId() . '/attribute/' . $attribute->getId());
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode());
    }
    
    /**
     * Test the DELETE /route/{routeId}/attribute/{attributeId} action
     */
    public function testDeleteRouteAttribute()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteData',
        ]);
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        
        $route = $this->getRoute('grunewald');
        $attribute = $this->getAttribute('run', 'activity');
        
        $hasAttribute = false;
        foreach ($route->getAttributes() as $routeAttribute) {
            if ($attribute->getId() == $routeAttribute->getId()) {
                $hasAttribute = true;
                break;
            }
        }
        $this->assertTrue($hasAttribute, 'The Route contains the Attribute to delete');
        
        $client = $this->createClient();
        $crawler = $client->request('DELETE', '/v1/route/' . $route->getId() . '/attribute/' . $attribute->getId());
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode());
        $this->assertJsonResponse($client);
        
        $em->refresh($route);
        
        $hasAttribute = false;
        foreach ($route->getAttributes() as $routeAttribute) {
            if ($attribute->getId() == $routeAttribute->getId()) {
                $hasAttribute = true;
                break;
            }
        }
        $this->assertFalse($hasAttribute, 'The Attribute was deleted from the Route');
        
        // Test that removing not associated Attribute returns no error
        $client = $this->createClient();
        $client->request('DELETE', '/v1/route/' . $route->getId() . '/attribute/' . $attribute->getId());
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode());
    }
    
    /**
     * Test the GET /route/{routeId}/related action
     */
    public function testGetRelatedRoutes()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteData',
        ]);
        
        $route = $this->getRoute('grunewald');
        
        $client = $this->createClient();
        $client->request('GET', '/v1/route/' . $route->getId() . '/related');
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode());
          
        $this->assertJsonResponse($client);
    }
    
    /**
     * Test the GET /route/{routeId}/related/campaigns action
     */
    public function testGetRelatedCampaigns()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\CampaignData',
        ]);
        
        $route = $this->getRoute('london');
        
        $client = $this->createClient();
        $client->request('GET', '/v1/route/' . $route->getId() . '/related/campaigns');
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode());
          
        $this->assertJsonResponse($client);
    }
    
}
