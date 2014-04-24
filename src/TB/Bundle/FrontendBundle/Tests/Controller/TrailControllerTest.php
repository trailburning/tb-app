<?php

namespace TB\Bundle\FrontendBundle\Tests\Controller;

use TB\Bundle\FrontendBundle\Tests\AbstractFrontendTest;
use Symfony\Component\HttpFoundation\Response;

/**
 *
 */
class TrailControllerTest extends AbstractFrontendTest
{
    
    /**
     * Test Trail created by UserProfile, no Event, no Editorial
     */
    public function testTrailUser()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteData',
        ]);

        $client = $this->createClient();
        $crawler = $client->request('GET', '/trail/grunewald');
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0,
            $crawler->filter('h2.tb-title:contains("Grunewald")')->count());
        $this->assertGreaterThan(0,
            $crawler->filter('h2.tb-title:contains("Berlin")')->count());
        $this->assertGreaterThan(0,
            $crawler->filter('.author:contains("by Matt Allbeury")')->count());
    }
    
    /**
     * Test Trail created by BrandProfile, no Event, no Editorial
     */
    public function testTrailBrand()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteData',
        ]);

        $client = $this->createClient();
        $crawler = $client->request('GET', '/trail/ttm');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());   
        $this->assertGreaterThan(0,
            $crawler->filter('h2.tb-title:contains("Thames Trail Marathon")')->count());
        $this->assertGreaterThan(0,
            $crawler->filter('h2.tb-title:contains("Thames Festival of Running")')->count());
        $this->assertGreaterThan(0,
            $crawler->filter('.author:contains("by ashmei")')->count());
    }
    
    /**
     * Test Trail that doesn't exist
     */
    public function testTrail404()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteData',
        ]);
 
        $client = $this->createClient();
        $crawler = $client->request('GET', '/trail/noneexistent');
        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
    }
    
    public function testTrailmakerNewTrail()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteData',
        ]);
 
        $client = $this->createClient();
        $this->logIn($client, 'email@mattallbeury');
        $crawler = $client->request('GET', '/trailmaker');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }
    
    public function testTrailmakerEditTrail()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteData',
        ]);
        
        $route = $this->getRoute('grunewald');
        
 
        $client = $this->createClient();
        $this->logIn($client, 'email@mattallbeury');
        $crawler = $client->request('GET', '/trailmaker/' . $route->getId());
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }

    
    /**
     * Test Trail ID url redirect
     */
    public function testTrailId()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteData',
        ]);
        
        $route = $this->getRoute('grunewald');

        $client = $this->createClient();
        $crawler = $client->request('GET', '/trail/' . $route->getId());
        
        $this->assertTrue($client->getResponse()->isRedirect(), 
            'User is redirected to the slug-URL');

        $crawler = $client->followRedirect();
        
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0,
            $crawler->filter('h2.tb-title:contains("Grunewald")')->count());
        $this->assertGreaterThan(0,
            $crawler->filter('h2.tb-title:contains("Berlin")')->count());
    }
        
    public function testTrails()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/trails');
    }
}
