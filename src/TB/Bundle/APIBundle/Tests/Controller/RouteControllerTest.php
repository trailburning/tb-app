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
        $response = $client->getResponse()->getContent();
        $this->assertTrue($this->isValidJson($response));   
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
        $response = $client->getResponse()->getContent();
        $this->assertTrue($this->isValidJson($response));

        // Verify that if Route was deleted from the DB
        $route = $em
            ->getRepository('TBFrontendBundle:Route')
            ->findOneBySlug('grunewald');

        if ($route) {
            $this->fail('Route was not deleted');
        }
    }
    
    /**
     * Test the DELETE /route/{id} action
     */
    public function testGetRoutesByUser()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteData',
        ]);
        
        // Get Route from DB with the slug "mattallbeury"..
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $user = $em
            ->getRepository('TBFrontendBundle:User')
            ->findOneByName('mattallbeury');
        
        if (!$user) {
            $this->fail('Missing User with name "mattallbeury" in test DB');
        }
        
        
        // Delete that Route per API
        $client = $this->createClient();
        $crawler = $client->request('GET', '/v1/routes/user/' . $user->getId());
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode());  
        $response = $client->getResponse()->getContent();
        $this->assertTrue($this->isValidJson($response));
    }

}
