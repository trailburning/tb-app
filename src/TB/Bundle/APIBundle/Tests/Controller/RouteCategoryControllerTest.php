<?php

namespace TB\Bundle\APIBundle\Tests\Controller;

use TB\Bundle\APIBundle\Tests\AbstractApiTest;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\ApplicationTester;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 *
 */
class RouteCategoryControllerTest extends AbstractApiTest
{
    
    /**
     * Test the GET /route_category/list action
     */
    public function testGetList()
    {   
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteCategoryData',
        ]);
        
        // Get RouteCategory from DB with the name "Park"..
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $routeCategory = $em
            ->getRepository('TBFrontendBundle:RouteCategory')
            ->findOneByName('Park');
        if (!$routeCategory) {
            $this->fail('Missing RouteCategory with name "Park" in test DB');
        }
        
        // Get same Route from API
        $client = $this->createClient();
        $crawler = $client->request('GET', '/v1/route_category/list');
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode());
        $this->assertJsonResponse($client);   
    }
    
    

}
