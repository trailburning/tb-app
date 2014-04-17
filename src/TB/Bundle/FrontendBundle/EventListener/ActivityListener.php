<?php 

namespace TB\Bundle\FrontendBundle\EventListener;

use Doctrine\ORM\EntityManager;
use OldSound\RabbitMqBundle\RabbitMq\Producer;

use TB\Bundle\FrontendBundle\Event\RoutePublishEvent;
use TB\Bundle\FrontendBundle\Event\UserFollowEvent;
use TB\Bundle\FrontendBundle\Event\UserUnfollowEvent;

use TB\Bundle\FrontendBundle\Entity\Activity;
use TB\Bundle\FrontendBundle\Entity\RoutePublishActivity;
use TB\Bundle\FrontendBundle\Entity\UserFollowActivity;
use TB\Bundle\FrontendBundle\Entity\UserUnfollowActivity;

/**
 * Create activity feed items from corresponding events
 */
class ActivityListener
{
    protected $em;
    protected $producer;

    public function __construct(EntityManager $em, Producer $producer)
    {
        $this->em = $em;
        $this->producer = $producer;
    }
    
    /**
     * Create a RoutePublishActivity from the RoutePublishEvent event
     */ 
    public function onRoutePublish(RoutePublishEvent $event)
    {
        $routePublishActivity = new RoutePublishActivity($event->getRoute(), $event->getUser());
        $this->em->persist($routePublishActivity);
        $this->em->flush();
        $this->publishMessage($routePublishActivity);
    }
    
    /**
     * Create a UserFollowActivity from the UserFollowEvent event
     */ 
    public function onUserFollow(UserFollowEvent $event)
    {
        $userFollowActivity = new UserFollowActivity($event->getFollowingUser(), $event->getFollowedUser());
        $this->em->persist($userFollowActivity);
        $this->em->flush();
        $this->publishMessage($userFollowActivity);
    }
    
    /**
     * Create a UserUnFollowActivity from the UserUnfollowEvent event
     */ 
    public function onUserUnfollow(UserUnfollowEvent $event)
    {
        $userUnfollowActivity = new UserUnfollowActivity($event->getUnfollowingUser(), $event->getUnfollowedUser());
        $this->em->persist($userUnfollowActivity);
        $this->em->flush();
        $this->publishMessage($userUnfollowActivity);
    }
    
    /**
     * Publishes a message to RabbitMQ
     */
    protected function publishMessage(Activity $activity)
    {   
        $this->producer->setContentType('application/json');
        $this->producer->publish(json_encode($activity->exportMessage()));
    }
}