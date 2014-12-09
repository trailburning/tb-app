<?php

namespace TB\Bundle\APIBundle\Tests\Controller;

use TB\Bundle\APIBundle\Tests\AbstractApiTest;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\ApplicationTester;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;

/**
 *
 */
class AttributeControllerTest extends AbstractApiTest
{
    
    /**
     * Test the GET /attribute/{type}/list action
     */
    public function testGetTypeList()
    {   
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\AttributeData',
        ]);
        
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $attribute = $em
            ->getRepository('TBFrontendBundle:Attribute')
            ->findOneByName('run');
        if (!$attribute) {
            $this->fail('Missing Attribute with name "run" in test DB');
        }
        
        // Get same Attribute from API
        $client = $this->createClient();
        $crawler = $client->request('GET', '/v1/attribute/' . $attribute->getType() . '/list');
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode());
        $this->assertJsonResponse($client);   
    }
    
    /**
     * Test the GET /attribute/{type}/list action with an invalid type
     */
    public function testGetTypeListInvalidType()
    {   
        // Get same Attribute from API
        $client = $this->createClient();
        $crawler = $client->request('GET', '/v1/attribute/invalidtype/list');
        $this->assertEquals(Response::HTTP_BAD_REQUEST,  $client->getResponse()->getStatusCode());
        $this->assertJsonResponse($client);
    }

}
