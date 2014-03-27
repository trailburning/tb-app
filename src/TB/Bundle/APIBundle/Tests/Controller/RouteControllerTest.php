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
class RouteControllerTest extends AbstractApiTestCase
{
    
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
        $response = $client->getResponse()->getContent();
        $this->assertTrue($this->isValidJson($response)); 
        // Check user message
        $jsonObj = json_decode($response);
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
        $response = $client->getResponse()->getContent();
        $this->assertTrue($this->isValidJson($response)); 
        // Check user message
        $jsonObj = json_decode($response);
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
        $response = $client->getResponse()->getContent();
        $this->assertTrue($this->isValidJson($response)); 
        // Check user message
        $jsonObj = json_decode($response);
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

}
