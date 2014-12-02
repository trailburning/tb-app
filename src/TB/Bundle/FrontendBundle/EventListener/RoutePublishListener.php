<?php 

namespace TB\Bundle\FrontendBundle\EventListener;

use Doctrine\ORM\EntityManager;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use TB\Bundle\FrontendBundle\Event\RoutePublishEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Exception;

/**
 *
 */
class RoutePublishListener
{
    
    protected $em;
    
    protected $producer;
    
    protected $container;
    
    protected $router;

    public function __construct(EntityManager $em, Producer $producer, ContainerInterface $container)
    {
        $this->em = $em;
        $this->producer = $producer;
        $this->container = $container;
    }
    
    /**
     * Sets the Route published Date
     * Creates the bitly url
     * Sends a message to RabbitMQ to create a Facebook share image,
     */ 
    public function onRoutePublish(RoutePublishEvent $event)
    {   
        $route = $event->getRoute();
        $route->setPublishedDate(new \DateTime("now"));
        
        $bitly = $this->container->get('tb.bitly_client');
        $url = 'http://www.trailburning.com/trail/' . $route->getSlug();

        try {
            $response= $bitly->shorten([
                'longUrl' => $url,
            ]);

            $route->setBitlyUrl($response['url']);
        } catch (Exception $e) {}

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