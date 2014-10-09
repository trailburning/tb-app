<?php

namespace TB\Bundle\FrontendBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use TB\Bundle\FrontendBundle\Entity\Event;
use CrEOF\Spatial\PHP\Types\Geometry\Point;

class EventData extends AbstractFixture implements FixtureInterface, DependentFixtureInterface
{
    
    public function load(ObjectManager $manager)
    {
        $event = new Event();
        $event->setAbout('The Eiger 3970m and its North Face fascinate climbers and trail runners likewise. In 1858 the local mountain guides Christian Almer and Peter Bohren together with their client Charles Barrington were the first to reach the summit. The Eiger Northface, the last of the big walls in the Alps, was conquered in 1938 by Anderl Heckmair and Ludwig Vörg as well as Heinrich Harrer und Fritz Kasparekt in a four day ascent.');
        $event->setSlug('eiger');
        $event->setUser($this->getReference('UserProfile-matt'));
        $event->setRegion($this->getReference('Region-grindelwald'));
        $event->setTitle('Eiger Ultra Trail');
        $event->setTitle2('Switzerland');
        $event->setDate(new \DateTime('2014-07-19'));
        $event->setSubtitle('Eiger Ultra Trail - harder than the North Face solo');
        $event->setSynopsis('Grindelwald warmly welcomes you to the foot hills of the Eiger, Mönch and Jungfrau and to the Jungfrau-Aletsch UNESCO World Nature Heritage.');
        $event->setLocation(new Point(130.997576, -25.25605, 4326));
        $event->setLogo('logo_eiger.png');
        $event->setImage('event_header.jpg');
        $event->setImageCredit('Thomas Senf / visualimpact.ch');
        $event->setLink('http://www.eigerultratrail.ch');
        $event->setMapZoom(7);
        $event->setLogoSmall('card_logo_eiger.png');
        $event->setPublish(true);
        
        $manager->persist($event);
        $manager->flush();
        
        $event = new Event();
        $event->setAbout('Our goal with Matterhorn Ultraks is twofold: to create a race that will enter into sporting legend, while also responding to the increasing popularity of both disciplines and giving newcomers the opportunity to have a go. 

The wintertime SkiTour, hosted in a magical Alpine landscape, will test the most talented competitors (porterage, glacier safety, crossing ridges that are seldom travelled and much more), while also allowing the less experienced to perform at a level they are comfortable with on courses that are marked out and made safe for the event.');
        $event->setSlug('ultraks');
        $event->setUser($this->getReference('UserProfile-matt'));
        $event->setTitle('Matterhorn Ultraks');
        $event->setTitle2('Switzerland');
        $event->setDate(new \DateTime('2014-08-23'));
        $event->setSubtitle('Run the Matterhorn Ultraks');
        $event->setSynopsis('Matterhorn Ultraks combines Ski Touring and Trail Running; both competitions will take place against the stunning backdrop of the Matterhorn in Zermatt.');
        $event->setLocation(new Point(-1.28153801, 51.66696583, 4326));
        $event->setLogo('logo_ultraks.png');
        $event->setImage('event_header.jpg');
        $event->setImageCredit('matterhorn.ultraks.com / Jordi Saragossa');
        $event->setLink('http://www.ultraks.com');
        $event->setMapZoom(7);
        $event->setLogoSmall('card_logo_ultraks.png');
        $event->setPublish(true);
        
        $manager->persist($event);
        $manager->flush();
    }
    
    public function getOrder()
    {
        return 1;
    }
        
    public function getDependencies()
    {
        return [
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RegionData',
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\UserProfileData',
        ];
    }
}