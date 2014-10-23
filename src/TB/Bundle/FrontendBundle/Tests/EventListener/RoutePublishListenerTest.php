<?php 

namespace TB\Bundle\APIBundle\Tests\EventListener;

use TB\Bundle\FrontendBundle\Tests\AbstractFrontendTest;
use TB\Bundle\FrontendBundle\Event\RoutePublishEvent;
use TB\Bundle\FrontendBundle\Event\UserFollowEvent;
use TB\Bundle\FrontendBundle\Event\UserUnfollowEvent;
use TB\Bundle\FrontendBundle\Event\RouteLikeEvent;
use TB\Bundle\FrontendBundle\Event\RouteUndoLikeEvent;
use FOS\UserBundle\Event\FilterUserResponseEvent;

class RoutePublishListenerTest extends AbstractFrontendTest
{

    /**
     * Test that a RoutePublishActivity is created when the tb.route_publish event gets dispatched
     */
    public function testOnRoutePublish()
    {
        // Replace the RabbitMQ Producer Service with a Stub
        $producer = $this->getMockBuilder('OldSound\RabbitMqBundle\RabbitMq\Producer')
            ->disableOriginalConstructor()
            ->getMock();
        // Test that the publish() method gets called three times, two times when two Routes are created from fixtures,
        // and once when the tb.route_publish Event is fired manually in this test
        $producer->expects($this->any())
            ->method('publish')
            ->will($this->returnCallback(array($this, 'assertAMQPMessage'))); // Use this callback to verify AMQP message 
        $this->getContainer()->set('old_sound_rabbit_mq.main_producer', $producer);
        
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteData',
        ]);
            
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $route= $this->getRoute('grunewald');
        // The published date is null bofore RoutePublishEvent
        $route->setPublishedDate(null);
        
        //  get the event dispatcher and dispatch the tb.route_publish manually
        $dispatcher = $this->getContainer()->get('event_dispatcher');
        $event = new RoutePublishEvent($route, $route->getUser());
        $dispatcher->dispatch('tb.route_publish', $event);
        
        $em->refresh($route);
        $this->assertNotNull($route->getPublishedDate());
    }
    
    /**
     * Checks the message sent to RabbitMQ 
     */
    public function assertAMQPMessage($message)
    {
        $this->assertJson($message);
        $obj = json_decode($message);
        $this->assertObjectHasAttribute('id', $obj,
            'The message has the id attribute');
        $this->assertGreaterThan(0, $obj->id,
            'The id value is grater than 0');
        $this->assertObjectHasAttribute('type', $obj,
            'The message has the type attribute');
        $this->assertContains($obj->type, ['activity', 'routeShareImage', 'routeIndex'], 
            'The type field contains one of the valid values');
    }
    
}