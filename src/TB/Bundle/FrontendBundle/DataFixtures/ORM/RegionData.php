<?php

namespace TB\Bundle\FrontendBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use TB\Bundle\FrontendBundle\Entity\Region;

class RegionData extends AbstractFixture implements FixtureInterface
{
    
    public function load(ObjectManager $manager)
    {
        $region = new Region();
        $region->setName('Grindelwald | Switzerland');
        $region->setAbout('The lively holiday resort at the foot of the Eiger is closely linked with mountaineering. Time and time again top alpinists have continued to cause a sensation in the Eiger north face, but Grindelwald also offers a variety of possibilities for those seeking active recovery. 

Grindelwald has something to offer everyone. The village is lively, sporty and active, but as a contrast also offers secluded, idyllic corners to savour and linger.');
        $region->setImage('grindelwald.jpg');
        $region->setLogo('logo_grindelwald.png');
        $region->setLink('http://www.grindelwald.ch');
        $region->setSlug('grindelwald');
        
        $manager->persist($region);
        $manager->flush();
        $this->addReference('Region-grindelwald', $region);
        
        
        $region = new Region();
        $region->setName('Valais | Switzerland');
        $region->setAbout('On the Italian border of the canton of Valais in the west of Switzerland, at the end of the 30 km-long Nikolaital, lies Zermatt, the village at the foot of the Matterhorn, the most photographed mountain in the world.');
        $region->setImage('zermatt.jpg');
        $region->setLogo('logo_zermatt.png');
        $region->setLink('http://www.zermatt.ch');
        $region->setSlug('valais');
        
        $manager->persist($region);
        $manager->flush();
        $this->addReference('Region-valais', $region);
        
        $region = new Region();
        $region->setName('London');
        $region->setAbout('London');
        $region->setImage('london.jpg');
        $region->setLogo('london.png');
        $region->setLink('http://www.london.com');
        $region->setSlug('london');
        
        $manager->persist($region);
        $manager->flush();
        $this->addReference('Region-london', $region);
    }
    
    public function getOrder()
    {
        return 1;
    }
    
}