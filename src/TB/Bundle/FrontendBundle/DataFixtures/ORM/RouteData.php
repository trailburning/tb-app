<?php

namespace TB\Bundle\FrontendBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use CrEOF\Spatial\PHP\Types\Geometry\Point;
use TB\Bundle\FrontendBundle\Entity\Route;
use TB\Bundle\FrontendBundle\Entity\RoutePoint;

class RouteData extends AbstractFixture implements FixtureInterface, DependentFixtureInterface
{
    
    public function load(ObjectManager $manager)
    {
        $route = new Route();
        $route->setGpxfile($this->getReference('GpxFile-grunewald'));
        $route->setName('Grunewald');
        $route->setLength(11298);    
        $route->setCentroid(new Point(52.508006, 13.257437, 4326));
        $route->setTags(['ascent' => 223.3, 'descent' => 207.3]);
        $route->setUser($this->getReference('UserProfile-matt'));
        $route->setRouteType($this->getReference('RouteType-marathon'));
        $route->setRouteCategory($this->getReference('RouteCategory-park'));
        $route->setRegion('Berlin');
        $route->setSlug('grunewald');
        $route->setAbout('The Grunewald is a forest located in the western side of Berlin on the east side of the river Havel.');
        
        $points = [
            [1, ['altitude' => 60.1, 'datetime' => 1376728877], [13.257437, 52.508006]],
            [2, ['altitude' => 60.1, 'datetime' => 1376728878], [13.257438, 52.508013]],
            [3, ['altitude' => 60.0, 'datetime' => 1376728884], [13.257321, 52.507924]],
        ];‚
        foreach ($points as $point {
            $routePoint = new RoutePoint();
            $routePoint->setPointNumber($point[0]);
            $routePoint->setTags($point[1]);
            $routePoint->setCoords(new Point($point[2][0], $point[2][1]));
            $route->addRoutePoint($routePoint);
        }

        $manager->persist($route);
        $manager->flush();
        $this->addReference('Route-grunewald', $route);
        
    }
    
    public function getOrder()
    {
        return 1;
    }
    
    public function getDependencies()
    {
        return [
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\UserProfileData',
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\BrandProfileData',
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\GpxFileData',
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteTypeData',
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteCategoryData',
        ];
    }
}