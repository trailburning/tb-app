<?php

namespace TB\Bundle\FrontendBundle\Tests\Controller;

use TB\Bundle\FrontendBundle\Tests\AbstractFrontendTest;
use Symfony\Component\HttpFoundation\Response;

class ProfileControllerTest extends AbstractFrontendTest
{
    
    public function testUserProfile()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteData',
        ]);

        $client = $this->createClient();
        $crawler = $client->request('GET', '/profile/mattallbeury');
        // echo $client->getResponse()->getContent();
        // exit;
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());   
        $this->assertGreaterThan(0,
            $crawler->filter('h2.tb-title:contains("Matt")')->count());
        $this->assertGreaterThan(0,
            $crawler->filter('h2.tb-title:contains("Allbeury")')->count());
    }
    
    public function testBrandProfile()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteData',
        ]);

        $client = $this->createClient();
        $crawler = $client->request('GET', '/profile/ashmei');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());   
    }
}
