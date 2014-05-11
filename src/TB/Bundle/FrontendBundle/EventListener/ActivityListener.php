<?php 

namespace TB\Bundle\FrontendBundle\EventListener;

use Doctrine\ORM\EntityManager;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use TB\Bundle\FrontendBundle\Util\ActivityFeedGenerator;

use TB\Bundle\FrontendBundle\Event\RoutePublishEvent;
use TB\Bundle\FrontendBundle\Event\UserFollowEvent;
use TB\Bundle\FrontendBundle\Event\UserUnfollowEvent;
use TB\Bundle\FrontendBundle\Event\RouteLikeEvent;
use TB\Bundle\FrontendBundle\Event\RouteUndoLikeEvent;

use TB\Bundle\FrontendBundle\Entity\Activity;
use TB\Bundle\FrontendBundle\Entity\RoutePublishActivity;
use TB\Bundle\FrontendBundle\Entity\UserFollowActivity;
use TB\Bundle\FrontendBundle\Entity\UserUnfollowActivity;
use TB\Bundle\FrontendBundle\Entity\RouteLikeActivity;
use TB\Bundle\FrontendBundle\Entity\RouteUndoLikeActivity;
use TB\Bundle\FrontendBundle\Entity\UserRegisterActivity;

use FOS\UserBundle\Event\FilterUserResponseEvent;

/**
 * Create activity feed items from corresponding events
 */
class ActivityListener
{
    protected $em;
    protected $producer;
    protected $generator;

    public function __construct(EntityManager $em, Producer $producer, ActivityFeedGenerator $generator)
    {
        $this->em = $em;
        $this->producer = $producer;
        $this->generator = $generator;
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
    }
    
    /**
     * Create a RouteLikeActivity from the RouteLikeEvent event
     */ 
    public function onRouteLike(RouteLikeEvent $event)
    {
        $routeLikeActivity = new RouteLikeActivity($event->getRoute(), $event->getUser());
        $this->em->persist($routeLikeActivity);
        $this->em->flush();
        $this->publishMessage($routeLikeActivity);
    }
    
    /**
     * Create a RouteUndoLikeActivity from the RouteUndoLikeEvent event
     */ 
    public function onRouteUndoLike(RouteUndoLikeEvent $event)
    {
        $routeUndoLikeActivity = new RouteUndoLikeActivity($event->getRoute(), $event->getUser());
        $this->em->persist($routeUndoLikeActivity);
        $this->em->flush();
    }
    
    /**
     * Create a UserRegisterActivity from a FilterUserResponseEvent event
     */ 
    public function onUserRegister(FilterUserResponseEvent $event)
    {
        $userRegisterActivity = new UserRegisterActivity($event->getUser());
        $this->em->persist($userRegisterActivity);
        $this->em->flush();
        $this->generator->createFeedFromActivity($userRegisterActivity);
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