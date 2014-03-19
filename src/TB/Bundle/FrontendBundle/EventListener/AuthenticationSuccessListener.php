<?php 

namespace TB\Bundle\FrontendBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;

/**
 * Updates the gravater image
 */
class AuthenticationSuccessListener
{
    protected $container;
    protected $em;

    public function __construct(ContainerInterface $container, EntityManager $em)
    {
        $this->container = $container;
        $this->em = $em;
    }
    
    public function onAuthenticationSuccess(AuthenticationEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();
        $user->updateAvatarGravatar();
        $this->em->persist($user);
        $this->em->flush();
    }
}
