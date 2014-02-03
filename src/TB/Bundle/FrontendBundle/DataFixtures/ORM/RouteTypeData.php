<?php

namespace TB\Bundle\FrontendBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use CrEOF\Spatial\PHP\Types\Geometry\Point;
use TB\Bundle\FrontendBundle\Entity\RouteType;

class RouteTypeData extends AbstractFixture implements FixtureInterface
{
    
    public function load(ObjectManager $manager)
    {
        $routeType = new RouteType();
        $routeType->setName('Marathon');
        
        $manager->persist($routeType);
        $manager->flush();
        $this->addReference('RouteType-marathon', $routeType);
        
        $routeType = new RouteType();
        $routeType->setName('Ultra Marathon');
        
        $manager->persist($routeType);
        $manager->flush();
        $this->addReference('RouteType-ultra-marathon', $routeType);
    }
    
    public function getOrder()
    {
        return 1;
    }
    
}