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
abstract class AbstractApiTestCase extends WebTestCase
{
    
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
    
}