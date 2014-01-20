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

        $manager->persist($route);
        $manager->flush();
        $this->addReference('Route-grunewald', $route);
        
        $points = [
            [1, ['altitude' => 60.1, 'datetime' => 1376728877], [13.257437, 52.508006]],
            [2, ['altitude' => 60.1, 'datetime' => 1376728878], [13.257438, 52.508013]],
            [3, ['altitude' => 60.0, 'datetime' => 1376728884], [13.257321, 52.507924]],
        ];
        foreach ($points as $point) {
            $routePoint = new RoutePoint();
            $routePoint->setPointNumber($point[0]);
            $routePoint->setTags($point[1]);
            $routePoint->setCoords(new Point($point[2][0], $point[2][1]));
            $routePoint->setRoute($route);
            $manager->persist($route);
        }
        $manager->flush();
        
        $route = new Route();
        $route->setGpxfile($this->getReference('GpxFile-ttm'));
        $route->setName('Thames Trail Marathon');
        $route->setLength(41309);    
        $route->setCentroid(new Point(52.508006, 13.257437, 4326));
        $route->setTags(['ascent' => 176.8, 'descent' => 187.6]);
        $route->setUser($this->getReference('UserProfile-ashmei'));
        $route->setRouteType($this->getReference('RouteType-marathon'));
        $route->setRouteCategory($this->getReference('RouteCategory-park'));
        $route->setRegion('Thames Festival of Running');
        $route->setSlug('ttm');
        $route->setAbout('Following the Thames Pathway and the Ridgeway from Abingdon to Pangbourne the Route is a picturesque trial run along the Thames riverside.

The terrain is stable underfoot throughout, changing from gravel paths, to side street roads, to grassed banked mud trials; creating an enjoyable and safe route which is suitable for athletes of all levels.

The elevation along the course is flat/medium, with one single significant but short climb and decent towards the finish.

The scenery along the footpath; including locks, bridges, forestry and the river running along side providing constant encouragement along the distance.');

        $manager->persist($route);
        $manager->flush();
        $this->addReference('Route-ttm', $route);
        
        $points = [
            [1, ['altitude' => 51.9, 'datetime' => 1385535601], [-1.279091835, 51.667578027]],
            [2, ['altitude' => 55.2, 'datetime' => 1385535602], [-1.28153801, 51.66696583]],
            [3, ['altitude' => 54.3, 'datetime' => 1385535603], [-1.282997131, 51.66603421]],
        ];
        foreach ($points as $point) {
            $routePoint = new RoutePoint();
            $routePoint->setPointNumber($point[0]);
            $routePoint->setTags($point[1]);
            $routePoint->setCoords(new Point($point[2][0], $point[2][1]));
            $routePoint->setRoute($route);
            $manager->persist($route);
        }
        $manager->flush();
        
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