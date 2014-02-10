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
class RouteTypeControllerTest extends AbstractApiTestCase
{
    
    /**
     * Test the GET /route_category/list action
     */
    public function testGetList()
    {   
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteTypeData',
        ]);
        
        // Get RouteType from DB with the name "Park"..
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $routeType = $em
            ->getRepository('TBFrontendBundle:RouteType')
            ->findOneByName('Marathon');
        if (!$routeType) {
            $this->fail('Missing RouteType with name "Marathon" in test DB');
        }
        
        // Get same Route from API
        $client = $this->createClient();
        $crawler = $client->request('GET', '/v1/route_type/list');
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode());  
        $response = $client->getResponse()->getContent();
        $this->assertTrue($this->isValidJson($response));   
    }
    
    

}
