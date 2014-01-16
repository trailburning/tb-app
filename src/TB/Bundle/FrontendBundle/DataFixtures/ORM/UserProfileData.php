<?php

namespace TB\Bundle\FrontendBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use CrEOF\Spatial\PHP\Types\Geometry\Point;
use TB\Bundle\FrontendBundle\Entity\UserProfile;


class UserProfileData extends AbstractFixture implements FixtureInterface
{
    
    public function load(ObjectManager $manager)
    {
        $userAdmin = new UserProfile();
        $userAdmin->setName('admin');

        $manager->persist($userAdmin);
        $manager->flush();
        
        $this->addReference('UserProfile-admin', $userAdmin);
        
        $userMatt = new UserProfile();
        $userMatt->setName('mattallbeury');
        $userMatt->setAbout('For me Trailburning is about connecting with nature, sharing great trail experiences with other like minded individuals. It\'s also about discovery but at the end of the day it\'s about just getting out there!');
        $userMatt->setFirstName('Matt');
        $userMatt->setLastName('Allbeury');
        $userMatt->setAvatar('avatar_ma.png');
        $userMatt->setSynopsis('I\'m all over the great outdoors, never happier than hitting the trails - whatever the weather! You name it and I\'m running in it, although not so much into mud, yes Tough Mudder I\'m looking at you!');
        $userMatt->setLocation(new Point(52.508006, 13.257437, 4326));

        $manager->persist($userMatt);
        $manager->flush();
        $this->addReference('UserProfile-matt', $userMatt);
        
        $userPaul = new UserProfile();
        $userPaul->setName('paultran');
        $userPaul->setAbout('I get so much joy out of running trails that I want to share it with others.');
        $userPaul->setFirstName('Paul');
        $userPaul->setLastName('Tran');
        $userPaul->setAvatar('avatar_pt.png');
        $userPaul->setSynopsis('It started as an innocent 4km run between work and home about 5 years ago and slowly progressed to road marathons, triathlons, and now trail ultras. My name is Paul and I\'m addicted to running.');
        $userPaul->setLocation(new Point(52.508006, 13.257437, 4326));

        $manager->persist($userPaul);
        $manager->flush();
        $this->addReference('UserProfile-paul', $userPaul);
    }
    
    public function getOrder()
    {
        return 1;
    }
}