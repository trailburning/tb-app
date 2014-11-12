<?php 

namespace TB\Bundle\FrontendBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use FOS\UserBundle\Event\UserEvent;

/**
 * 
 */
class RegistrationInitializeListener
{

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    /**
     *
     */ 
    public function onRegistrationInitialize(UserEvent $event)
    {
        
    }
}
