<?php 

namespace TB\Bundle\APIBundle\Tests\EventListener;

use TB\Bundle\FrontendBundle\Tests\AbstractFrontendTest;
use TB\Bundle\FrontendBundle\Event\RoutePublishEvent;
use TB\Bundle\FrontendBundle\Event\UserFollowEvent;
use TB\Bundle\FrontendBundle\Event\UserUnfollowEvent;

class ActivityListenerTest extends AbstractFrontendTest
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
        $producer->expects($this->exactly(3))
            ->method('publish')
            ->will($this->returnCallback(array($this, 'assertAMQPMessage'))); // Use this callback to verify AMQP message 
        $this->getContainer()->set('old_sound_rabbit_mq.main_producer', $producer);
        
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteData',
        ]);
            
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        
        $route = $em
            ->getRepository('TBFrontendBundle:Route')
            ->findOneBySlug('grunewald');
        if (!$route) {
            $this->fail('Missing Route with slug "grunewald" in test DB');
        }
        
        //  get the event dispatcher and dispathe the tb.route_publish manually
        $dispatcher = $this->getContainer()->get('event_dispatcher');
        $event = new RoutePublishEvent($route, $route->getUser());
        $dispatcher->dispatch('tb.route_publish', $event);
        
        $activity = $em
            ->getRepository('TBFrontendBundle:RoutePublishActivity')
            ->findOneByObjectId($route->getId());
        
        if (!$activity) {
            $this->fail('RoutePublishActivity was not created for tb.route_publish event');
        }
        
        $this->assertEquals($route->getId(), $activity->getObject()->getId(), 
            'RoutePublishActivity with excpected objectId was created');
        $this->assertEquals($route->getUser()->getId(), $activity->getActor()->getId(),
            'RoutePublishActivity with excpected targetId was created');
        
    }
    
    /**
     * Test that a UserFollowActivity is created when the tb.user_follow event gets dispatched
     */
    public function testOnUserFollow()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\UserProfileData',
        ]);
            
        // Get Route from DB with the slug "grunewald"..
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $user = $em
            ->getRepository('TBFrontendBundle:User')
            ->findOneByName('mattallbeury');
        if (!$user) {
            $this->fail('Missing User with name "mattallbeury" in test DB');
        }
        
        $userToFollow = $em
            ->getRepository('TBFrontendBundle:User')
            ->findOneByName('paultran');
        if (!$userToFollow) {
            $this->fail('Missing User to follow with name "paultran" in test DB');
        }
        
        // Replace the RabbitMQ Producer Service with a Stub
        $producer = $this->getMockBuilder('OldSound\RabbitMqBundle\RabbitMq\Producer')
            ->disableOriginalConstructor()
            ->getMock();
        // Test that the publish() method gets called exactly once
        $producer->expects($this->once())
            ->method('publish')
            ->will($this->returnCallback(array($this, 'assertAMQPMessage'))); // Use this callback to verify AMQP message AMQPChannel;
        $this->getContainer()->set('old_sound_rabbit_mq.main_producer', $producer);
        
        //  get the event dispatcher and dispathe the tb.route_publish manually
        $dispatcher = $this->getContainer()->get('event_dispatcher');
        $event = new UserFollowEvent($user, $userToFollow);
        $dispatcher->dispatch('tb.user_follow', $event);
        
        $activity = $em
            ->getRepository('TBFrontendBundle:UserFollowActivity')
            ->findOneByObjectId($userToFollow->getId());
        
        if (!$activity) {
            $this->fail('UserFollowActivity was not created for tb.user_follow event');
        }
        
        $this->assertEquals($userToFollow->getId(), $activity->getObject()->getId(), 
            'UserFollowActivity with excpected objectId was created');
        $this->assertEquals($user->getId(), $activity->getActor()->getId(),
            'UserFollowActivity with excpected targetId was created');
        
    }
    
    /**
     * Test that a UserUnfollowEvent is created when the tb.user_unfollow event gets dispatched
     */
    public function testOnUserUnfollow()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\UserProfileData',
        ]);
            
        // Get Route from DB with the slug "grunewald"..
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $user = $em
            ->getRepository('TBFrontendBundle:User')
            ->findOneByName('mattallbeury');
        if (!$user) {
            $this->fail('Missing User with name "mattallbeury" in test DB');
        }
        
        $userToUnfollow = $em
            ->getRepository('TBFrontendBundle:User')
            ->findOneByName('paultran');
        if (!$userToUnfollow) {
            $this->fail('Missing User to follow with name "paultran" in test DB');
        }
        
        // Replace the RabbitMQ Producer Service with a Stub
        $producer = $this->getMockBuilder('OldSound\RabbitMqBundle\RabbitMq\Producer')
            ->disableOriginalConstructor()
            ->getMock();
        // Test that the publish() method gets called exactly once
        $producer->expects($this->once())
            ->method('publish')
            ->will($this->returnCallback(array($this, 'assertAMQPMessage'))); // Use this callback to verify AMQP message AMQPChannel;
        $this->getContainer()->set('old_sound_rabbit_mq.main_producer', $producer);
        
        //  Get the event dispatcher and dispathe the tb.route_publish manually
        $dispatcher = $this->getContainer()->get('event_dispatcher');
        $event = new UserUnfollowEvent($user, $userToUnfollow);
        $dispatcher->dispatch('tb.user_unfollow', $event);
        
        $activity = $em
            ->getRepository('TBFrontendBundle:UserUnfollowActivity')
            ->findOneByObjectId($userToUnfollow->getId());
        
        if (!$activity) {
            $this->fail('UserUnfollowActivity was not created for tb.user_unfollow event');
        }
        
        $this->assertEquals($userToUnfollow->getId(), $activity->getObject()->getId(), 
            'UserUnfollowActivity with excpected objectId was created');
        $this->assertEquals($user->getId(), $activity->getActor()->getId(),
            'UserUnfollowActivity with excpected targetId was created');
        
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
        $this->assertEquals('activity', $obj->type, 
            'The type field contains one of the valid values');
    }
    
}