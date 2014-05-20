<?php 

namespace TB\Bundle\FrontendBundle\EventListener;

use Doctrine\ORM\EntityManager;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use TB\Bundle\FrontendBundle\Event\RoutePublishEvent;

/**
 *
 */
class RoutePublishListener
{
    protected $em;

    public function __construct(EntityManager $em, Producer $producer)
    {
        $this->em = $em;
        $this->producer = $producer;
    }
    
    /**
     * Send a message to RabbitMQ to create a Facebook share image,
     * set the published date to the route
     */ 
    public function onRoutePublish(RoutePublishEvent $event)
    {   
        $route = $event->getRoute();
        $route->setPublishedDate(new \DateTime("now"));
        $this->em->persist($route);
        $this->em->flush();
        
        $message = [
            'type' => 'routeShareImage',
            'id' => $event->getRoute()->getId(),
        ];
        
        $this->producer->setContentType('application/json');
        $this->producer->publish(json_encode($message));
    }   
}