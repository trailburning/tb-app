<?php 

namespace TB\Bundle\APIBundle\Tests\EventListener;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use TB\Bundle\FrontendBundle\Event\RoutePublishEvent;
use TB\Bundle\FrontendBundle\Event\UserFollowEvent;
use TB\Bundle\FrontendBundle\Event\UserUnfollowEvent;

class EntityEventSubscriberTest extends WebTestCase
{
    
    protected static function getKernelClass()
    {
        require_once self::getPhpUnitXmlDir() . '/frontend/AppKernel.php';

        return 'AppKernel';
    }

    /**
     * Test that a RoutePublishActivity is created when the tb.route_publish event gets dispatched
     */
    public function testOnRoutePublish()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteData',
        ]);
            
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        
        $route = $em
            ->getRepository('TBFrontendBundle:Route')
            ->findOneBySlug('grunewald');
        if (!$route) {
            $this->fail('Missing Route with slug "grunewald" in test DB');
        }
        
        //  get the event dispatcher and dispathe the tb.route_publish manually
        $dispatcher = $this->getContainer()->get('event_dispatcher');
        $event = new RoutePublishEvent($route, $route->getUser());
        $dispatcher->dispatch('tb.route_publish', $event);
        
        $activity = $em
            ->getRepository('TBFrontendBundle:RoutePublishActivity')
            ->findOneByObjectId($route->getId());
        
        if (!$activity) {
            $this->fail('RoutePublishActivity was not created for tb.route_publish event');
        }
        
        $this->assertEquals($route->getId(), $activity->getObject()->getId(), 
            'RoutePublishActivity with excpected objectId was created');
        $this->assertEquals($route->getUser()->getId(), $activity->getActor()->getId(),
            'RoutePublishActivity with excpected targetId was created');
        
    }
    
    /**
     * Test that a UserFollowActivity is created when the tb.user_follow event gets dispatched
     */
    public function testOnUserFollow()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\UserProfileData',
        ]);
            
        // Get Route from DB with the slug "grunewald"..
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $user = $em
            ->getRepository('TBFrontendBundle:User')
            ->findOneByName('mattallbeury');
        if (!$user) {
            $this->fail('Missing User with name "mattallbeury" in test DB');
        }
        
        $userToFollow = $em
            ->getRepository('TBFrontendBundle:User')
            ->findOneByName('paultran');
        if (!$userToFollow) {
            $this->fail('Missing User to follow with name "paultran" in test DB');
        }
        
        //  get the event dispatcher and dispathe the tb.route_publish manually
        $dispatcher = $this->getContainer()->get('event_dispatcher');
        $event = new UserFollowEvent($user, $userToFollow);
        $dispatcher->dispatch('tb.user_follow', $event);
        
        $activity = $em
            ->getRepository('TBFrontendBundle:UserFollowActivity')
            ->findOneByObjectId($userToFollow->getId());
        
        if (!$activity) {
            $this->fail('UserFollowActivity was not created for tb.user_follow event');
        }
        
        $this->assertEquals($userToFollow->getId(), $activity->getObject()->getId(), 
            'UserFollowActivity with excpected objectId was created');
        $this->assertEquals($user->getId(), $activity->getActor()->getId(),
            'UserFollowActivity with excpected targetId was created');
        
    }
    
    /**
     * Test that a UserUnfollowEvent is created when the tb.user_unfollow event gets dispatched
     */
    public function testOnUserUnfollow()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\UserProfileData',
        ]);
            
        // Get Route from DB with the slug "grunewald"..
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $user = $em
            ->getRepository('TBFrontendBundle:User')
            ->findOneByName('mattallbeury');
        if (!$user) {
            $this->fail('Missing User with name "mattallbeury" in test DB');
        }
        
        $userToUnfollow = $em
            ->getRepository('TBFrontendBundle:User')
            ->findOneByName('paultran');
        if (!$userToUnfollow) {
            $this->fail('Missing User to follow with name "paultran" in test DB');
        }
        
        //  get the event dispatcher and dispathe the tb.route_publish manually
        $dispatcher = $this->getContainer()->get('event_dispatcher');
        $event = new UserUnfollowEvent($user, $userToUnfollow);
        $dispatcher->dispatch('tb.user_unfollow', $event);
        
        $activity = $em
            ->getRepository('TBFrontendBundle:UserUnfollowActivity')
            ->findOneByObjectId($userToUnfollow->getId());
        
        if (!$activity) {
            $this->fail('UserUnfollowActivity was not created for tb.user_unfollow event');
        }
        
        $this->assertEquals($userToUnfollow->getId(), $activity->getObject()->getId(), 
            'UserUnfollowActivity with excpected objectId was created');
        $this->assertEquals($user->getId(), $activity->getActor()->getId(),
            'UserUnfollowActivity with excpected targetId was created');
        
    }
    
}