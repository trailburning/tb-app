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
        $user = new UserProfile();
        $user->setFirstName('Matt');
        $user->setLastName('Allbeury');
        $user->setEmail('email@mattallbeury');
        $user->setPlainPassword('password');
        $user->setAbout('For me Trailburning is about connecting with nature, sharing great trail experiences with other like minded individuals. It\'s also about discovery but at the end of the day it\'s about just getting out there!');
        $user->setAvatar('avatar_ma.png');
        $user->setSynopsis('I\'m all over the great outdoors, never happier than hitting the trails - whatever the weather! You name it and I\'m running in it, although not so much into mud, yes Tough Mudder I\'m looking at you!');
        $user->setLocation(new Point(52.508006, 13.257437, 4326));
        $user->setEnabled(true);

        $manager->persist($user);
        $manager->flush();
        $this->addReference('UserProfile-matt', $user);
        
        $user = new UserProfile();
        $user->setFirstName('Paul');
        $user->setLastName('Tran');
        $user->setEmail('email@paultran');
        $user->setPlainPassword('password');
        $user->setAbout('I get so much joy out of running trails that I want to share it with others.');
        $user->setAvatar('avatar_pt.png');
        $user->setSynopsis('It started as an innocent 4km run between work and home about 5 years ago and slowly progressed to road marathons, triathlons, and now trail ultras. My name is Paul and I\'m addicted to running.');
        $user->setLocation(new Point(52.508006, 13.257437, 4326));
        $user->setEnabled(true);

        $manager->persist($user);
        $manager->flush();
        
        $this->addReference('UserProfile-paul', $user);
    }
    
    public function getOrder()
    {
        return 1;
    }
}