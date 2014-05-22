<?php 

namespace TB\Bundle\APIBundle\Tests\EventListener;

use TB\Bundle\FrontendBundle\Tests\AbstractFrontendTest;
use TB\Bundle\FrontendBundle\Event\RouteUpdateEvent;
use TB\Bundle\FrontendBundle\Entity\Route;
use TB\Bundle\FrontendBundle\Entity\UserProfile;
use FOS\UserBundle\Event\FilterUserResponseEvent;

class RouteUpdateListenerTest extends AbstractFrontendTest
{

    /**
     * Test that a routeShareImage message is sent to RabbitMQ when the tb.route_update event gets dispatched
     */
    public function testOnRouteUpdate()
    {
        // Replace the RabbitMQ Producer Service with a Stub
        $producer = $this->getMockBuilder('OldSound\RabbitMqBundle\RabbitMq\Producer')
            ->disableOriginalConstructor()
            ->getMock();
        // Test that the publish() method gets called three times, two times when two Routes are created from fixtures,
        // and once when the tb.route_publish Event is fired manually in this test
        $producer->expects($this->once())
            ->method('publish')
            ->will($this->returnCallback(array($this, 'assertAMQPMessage'))); // Use this callback to verify AMQP message 
        $this->getContainer()->set('old_sound_rabbit_mq.main_producer', $producer);
        
        $this->loadFixtures([]);
            
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        
        $route= new Route();
        $user = new UserProfile();
        
        //  get the event dispatcher and dispathe the tb.route_publish manually
        $dispatcher = $this->getContainer()->get('event_dispatcher');
        $event = new RouteUpdateEvent($route, $user);
        $dispatcher->dispatch('tb.route_update', $event);
        
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
        $this->assertObjectHasAttribute('type', $obj,
            'The message has the type attribute');
        $this->assertContains($obj->type, ['routeShareImage'], 
            'The type field contains one of the valid values');
    }
    
}