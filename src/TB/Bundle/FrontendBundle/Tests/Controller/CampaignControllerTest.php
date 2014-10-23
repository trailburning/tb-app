<?php

namespace TB\Bundle\FrontendBundle\Tests\Controller;

use TB\Bundle\FrontendBundle\Tests\AbstractFrontendTest;
use Symfony\Component\HttpFoundation\Response;

class CampaignControllerTest extends AbstractFrontendTest
{
    
    public function testCampaign()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\CampaignData',
        ]); 
        
        $client = static::createClient();

        $campaign = $this->getCampaign('urbantrails-london');

        $crawler = $client->request('GET', '/campaign/' . $campaign->getSlug());
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode());
    }
    
    public function testCampaignRoute()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\CampaignData',
        ]); 
        $client = static::createClient();

        $campaign = $this->getCampaign('urbantrails-london');
        $route = $this->getRoute('ttm');

        $crawler = $client->request('GET', '/campaign/' . $campaign->getSlug() . '/trail/' . $route->getSlug());
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode());
    }

}
