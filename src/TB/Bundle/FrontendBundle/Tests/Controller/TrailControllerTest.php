<?php

namespace TB\Bundle\FrontendBundle\Tests\Controller;

use Liip\FunctionalTestBundle\Test\WebTestCase;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\ApplicationTester;
use Symfony\Component\Console\Output\Output;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;

/**
 *
 */
class TrailControllerTest extends WebTestCase
{
    /**
     *
     */
    public function testTrail()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteData',
        ]);

        $client = $this->createClient();
        $crawler = $client->request('GET', '/trail/ttm');
        
    }

}
