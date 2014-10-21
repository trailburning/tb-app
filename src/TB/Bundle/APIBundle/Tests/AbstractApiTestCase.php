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

        $attribute = $query->getSingleResult();
        
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
}