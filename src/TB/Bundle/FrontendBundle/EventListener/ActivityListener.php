<?php 

namespace TB\Bundle\FrontendBundle\EventListener;

use Doctrine\ORM\EntityManager;

use TB\Bundle\FrontendBundle\Event\RoutePublishEvent;
use TB\Bundle\FrontendBundle\Event\UserFollowEvent;
use TB\Bundle\FrontendBundle\Event\UserUnfollowEvent;

use TB\Bundle\FrontendBundle\Entity\RoutePublishActivity;
use TB\Bundle\FrontendBundle\Entity\UserFollowActivity;
use TB\Bundle\FrontendBundle\Entity\UserUnfollowActivity;

/**
 * Create activity feed items from corresponding events
 */
class ActivityListener
{
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }
    
    /**
     * Create a RoutePublishActivity from the RoutePublishEvent event
     */ 
    public function onRoutePublish(RoutePublishEvent $event)
    {
        $routePublishActivity = new RoutePublishActivity($event->getRoute(), $event->getUser());
        $this->em->persist($routePublishActivity);
        $this->em->flush();
    }
    
    /**
     * Create a UserFollowActivity from the UserFollowEvent event
     */ 
    public function onUserFollow(UserFollowEvent $event)
    {
        $userFollowActivity = new UserFollowActivity($event->getFollowingUser(), $event->getFollowedUser());
        $this->em->persist($userFollowActivity);
        $this->em->flush();
    }
    
    /**
     * Create a UserUnFollowActivity from the UserUnfollowEvent event
     */ 
    public function onUserUnfollow(UserUnfollowEvent $event)
    {
        $userUnfollowActivity = new UserUnfollowActivity($event->getUnfollowingUser(), $event->getUnfollowedUser());
        $this->em->persist($userUnfollowActivity);
        $this->em->flush();
    }
}