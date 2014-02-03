<?php

namespace TB\Bundle\FrontendBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use CrEOF\Spatial\PHP\Types\Geometry\Point;
use TB\Bundle\FrontendBundle\Entity\RouteCategory;

class RouteCategoryData extends AbstractFixture implements FixtureInterface
{
    
    public function load(ObjectManager $manager)
    {
        $routeCategory = new RouteCategory();
        $routeCategory->setName('Park');
        
        $manager->persist($routeCategory);
        $manager->flush();
        $this->addReference('RouteCategory-park', $routeCategory);
        
        $routeCategory = new RouteCategory();
        $routeCategory->setName('Bush');
        
        $manager->persist($routeCategory);
        $manager->flush();
        $this->addReference('RouteCategory-bush', $routeCategory);
        
        $routeCategory = new RouteCategory();
        $routeCategory->setName('Mountain');
        
        $manager->persist($routeCategory);
        $manager->flush();
        $this->addReference('RouteCategory-mountain', $routeCategory);
    }
    
    public function getOrder()
    {
        return 1;
    }
    
}