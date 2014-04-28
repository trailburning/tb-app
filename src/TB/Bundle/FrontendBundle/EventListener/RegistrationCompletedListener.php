<?php 

namespace TB\Bundle\FrontendBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Event\FilterUserResponseEvent;

/**
 * 
 */
class RegistrationCompletedListener
{

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    /**
     * 
     */ 
    public function onRegistrationCompleted(FilterUserResponseEvent $event)
    {
        if ($event->getUser()->getNewsletter() == true) {
            $mailproxy = $this->container->get('tb.mailproxy');
            $mailproxy->post($event->getUser()->getEmail());
        }
    }
}
