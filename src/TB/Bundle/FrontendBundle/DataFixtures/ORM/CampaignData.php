<?php

namespace TB\Bundle\FrontendBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use TB\Bundle\FrontendBundle\Entity\Campaign;
use TB\Bundle\FrontendBundle\Entity\CampaignGroup;
use TB\Bundle\FrontendBundle\Entity\CampaignRoute;

class CampaignData extends AbstractFixture implements FixtureInterface, DependentFixtureInterface
{
    
    public function load(ObjectManager $manager)
    {   
        $campaignGroup = new CampaignGroup();
        $campaignGroup->setName('Urban Trails');
        
        $manager->persist($campaignGroup);
        $manager->flush();
        
        $campaign = new Campaign();
        $campaign->setSlug('urbantrails-london');
        $campaign->setUser($this->getReference('UserProfile-matt'));
        $campaign->setRegion($this->getReference('Region-london'));
        $campaign->setTitle('London');
        $campaign->setSynopsis('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut feugiat accumsan leo eget porttitor. Donec blandit, dui nec aliquam mattis, leo augue adipiscing purus, quis iaculis eros purus ac nisi. Etiam neque magna, consectetur eget suscipit vitae, convallis a metus.');
        $campaign->setText('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut feugiat accumsan leo eget porttitor. Donec blandit, dui nec aliquam mattis, leo augue adipiscing purus, quis iaculis eros purus ac nisi.');
        $campaign->setImage('images/campaign/urbantrails-london/image.jpg');
        $campaign->setWatermarkImage('images/campaign/urbantrails-london/watermark.png');
        $campaign->setLogo('images/campaign/urbantrails-london/logo_urbantrails_london.png');
        $campaign->setCampaignGroup($campaignGroup);
        
        $manager->persist($campaign);
        $manager->flush();
        
        $campaignRoute = new CampaignRoute();
        $campaignRoute->setCampaign($campaign);
        $campaignRoute->setRoute($this->getReference('Route-london'));
        
        $manager->persist($campaignRoute);
        $manager->flush();
        
        $this->addReference('Campaign-london', $campaign);
    }
    
    public function getOrder()
    {
        return 1;
    }
        
    public function getDependencies()
    {
        return [
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RegionData',
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteData',
        ];
    }
}