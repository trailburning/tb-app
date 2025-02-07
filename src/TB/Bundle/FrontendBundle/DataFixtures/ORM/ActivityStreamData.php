<?php

namespace TB\Bundle\FrontendBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use TB\Bundle\FrontendBundle\Entity\UserFollowActivity;
use TB\Bundle\FrontendBundle\Entity\RouteLikeActivity;
use TB\Bundle\FrontendBundle\Entity\RouteLike;

class ActivityStreamData extends AbstractFixture implements FixtureInterface, DependentFixtureInterface
{
    
    public function load(ObjectManager $manager)
    {
        $route = $this->getReference('Route-grunewald');
        $userA = $this->getReference('UserProfile-matt');
        $userB = $this->getReference('UserProfile-paul');
        
        // User Paul follows User Matt
        $userB->addUserIFollow($userA);
        $manager->persist($userB);
        $activity1 = new UserFollowActivity($userB, $userA);
        $manager->persist($activity1);
        
        // User Matt follows User Paul
        $userA->addUserIFollow($userB);
        $manager->persist($userA);
        $activity2 = new UserFollowActivity($userA, $userB);
        $manager->persist($activity2);
        
        // User Paul likes User matt's Route grunewald
        $routeLike = new RouteLike();        
        $routeLike->setRoute($route);
        $routeLike->setUser($userB);
        $manager->persist($routeLike);
        $activity3 = new RouteLikeActivity($route, $userB);
        $manager->persist($activity3);
        
        $manager->flush();
    }
    
    public function getOrder()
    {
        return 1;
    }
    
    public function getDependencies()
    {
        return [
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteData',
        ];
    }
}