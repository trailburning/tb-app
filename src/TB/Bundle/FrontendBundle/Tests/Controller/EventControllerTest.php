<?php

namespace TB\Bundle\FrontendBundle\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;

class EventControllerTest extends BaseFrontendTest
{
    
    protected static function getKernelClass()
    {
        require_once self::getPhpUnitXmlDir() . '/frontend/AppKernel.php';

        return 'AppKernel';
    }
    
    public function testEvent()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\EventData',
        ]);
        
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $event = $em
            ->getRepository('TBFrontendBundle:Event')
            ->findOneBySlug('eiger');

        if (!$event) {
            throw $this->createNotFoundException(
                sprintf('Event with slug eiger not found in Test DB')
            );
        }
        
        $client = static::createClient();
        $crawler = $client->request('GET', '/event/' . $event->getSlug());
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode());
    }

    public function testEvents()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\EventData',
        ]);
        
        $client = static::createClient();
        $crawler = $client->request('GET', '/events');
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode());
    }
}
