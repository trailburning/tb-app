<?php

namespace TB\Bundle\FrontendBundle\Tests\Controller;

use TB\Bundle\FrontendBundle\Tests\AbstractFrontendTest;

class CampaignControllerTest extends AbstractFrontendTest
{
    
    public function testCampaign()
    {
        $client = static::createClient();

        $campaign = $this->getCampaign('urbantrails-london');

        $crawler = $client->request('GET', '/campaign/' . $campaign->getSlug());
    }
    
    public function testCampaignRoute()
    {
        $client = static::createClient();

        $campaign = $this->getCampaign('urbantrails-london');
        $route = $this->getRoute('ttm');

        $crawler = $client->request('GET', '/campaign/' . $campaign->getSlug() . '/trail/' . $route->getSlug());
    }

}
