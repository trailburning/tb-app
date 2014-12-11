<?php

namespace TB\Bundle\APIBundle\Tests;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\ApplicationTester;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;

/**
 *
 */
abstract class AbstractApiTest extends WebTestCase
{

    protected function setUp()
    {
        // replace the Bitly CLient with a Mock
        $bitly = $this->getMockBuilder('Hpatoio\Bitly\Client')
            ->disableOriginalConstructor()
            ->getMock();
        
        $bitly->method('__call')->willReturn([
            'long_url' => 'http://www.trailburning.com/trail/grunewald',
            'url' => 'http://bit.ly/15LTaFB',
            'hash' => '15LTaFB',
            'global_hash' => '15LTaFC',
            'new_hash' => 0,
        ]);
        
        $this->getContainer()->set('tb.bitly_client', $bitly);
        
        // Replace the RabbitMQ Producer Service with a Mock
        $producer = $this->getMockBuilder('OldSound\RabbitMqBundle\RabbitMq\Producer')
            ->disableOriginalConstructor()
            ->getMock();
        // Test that the publish() method gets called three times, two times when two Routes are created from fixtures,
        // and once when the tb.route_publish Event is fired manually in this test
        $producer->expects($this->any())
            ->method('publish')
            ->willReturn(true);
        $this->getContainer()->set('old_sound_rabbit_mq.main_producer', $producer);
        
        // Replace the Mailproxy Service with a Mock
        $mailproxy = $this->getMockBuilder('TB\Bundle\FrontendBundle\Service\Mailproxy')
            ->disableOriginalConstructor()
            ->getMock();
        $mailproxy->method('addNewsletterSubscriber')->willReturn(true);
        $mailproxy->method('removeNewsletterSubscriber')->willReturn(true);
        $mailproxy->method('sendWelcomeMail')->willReturn(true);
        $this->getContainer()->set('tb.mailproxy', $mailproxy);
        
        // Replace the Timezone Service with a Mock
        $timezone = $this->getMockBuilder('TB\Bundle\FrontendBundle\Service\Timezone')
            ->disableOriginalConstructor()
            ->getMock();
        $timezone->method('getTimezoneForGeoPoint')->willReturn('Europe/Berlin');
        $this->getContainer()->set('tb.timezone', $timezone);
    }
    
    protected static function getKernelClass()
    {
        require_once self::getPhpUnitXmlDir() . '/api/AppKernel.php';

        return 'AppKernel';
    }
    
    protected function assertJsonResponse($client)
    {
        $this->assertEquals('application/json',  $client->getResponse()->headers->get('Content-Type'),
            'Content-Type Header is "application/json"');  
        $this->assertJson($client->getResponse()->getContent(), 
            'Response is Valid JSON');
    }
    
    protected function getUser($name)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $user = $em
            ->getRepository('TBFrontendBundle:User')
            ->findOneByName($name);
        
        if (!$user) {
            $this->fail(sprintf('Missing User with name "%s" in test DB', $name));
        }
        
        return $user;
    }
    
    protected function getRoute($slug)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $route = $em
            ->getRepository('TBFrontendBundle:Route')
            ->findOneBySlug($slug);
        
        if (!$route) {
            $this->fail(sprintf('Missing Route with slug "%s" in test DB', $slug));
        }
        
        return $route;
    }
    
    protected function getRegion($slug)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $region = $em
            ->getRepository('TBFrontendBundle:Region')
            ->findOneBySlug($slug);
        
        if (!$region) {
            $this->fail(sprintf('Missing Region with slug "%s" in test DB', $slug));
        }
        
        return $region;
    }
    
    protected function getAttribute($name, $type)
    {
        $query = $this->getContainer()->get('doctrine.orm.entity_manager')
            ->createQuery('
                SELECT a FROM TBFrontendBundle:Attribute a
                WHERE a.type=:type
                AND a.name=:name')
            ->setParameter('type', $type)
            ->setParameter('name', $name);

        $attribute = $query->getOneOrNullResult();
        
        if (!$attribute) {
            $this->fail(sprintf('Missing Attribute with name "%s" and type "%s" in test DB', $name, $type‚));
        }
        
        return $attribute;
    }
    
    protected function getCampaign($slug)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $campaign = $em
            ->getRepository('TBFrontendBundle:Campaign')
            ->findOneBySlug($slug);
        
        if (!$campaign) {
            $this->fail(sprintf('Missing Campaign with slug "%s" in test DB', $slug));
        }
        
        return $campaign;
    }
    
    protected function callProtectedMethod($obj, $methodName, $parameter = array())
    {
        $method = new \ReflectionMethod($obj, $methodName);
        $method->setAccessible(true);
        
        return $method->invokeArgs($obj, $parameter);
    }
}