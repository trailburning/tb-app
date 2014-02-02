<?php

namespace TB\Bundle\FrontendBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use CrEOF\Spatial\PHP\Types\Geometry\Point;

use TB\Bundle\FrontendBundle\Entity\GpxFile;
use TB\Bundle\FrontendBundle\Entity\Route;
use TB\Bundle\FrontendBundle\Entity\RoutePoint;
use TB\Bundle\FrontendBundle\Entity\Media;
use TB\Bundle\FrontendBundle\Entity\MediaVersion;
use TB\Bundle\FrontendBundle\Entity\RouteMedia;

class RouteData extends AbstractFixture implements FixtureInterface, DependentFixtureInterface
{
    
    public function load(ObjectManager $manager)
    {
        //
        // Trail created by UserProfile, no Event, no Editorial
        //
        $gpxFile = new GpxFile();
        $gpxFile->setPath('path');
        
        $manager->persist($gpxFile);
        
        $route = new Route();
        $route->setGpxfile($gpxFile);
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
        $this->addReference('Route-grunewald', $route);
        
        // add RoutePoints to Route
        $points = [
            [1, ['altitude' => 60.1, 'datetime' => 1376728877], [13.257437, 52.508006]],
            [2, ['altitude' => 70.6, 'datetime' => 1376729276], [13.257437, 52.508006]],
            [69, ['altitude' => 64.1, 'datetime' => 1376729222], [13.249617, 52.501565]],
            [111, ['altitude' => 87.3, 'datetime' => 1376729446], [13.248257, 52.50296]],
            [233, ['altitude' => 47.5, 'datetime' => 1376730055], [13.227167, 52.496973]],
            [290, ['altitude' => 51.4, 'datetime' => 1376730345], [13.231805, 52.490537]],
            [342, ['altitude' => 52.9, 'datetime' => 1376730612], [13.233876, 52.48959]],
            [394, ['altitude' => 58.8, 'datetime' => 1376730897], [13.221316, 52.489695]],
            [427, ['altitude' => 69.9, 'datetime' => 1376731062], [13.213987, 52.490498]],
            [478, ['altitude' => 61.2, 'datetime' => 1376731319], [13.203118, 52.491101]],
            [563, ['altitude' => 31.2, 'datetime' => 1376731754], [13.193966, 52.485072]],
            [616, ['altitude' => 30.7, 'datetime' => 1376732022], [13.192097, 52.478326]],
            [678, ['altitude' => 31.6, 'datetime' => 1376732355], [13.196252, 52.471298]],
            [746, ['altitude' => 74.0, 'datetime' => 1376732687], [13.196559, 52.477397]],
            [758, ['altitude' => 76.1, 'datetime' => 1376732751], [13.196279, 52.477955]],
        ];
        foreach ($points as $point) {
            $routePoint = new RoutePoint();
            $routePoint->setPointNumber($point[0]);
            $routePoint->setTags($point[1]);
            $routePoint->setCoords(new Point($point[2][0], $point[2][1], 4326));
            $routePoint->setRoute($route);
            $manager->persist($routePoint);
        }
         
        // add Image Media to Route 
        $medias = [
            [['width' => 1280, 'height' => 960, 'altitude' => 60.1, 'datetime' => 1376728834, 'filesize' => 607249], [13.257437,  52.508006], 0, 'trailburning-media/cb88c97a09a59aa2452c0d0cdfdd2f4ccc211a53.jpg'],
            [['width' => 1280, 'height' => 956, 'altitude' => 56.8, 'datetime' => 1376729041, 'filesize' => 329318], [13.252078,  52.504918], 0, 'trailburning-media/895d0ce8f7d3bb08c7d4ef128b0131eaccd67b97.jpg'],
            [['width' => 1280, 'height' => 956, 'altitude' => 86.2, 'datetime' => 1376729419, 'filesize' => 283286], [13.248702,  52.502691], 0, 'trailburning-media/4f222c50f054b2feefd18be66edb3dcd44858493.jpg'],
            [['width' => 1280, 'height' => 956, 'altitude' => 79.0, 'datetime' => 1376729478, 'filesize' => 413357], [13.247005,  52.503021], 0, 'trailburning-media/29d35f4e5f485b8da742f7ce6b3e96b4c8cf8691.jpg'],
            [['width' => 1280, 'height' => 956, 'altitude' => 47.5, 'datetime' => 1376730053, 'filesize' => 341782], [13.227167,  52.496973], 0, 'trailburning-media/c9472914f7ef51f7df7025ebd85d791e48309987.jpg'],
            [['width' => 1280, 'height' => 956, 'altitude' => 38.0, 'datetime' => 1376731875, 'filesize' => 287037], [13.194522,  52.48193], 0, 'trailburning-media/837c5c05f9b047ea7f545ad7aef9796271508066.jpg'],
            [['width' => 1280, 'height' => 956, 'altitude' => 31.6, 'datetime' => 1376732354, 'filesize' => 193569], [13.196252,  52.471298], 0, 'trailburning-media/3d0994ca3d074920e661f5a4182a93f8c07460e5.jpg'],
            [['width' => 1280, 'height' => 956, 'altitude' => 76.5, 'datetime' => 1376732724, 'filesize' => 279660], [13.196632,  52.47771], 0, 'trailburning-media/d7bc334a55c629e69ace79e5b64d14a6a36bec22.jpg'],
        ];
        foreach ($medias as $mediaData) {
            $media = new Media();
            $media->setTags($mediaData[0]);
            $media->setCoords(new Point($mediaData[1][0], $mediaData[1][1], 4326));
            $manager->persist($media);
            
            $mediaVersion = new MediaVersion();
            $mediaVersion->setVersionSize($mediaData[2]);
            $mediaVersion->setPath($mediaData[3]);
            $mediaVersion->setMedia($media);
            $manager->persist($mediaVersion);
            
            $route->addMedia($media);
        }
        $manager->persist($route);
        $manager->flush();
        
        //
        // Trail created by BrandProfile, no Event, no Editorial
        //
        $this->addReference('GpxFile-grunewald', $gpxFile);
        
        $gpxFile = new GpxFile();
        $gpxFile->setPath('path');
        
        $manager->persist($gpxFile);
        
        $route = new Route();
        $route->setGpxfile($gpxFile);
        $route->setName('Thames Trail Marathon');
        $route->setLength(41309);    
        $route->setCentroid(new Point(52.508006, 13.257437, 4326));
        $route->setTags(['ascent' => 176.8, 'descent' => 187.6]);
        $route->setUser($this->getReference('BrandProfile-ashmei'));
        $route->setRouteType($this->getReference('RouteType-marathon'));
        $route->setRouteCategory($this->getReference('RouteCategory-park'));
        $route->setRegion('Thames Festival of Running');
        $route->setSlug('ttm');
        $route->setAbout('Following the Thames Pathway and the Ridgeway from Abingdon to Pangbourne the Route is a picturesque trial run along the Thames riverside.

The terrain is stable underfoot throughout, changing from gravel paths, to side street roads, to grassed banked mud trials; creating an enjoyable and safe route which is suitable for athletes of all levels.

The elevation along the course is flat/medium, with one single significant but short climb and decent towards the finish.

The scenery along the footpath; including locks, bridges, forestry and the river running along side providing constant encouragement along the distance.');

        $manager->persist($route);
        
        $this->addReference('Route-ttm', $route);
        
        $points = [
            [1, ['altitude' => 51.9, 'datetime' => 1385535601], [-1.279091835, 51.667578027]],
            [2, ['altitude' => 55.2, 'datetime' => 1385535602], [-1.28153801, 51.66696583]],
            [3, ['altitude' => 48.2, 'datetime' => 1385535624], [-1.240167618, 51.644095676]],
            [4, ['altitude' => 47.4, 'datetime' => 1385535642], [-1.205663681, 51.65645072]],
            [5, ['altitude' => 46.4, 'datetime' => 1385535660], [-1.178069115, 51.64566687]],
            [6, ['altitude' => 53.0, 'datetime' => 1385535674], [-1.162362099, 51.630778281]],
            [7, ['altitude' => 46.5, 'datetime' => 1385535687], [-1.118459702, 51.621107402]],
            [8, ['altitude' => 45.0, 'datetime' => 1385535695], [-1.116185188, 51.616844101]],
            [9, ['altitude' => 46.6, 'datetime' => 1385535713], [-1.116056442, 51.593842015]],
            [10, ['altitude' => 48.8, 'datetime' => 1385535725], [-1.120433807, 51.573975685]],
            [11, ['altitude' => 43.4, 'datetime' => 1385535735], [-1.135754585, 51.559090248]],
            [12, ['altitude' => 46.8, 'datetime' => 1385535746], [-1.1363554, 51.546201606]],
            [13, ['altitude' => 45.9, 'datetime' => 1385535765], [-1.14197731, 51.516113907]],
            [14, ['altitude' => 40.2, 'datetime' => 1385535774], [-1.125798225, 51.511894166]],
            [15, ['altitude' => 72.5, 'datetime' => 1385535786], [-1.105885506, 51.501690393]],
            [16, ['altitude' => 67.4, 'datetime' => 1385535792], [-1.094169617, 51.494744109]],
            [17, ['altitude' => 43.6, 'datetime' => 1385535802], [-1.089363098, 51.485552015]],
            [18, ['altitude' => 42.2, 'datetime' => 1385535813], [-1.108632088, 51.500995812]],
            [19, ['altitude' => 41.1, 'datetime' => 1385535819], [-1.115927696, 51.510158083]],
        ];
        foreach ($points as $point) {
            $routePoint = new RoutePoint();
            $routePoint->setPointNumber($point[0]);
            $routePoint->setTags($point[1]);
            $routePoint->setCoords(new Point($point[2][0], $point[2][1], 4326));
            $routePoint->setRoute($route);
            $manager->persist($routePoint);
        }
        
        // add Image Media to Route 
        $medias = [
            [['width' => 1280, 'height' => 853, 'altitude' => 55.2, 'datetime' => 1385535601, 'filesize' => 456795], [-1.28153801, 51.66696583], 0, 'trailburning-media/465195300a1a39b572936ea21eb51ea01d3a0a32.jpg'],
            [['width' => 1280, 'height' => 853, 'altitude' => 54.3, 'datetime' => 1385535602, 'filesize' => 330399], [-1.282997131, 51.66603421], 0, 'trailburning-media/fd50753240135f6710b5326124cdb875bc793c6c.jpg'],
            [['width' => 1280, 'height' => 853, 'altitude' => 51.3, 'datetime' => 1385535605, 'filesize' => 429740], [-1.281108856, 51.660044766], 0, 'trailburning-media/7fb4aae007ca8c9beea19bd601b34410e516f60a.jpg'],
            [['width' => 1280, 'height' => 853, 'altitude' => 47.0, 'datetime' => 1385535633, 'filesize' => 222317], [-1.211628914, 51.648223274], 0, 'trailburning-media/ab1f6a307886400a912322cdc0ef5684ebac841f.jpg'],
            [['width' => 1280, 'height' => 853, 'altitude' => 48.2, 'datetime' => 1385535637, 'filesize' => 263489], [-1.211071014, 51.654693527], 0, 'trailburning-media/3e7e193fa3aa0f199ed4b03b5cec29fb48d8d653.jpg'],
            [['width' => 1280, 'height' => 853, 'altitude' => 57.2, 'datetime' => 1385535779, 'filesize' => 267777], [-1.112751961, 51.5096506], 0, 'trailburning-media/9338e17e06f412d2a6e8a172cfdf7ef13ef44682.jpg'],
            [['width' => 1280, 'height' => 853, 'altitude' => 68.5, 'datetime' => 1385535784, 'filesize' => 377970], [-1.106786728, 51.502545247], 0, 'trailburning-media/eaef8b08afaf4e410b57ac27356f4d88f8bd7c10.jpg'],
        ];
        foreach ($medias as $mediaData) {
            $media = new Media();
            $media->setTags($mediaData[0]);
            $media->setCoords(new Point($mediaData[1][0], $mediaData[1][1], 4326));
            $manager->persist($media);
            
            $mediaVersion = new MediaVersion();
            $mediaVersion->setVersionSize($mediaData[2]);
            $mediaVersion->setPath($mediaData[3]);
            $mediaVersion->setMedia($media);
            $manager->persist($mediaVersion);
            
            $route->addMedia($media);
        }
        $manager->persist($route);
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
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteTypeData',
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteCategoryData',
        ];
    }
}