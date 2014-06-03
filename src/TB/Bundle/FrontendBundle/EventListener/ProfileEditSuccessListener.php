<?php

namespace TB\Bundle\FrontendBundle\EventListener;

use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Redirect to the homepage after editing the profile
 */
class ProfileEditSuccessListener implements EventSubscriberInterface
{
    private $router;
    private $container;

    public function __construct(ContainerInterface $container, UrlGeneratorInterface $router)
    {
        $this->container = $container;
        $this->router = $router;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FOSUserEvents::PROFILE_EDIT_SUCCESS => 'onProfileEditSuccess'
        );
    }

    public function onProfileEditSuccess(FormEvent $event)
    {
        $user = $this->container->get('security.context')->getToken()->getUser();
        $url = $this->router->generate('profile', ['name' => $user->getName()]);

        $event->setResponse(new RedirectResponse($url));
    }
}