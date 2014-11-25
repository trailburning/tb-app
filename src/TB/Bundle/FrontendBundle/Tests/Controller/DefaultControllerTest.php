<?php

namespace TB\Bundle\FrontendBundle\Tests\Controller;

use TB\Bundle\FrontendBundle\Tests\AbstractFrontendTest;
use Symfony\Component\HttpFoundation\Response;

class DefaultControllerTest extends AbstractFrontendTest
{
    
    public function testHomepage()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\CampaignData',
        ]); 
        
        $client = static::createClient();

        $crawler = $client->request('GET', '/');
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode());
    }

}
