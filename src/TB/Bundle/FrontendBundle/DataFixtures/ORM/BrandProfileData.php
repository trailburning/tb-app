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
        
        $brand = new BrandProfile();
        $brand->setAbout('For me Trailburning is about connecting with nature, sharing great trail experiences with other like minded individuals. It\'s also about discovery but at the end of the day it\'s about just getting out there!');
        $brand->setDisplayName('ashmei');
        $brand->setFirstName('ashmei');
        $brand->setLastName('ashmei');
        $brand->setAvatar('brand_logo.png');
        $brand->setHeaderImage('brand_hero2.jpg');
        $brand->setSynopsis('ashmei was established to fulfil a gap in the market for stylish, quality running apparel that had an understated and classic style.');
        $brand->setSubtitle('The finest performance running clothes in the World');
        $brand->setAbstract('Performance running apparel');
        $brand->setLink('http://www.ashmei.com');
        $brand->setEmail('email@ashmei');
        $brand->setPassword('password');
        $brand->setLocation(new Point(52.508006, 13.257437, 4326));
        
        $brand->setName('ashmei');
        $brand->setUsername('ashmei');
        $brand->setEnabled(true);
        $brand->setLocked(false);
        $brand->setExpired(false);
        $brand->setCredentialsExpired(false);

        $manager->persist($brand);
        $manager->flush();
        
        $this->addReference('BrandProfile-ashmei', $brand);
    }
    
    public function getOrder()
    {
        return 1;
    }
}