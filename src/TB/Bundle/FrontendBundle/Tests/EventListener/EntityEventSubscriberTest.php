<?php 

namespace TB\Bundle\APIBundle\Tests\EventListener;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use TB\Bundle\FrontendBundle\Entity\GpxFile;
use TB\Bundle\FrontendBundle\Entity\Route;
use CrEOF\Spatial\PHP\Types\Geometry\Point;

class EntityEventSubscriberTest extends WebTestCase
{
    
    protected static function getKernelClass()
    {
        require_once self::getPhpUnitXmlDir() . '/frontend/AppKernel.php';

        return 'AppKernel';
    }

    protected function setUp()
    {
        $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');
    }
    
    public function testUserFollow()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\UserProfileData',
        ]);
        
        // Get Route from DB with the slug "grunewald"..
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $user1 = $em
            ->getRepository('TBFrontendBundle:User')
            ->findOneByName('mattallbeury');
        if (!$user1) {
            $this->fail('Missing User with name "mattallbeury" in test DB');
        }
        
        $user2 = $em
            ->getRepository('TBFrontendBundle:User')
            ->findOneByName('paultran');
        if (!$user2) {
            $this->fail('Missing User to follow with name "paultran" in test DB');
        }
        
        $user1->addIFollow($user2);
        $em->persist($user1);
        $em->flush();
        
    }
    
    
    protected function getRoute()
    {
        $user = $this->em
            ->getRepository('TBFrontendBundle:User')
            ->findOneByName('mattallbeury');
        if (!$user) {
            $this->fail('Missing User with name "mattallbeury" in test DB');
        }
        
        $gpxFile = new GpxFile();
        $gpxFile->setPath('path');
        
        $this->em->persist($gpxFile);
        
        $route = new Route();
        $route->setGpxfile($gpxFile);
        $route->setName('Grunewald');
        $route->setLength(11298);    
        $route->setCentroid(new Point(13.257437, 52.508006, 4326));
        $route->setTags(['ascent' => 223.3, 'descent' => 207.3]);
        $route->setUser($user);
        $route->setRegion('Berlin');
        $route->setSlug('grunewald');
        $route->setAbout('The Grunewald is a forest located in the western side of Berlin on the east side of the river Havel.');
        
        return $route; 
    }

    protected $eventDispatched; 

    /**
     * Test that on Route persist and published set to true, the RoutePublishEvent gets dispatched
     */
    public function testRoutePersistDispatchesEvent()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\UserProfileData',
        ]);
        
        // set flag to false
        $this->eventDispatched = false;
        
        //  get the event dispatcher and add a listener for the tb.route_publish event
        $dispatcher = $this->getContainer()->get('event_dispatcher'); 
        $dispatcher->addListener('tb.route_publish', function ($event, $eventName, $dispatcher) {
            // this part gets executed when the event is dispatched
            $this->assertInstanceOf('TB\Bundle\FrontendBundle\Event\RoutePublishEvent', $event,
                'The RoutePublishEvent was created');
            $this->assertEquals('grunewald', $event->getRoute()->getSlug(), 
                'The Route that was published was passed to the RoutePublishEvent event');
            $this->assertEquals('mattallbeury', $event->getUser()->getName(), 
                'The User who published the Route was passed to the RoutePublishEvent event');
            
            // set flag to true, it means the event was dispatched
            $this->eventDispatched = true;
        });
        
        $route = $this->getRoute();
        $route->setPublish(true);
        
        $this->em->persist($route);
        $this->em->flush();
        
        $this->assertTrue($this->eventDispatched, 'The tb.route_publish Event was successfully dispatched');
    }
    
    
    /**
     * Test that on Route persist and published set to false, the RoutePublishEvent gets not dispatched
     */
    public function testRoutePersistNotDispatchesEvent()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\UserProfileData',
        ]);
        
        // set flag to false
        $this->eventDispatched = false;
        
        //  get the event dispatcher and add a listener for the tb.route_publish event
        $dispatcher = $this->getContainer()->get('event_dispatcher'); 
        $dispatcher->addListener('tb.route_publish', function ($event, $eventName, $dispatcher) {
             // set flag to true, it means the event was dispatched
            $this->eventDispatched = true;
        });
        
        $route = $this->getRoute();
        $route->setPublish(false);
        
        $this->em->persist($route);
        $this->em->flush();
        
        $this->assertFalse($this->eventDispatched, 'The tb.route_publish Event was not dispatched');
    }
    
    /**
     * Test that on Route persist and published set to true, the RoutePublishEvent gets dispatched
     */
    public function testRouteUpdateDispatchesEvent()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\UserProfileData',
        ]);
        
        // set flag to false
        $this->eventDispatched = false;
        
        //  get the event dispatcher and add a listener for the tb.route_publish event
        $dispatcher = $this->getContainer()->get('event_dispatcher'); 
        $dispatcher->addListener('tb.route_publish', function ($event, $eventName, $dispatcher) {
            
            $this->assertInstanceOf('TB\Bundle\FrontendBundle\Event\RoutePublishEvent', $event,
                'The RoutePublishEvent was created');
            $this->assertEquals('grunewald', $event->getRoute()->getSlug(), 
                'The Route that was published was passed to the RoutePublishEvent event');
            $this->assertEquals('mattallbeury', $event->getUser()->getName(), 
                'The User who published the Route was passed to the RoutePublishEvent event');
            
            // set flag to true, it means the event was dispatched
            $this->eventDispatched = true;
        });
        
        $route = $this->getRoute();
        $route->setPublish(false);
        
        $this->em->persist($route);
        $this->em->flush();
        
        $route->setPublish(true);
        $this->em->persist($route);
        $this->em->flush();
        
        $this->assertTrue($this->eventDispatched, 'The tb.route_publish Event was successfully dispatched');
    }
    
    
    /**
     * Test that on Route persist and published set to false, the RoutePublishEvent gets not dispatched
     */
    public function testRouteUpdateNotDispatchesEvent()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\UserProfileData',
        ]);
        
        // set flag to false
        $this->eventDispatched = false;
        
        //  get the event dispatcher and add a listener for the tb.route_publish event
        $dispatcher = $this->getContainer()->get('event_dispatcher'); 
        $dispatcher->addListener('tb.route_publish', function ($event, $eventName, $dispatcher) {
             // set flag to true, it means the event was dispatched
            $this->eventDispatched = true;
        });
        
        $route = $this->getRoute();
        $route->setPublish(false);
        
        $this->em->persist($route);
        $this->em->flush();
        
        $route->setPublish(false);
        
        $this->em->persist($route);
        $this->em->flush();
        
        $this->assertFalse($this->eventDispatched, 'The tb.route_publish Event was not dispatched');
    }
    
}