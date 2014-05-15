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
     * Send a message to RabbitMQ to create a Facebook share image
     */ 
    public function onRoutePublish(RoutePublishEvent $event)
    {   
        $message = [
            'type' => 'routeShareImage',
            'id' => $event->getRoute()->getId(),
        ];
        
        $this->producer->setContentType('application/json');
        $this->producer->publish(json_encode($message));
    }   
}