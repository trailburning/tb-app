<?php 

namespace TB\Bundle\FrontendBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;

use TB\Bundle\FrontendBundle\Entity\Route;

use TB\Bundle\FrontendBundle\Event\RoutePublishEvent;
use TB\Bundle\FrontendBundle\Event\RouteUpdateEvent;

/**
 * Listen for Dcotrine postPersist and postUpdate events
 */
class EntityEventSubscriber implements EventSubscriber
{
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
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
        $this->update($args);
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $this->update($args);
    }

    public function update(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $em = $args->getEntityManager();

        if ($entity instanceof Route) {
            // Create custom Event RoutePublishEvent named tb.route_publish when a Route gets published
            $uow = $em->getUnitOfWork();
            $uow->computeChangeSets();
            // Test that publish was set from false to true  by computing a changeset
            $changeset = $uow->getEntityChangeSet($entity);
            // publish is in the changeset, the new differs from the old value, publish is true
            if (isset($changeset['publish']) && $entity->getPublish() === true) {
                if ($changeset['publish'][0] !== $changeset['publish'][1]) {
                    // published is changed from false to true
                    $event = new RoutePublishEvent($entity, $entity->getUser());
                    $dispatcher = $this->container->get('event_dispatcher'); 
                    $dispatcher->dispatch('tb.route_publish', $event);
                }
                $event = new RouteUpdateEvent($entity, $entity->getUser());
                $dispatcher = $this->container->get('event_dispatcher'); 
                $dispatcher->dispatch('tb.route_update', $event);
            }        
        }
    }
}