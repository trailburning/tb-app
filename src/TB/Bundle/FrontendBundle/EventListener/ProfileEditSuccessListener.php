<?php

namespace TB\Bundle\FrontendBundle\EventListener;

use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Redirect to the homepage after editing the profile
 */
class ProfileEditSuccessListener implements EventSubscriberInterface
{
    private $router;

    public function __construct(UrlGeneratorInterface $router)
    {
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
        $url = $this->router->generate('homepage');

        $event->setResponse(new RedirectResponse($url));
    }
}