<?php 

namespace TB\Bundle\FrontendBundle\EventListener;

use Doctrine\ORM\EntityManager;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use TB\Bundle\FrontendBundle\Event\RoutePublishEvent;
use TB\Bundle\FrontendBundle\Event\RouteUpdateEvent;
use FOS\UserBundle\Event\FilterUserResponseEvent;

/**
 *
 */
class SearchListener
{
    protected $em;

    public function __construct(EntityManager $em, Producer $producer)
    {
        $this->em = $em;
        $this->producer = $producer;
    }
    
    /**
     * Send a message to RabbitMQ to index the route in elasticsearch
     */ 
    public function onRoutePublish(RoutePublishEvent $event)
    {   
        $this->routeIndex($event->getRoute());
    }   
    
    /**
     * Send a message to RabbitMQ to update the route in elasticsearch
     */ 
    public function onRouteUpdate(RouteUpdateEvent $event)
    {   
        $this->routeIndex($event->getRoute());
    } 
    
    protected function routeIndex($route) 
    {
        $message = [
            'type' => 'routeIndex',
            'id' => $route->getId(),
        ];
        
        $this->producer->setContentType('application/json');
        $this->producer->publish(json_encode($message));
    }
    
    /**
     * Send a message to RabbitMQ to create the user_profile in elasticsearch
     */ 
    public function onUserCreate(FilterUserResponseEvent $event)
    {   
        $message = [
            'type' => 'userIndex',
            'id' => $event->getUser()->getId(),
        ];
        
        $this->producer->setContentType('application/json');
        $this->producer->publish(json_encode($message));
    }  
    
}