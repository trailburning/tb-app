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
     * Post the users email to createsend mailinglist when the newsletter checkbox is ticked.
     */ 
    public function onRegistrationCompleted(FilterUserResponseEvent $event)
    {
        $mailproxy = $this->container->get('tb.mailproxy');
        $mailproxy->sendWelcomeMail($event->getUser()->getEmail(), $event->getUser()->getFirstName());
    }
}
