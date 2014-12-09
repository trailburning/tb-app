<?php 

namespace TB\Bundle\FrontendBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;

use TB\Bundle\FrontendBundle\Entity\Route;
use TB\Bundle\FrontendBundle\Entity\CampaignRoute;
use TB\Bundle\FrontendBundle\Entity\User;

use TB\Bundle\FrontendBundle\Event\RoutePublishEvent;
use TB\Bundle\FrontendBundle\Event\RouteUpdateEvent;
use TB\Bundle\FrontendBundle\Event\CampaignRouteAcceptEvent;

use TB\Bundle\FrontendBundle\Service\Mailproxy;

/**
 * Listen for Dcotrine postPersist and postUpdate events
 */
class EntityEventSubscriber implements EventSubscriber
{
    public function __construct(ContainerInterface $container, Mailproxy $mailproxy)
    {
        $this->container = $container;
        $this->mailproxy = $mailproxy;
    }
    
    public function getSubscribedEvents()
    {
        return array(
            'postPersist',
            'postUpdate',
        );
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof Route) {
            $this->dispatchRouteEvents($args, $entity);
        } elseif ($entity instanceof User) {
            $this->subscribeUnsubscribeNewsletter($args, $entity);
        }
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof Route) {
            $this->dispatchRouteEvents($args, $entity);
        } elseif ($entity instanceof CampaignRoute) {
            $this->dispatchCampaignRouteAcceptEvent($args, $entity);
        } elseif ($entity instanceof User) {
            $this->subscribeUnsubscribeNewsletter($args, $entity);
        }
    }

    protected function dispatchRouteEvents(LifecycleEventArgs $args, $route)
    {        
        $em = $args->getEntityManager();
        $event = new RouteUpdateEvent($route, $route->getUser());
        $dispatcher = $this->container->get('event_dispatcher'); 
        $dispatcher->dispatch('tb.route_update', $event);
    
        // Create custom Event RoutePublishEvent named tb.route_publish when a Route gets published
        $uow = $em->getUnitOfWork();
        $uow->computeChangeSets();
        // Test that publish was set from false to true  by computing a changeset
        $changeset = $uow->getEntityChangeSet($route);
        // publish is in the changeset, the new differs from the old value, publish is true
        if (isset($changeset['publish']) && $route->getPublish() === true && $changeset['publish'][0] !== $changeset['publish'][1]) {
            // published is changed from false to true
            $event = new RoutePublishEvent($route, $route->getUser());
            $dispatcher = $this->container->get('event_dispatcher'); 
            $dispatcher->dispatch('tb.route_publish', $event);
        }
    }
    
    public function dispatchCampaignRouteAcceptEvent(LifecycleEventArgs $args, $campaignRoute) 
    {
        $em = $args->getEntityManager();
        
        // Create custom Event CampaignRouteAcceptEvent named tb.campaign_route_accept when a Route gets accepted to a Campaign
        $uow = $em->getUnitOfWork();
        $uow->computeChangeSets();
        // Test that publish was set from false to true  by computing a changeset
        $changeset = $uow->getEntityChangeSet($campaignRoute);
        // accept is in the changeset, the newsletterw differs from the old value, accept is true TODO: field accept is currently not present 
        if (true || (isset($changeset['publish']) && $campaignRoute->getPublish() === true && $changeset['publish'][0] !== $changeset['publish'][1])) {
            // accept is changed from false to true
            $event = new CampaignRouteAcceptEvent($campaignRoute->getCampaign()->getUser(), $campaignRoute->getRoute(), $campaignRoute->getCampaign());
            $dispatcher = $this->container->get('event_dispatcher'); 
            $dispatcher->dispatch('tb.campaign_route_accept', $event);
        }
    }
    
    public function subscribeUnsubscribeNewsletter(LifecycleEventArgs $args, $user) 
    {
        $em = $args->getEntityManager();
    
        $uow = $em->getUnitOfWork();
        $uow->computeChangeSets();
        // Test that publish was set from false to true  by computing a changeset
        $changeset = $uow->getEntityChangeSet($user);
        // newsletter is in the changeset, the new value differs from the old value
        if (isset($changeset['newsletter']) && $changeset['newsletter'][0] !== $changeset['newsletter'][1]) {
            if ($user->getNewsletter() == true) {
                // user subscribed to newsletter
                $this->mailproxy->addNewsletterSubscriber($user->getEmail());
            } else {
                // user unsubscribed from newsletter
                $this->mailproxy->removeNewsletterSubscriber($user->getEmail());
            }
        }
    }
}