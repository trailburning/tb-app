<?php

namespace TB\Bundle\FrontendBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use CrEOF\Spatial\PHP\Types\Geometry\Point;
use TB\Bundle\FrontendBundle\Entity\BrandProfile;


class BrandProfileData extends AbstractFixture implements FixtureInterface
{
    
    public function load(ObjectManager $manager)
    {
        
        $brandAshmei = new BrandProfile();
        $brandAshmei->setName('ashmei');
        $brandAshmei->setAbout('For me Trailburning is about connecting with nature, sharing great trail experiences with other like minded individuals. It\'s also about discovery but at the end of the day it\'s about just getting out there!');
        $brandAshmei->setDisplayName('ashmei');
        $brandAshmei->setAvatar('brand_logo.png');
        $brandAshmei->setHeaderImage('brand_hero2.jpg');
        $brandAshmei->setSynopsis('ashmei was established to fulfil a gap in the market for stylish, quality running apparel that had an understated and classic style.');
        $brandAshmei->setSubtitle('The finest performance running clothes in the World');
        $brandAshmei->setAbstract('Performance running apparel');
        $brandAshmei->setLink('http://www.ashmei.com');

        $manager->persist($brandAshmei);
        $manager->flush();
        $this->addReference('BrandProfile-ashmei', $brandAshmei);
    }
    
    public function getOrder()
    {
        return 1;
    }
}