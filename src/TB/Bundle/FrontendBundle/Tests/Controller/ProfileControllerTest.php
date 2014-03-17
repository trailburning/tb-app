<?php

namespace TB\Bundle\FrontendBundle\Tests\Controller;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ProfileControllerTest extends WebTestCase
{
    protected static function getKernelClass()
    {
        require_once self::getPhpUnitXmlDir() . '/frontend/AppKernel.php';

        return 'AppKernel';
    }
    
    public function testUserProfile()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteData',
        ]);

        $client = $this->createClient();
        $crawler = $client->request('GET', '/profile/mattallbeury');
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
