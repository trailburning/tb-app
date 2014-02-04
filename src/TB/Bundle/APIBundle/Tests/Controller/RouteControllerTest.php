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
class RouteControllerTest extends WebTestCase
{
    
    protected $environment = 'test_api';
    
    /**
     * 
     */
    public function testGetRouteAction()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteData',
        ]);
        
        $client = $this->createClient();
        $crawler = $client->request('GET', '/v1/route/1');
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode());    
    }
    

}
