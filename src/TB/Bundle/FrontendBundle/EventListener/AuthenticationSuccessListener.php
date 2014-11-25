<?php 

namespace TB\Bundle\FrontendBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;
use TB\Bundle\FrontendBundle\Util\FacebookConnector;

/**
 * 
 */
class AuthenticationSuccessListener
{
    protected $container;
    protected $em;
    protected $facebookConnector;

    public function __construct(ContainerInterface $container, EntityManager $em, FacebookConnector $facebookConnector)
    {
        $this->container = $container;
        $this->em = $em;
        $this->facebookConnector = $facebookConnector;
    }
    
    /**
     * Updates the gravater image 
     */ 
    public function onAuthenticationSuccess(AuthenticationEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();
        $user->updateAvatarGravatar();
        if ($user->getOAuthService() == 'facebook') {
            $picture = $this->facebookConnector->getProfilePicture($user->getOAuthId());
            $user->setAvatarFacebook($picture);
        }
        
        $this->em->persist($user);
        $this->em->flush();
    }
}
