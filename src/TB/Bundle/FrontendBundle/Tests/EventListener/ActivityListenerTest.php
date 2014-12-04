<?php 

namespace TB\Bundle\APIBundle\Tests\EventListener;

use TB\Bundle\FrontendBundle\Tests\AbstractFrontendTest;
use TB\Bundle\FrontendBundle\Event\RoutePublishEvent;
use TB\Bundle\FrontendBundle\Event\UserFollowEvent;
use TB\Bundle\FrontendBundle\Event\UserUnfollowEvent;
use TB\Bundle\FrontendBundle\Event\RouteLikeEvent;
use TB\Bundle\FrontendBundle\Event\RouteUndoLikeEvent;
use TB\Bundle\FrontendBundle\Event\CampaignRouteAcceptEvent;
use TB\Bundle\FrontendBundle\Event\CampaignFollowEvent;
use TB\Bundle\FrontendBundle\Event\CampaignUnfollowEvent;
use FOS\UserBundle\Event\FilterUserResponseEvent;

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
        // Test that the publish() method gets called sic times, two times when two Routes are created from fixtures,
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
        
        //  get the event dispatcher and dispatch the tb.route_publish manually
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
            'RoutePublishActivity with excpected actorId was created');
        
    }
    
    /**
     * Test that a UserFollowActivity is created when the tb.user_follow event gets dispatched
     */
    public function testOnUserFollow()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\UserProfileData',
        ]);
            
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $user = $this->getUser('mattallbeury');
        $userToFollow = $this->getUser('paultran');
        
        // Replace the RabbitMQ Producer Service with a Stub
        $producer = $this->getMockBuilder('OldSound\RabbitMqBundle\RabbitMq\Producer')
            ->disableOriginalConstructor()
            ->getMock();
        // Test that the publish() method gets called exactly once
        $producer->expects($this->once())
            ->method('publish')
            ->will($this->returnCallback(array($this, 'assertAMQPMessage'))); // Use this callback to verify AMQP message AMQPChannel;
        $this->getContainer()->set('old_sound_rabbit_mq.main_producer', $producer);
        
        //  get the event dispatcher and dispatch the tb.route_publish manually
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
            'UserFollowActivity with excpected actorId was created');
        
    }
    
    /**
     * Test that a UserUnfollowEvent is created when the tb.user_unfollow event gets dispatched
     */
    public function testOnUserUnfollow()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\UserProfileData',
        ]);
        
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $user = $this->getUser('mattallbeury');
        $userToUnfollow = $this->getUser('paultran');
        
        // Replace the RabbitMQ Producer Service with a Stub
        $producer = $this->getMockBuilder('OldSound\RabbitMqBundle\RabbitMq\Producer')
            ->disableOriginalConstructor()
            ->getMock();
        // Test that the publish() method gets not called
        $producer->expects($this->never())
            ->method('publish')
            ->will($this->returnCallback(array($this, 'assertAMQPMessage'))); // Use this callback to verify AMQP message AMQPChannel;
        $this->getContainer()->set('old_sound_rabbit_mq.main_producer', $producer);
        
        //  Get the event dispatcher and dispatch the tb.route_publish manually
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
            'UserUnfollowActivity with excpected actorId was created');
        
    }
    
    /**
     * Test that a UserFollowActivity is created when the tb.user_follow event gets dispatched
     */
    public function testOnRouteLike()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteData',
        ]);
            
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $user = $this->getUser('mattallbeury');
        $route = $this->getRoute('grunewald');
        
        // Replace the RabbitMQ Producer Service with a Stub
        $producer = $this->getMockBuilder('OldSound\RabbitMqBundle\RabbitMq\Producer')
            ->disableOriginalConstructor()
            ->getMock();
        // Test that the publish() method gets called exactly once
        $producer->expects($this->once())
            ->method('publish')
            ->will($this->returnCallback(array($this, 'assertAMQPMessage'))); // Use this callback to verify AMQP message AMQPChannel;
        $this->getContainer()->set('old_sound_rabbit_mq.main_producer', $producer);
        
        //  get the event dispatcher and dispatch the tb.route_publish manually
        $dispatcher = $this->getContainer()->get('event_dispatcher');
        $event = new RouteLikeEvent($route, $user);
        $dispatcher->dispatch('tb.route_like', $event);
        
        $activity = $em
            ->getRepository('TBFrontendBundle:RouteLikeActivity')
            ->findOneByObjectId($route->getId());
        
        if (!$activity) {
            $this->fail('RouteLikeActivity was not created for tb.route_like event');
        }
        
        $this->assertEquals($route->getId(), $activity->getObject()->getId(), 
            'RouteLikeActivity with excpected objectId was created');
        $this->assertEquals($user->getId(), $activity->getActor()->getId(),
            'RouteLikeActivity with excpected actorId was created');
        
    }
    
    /**
     * Test that a RouteUndoLikeEvent is created when the tb.route_undolike event gets dispatched
     */
    public function testOnRouteUndoLike()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteData',
        ]);
            
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $user = $this->getUser('mattallbeury');
        $route = $this->getRoute('grunewald');
        
        // Replace the RabbitMQ Producer Service with a Stub
        $producer = $this->getMockBuilder('OldSound\RabbitMqBundle\RabbitMq\Producer')
            ->disableOriginalConstructor()
            ->getMock();
        // Test that the publish() method gets not called
        $producer->expects($this->never())
            ->method('publish')
            ->will($this->returnCallback(array($this, 'assertAMQPMessage'))); // Use this callback to verify AMQP message AMQPChannel;
        $this->getContainer()->set('old_sound_rabbit_mq.main_producer', $producer);
        
        //  Get the event dispatcher and dispatch the tb.route_publish manually
        $dispatcher = $this->getContainer()->get('event_dispatcher');
        $event = new RouteUndoLikeEvent($route, $user);
        $dispatcher->dispatch('tb.route_undolike', $event);
        
        $activity = $em
            ->getRepository('TBFrontendBundle:RouteUndoLikeActivity')
            ->findOneByObjectId($route->getId());
        
        if (!$activity) {
            $this->fail('RouteUndoLikeActivity was not created for tb.route_undolike event');
        }
        
        $this->assertEquals($route->getId(), $activity->getObject()->getId(), 
            'RouteUndoLikeActivity with excpected objectId was created');
        $this->assertEquals($user->getId(), $activity->getActor()->getId(),
            'RouteUndoLikeActivity with excpected actorId was created');
        
    }
    
    /**
     * Test that a UserRegisterActivity is created when the fos_user.registration.completed event gets dispatched
     */
    public function testOnUserRegister()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\UserProfileData',
        ]);
            
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $user = $this->getUser('mattallbeury');
        
        // Replace the ActivityFeedgenerator Service with a Stub
        $generator = $this->getMockBuilder('TB\Bundle\FrontendBundle\Service\ActivityFeedGenerator')
            ->disableOriginalConstructor()
            ->getMock();
        // Test that the createFeedFromActivity() method gets called exactly once
        $generator->expects($this->once())
            ->method('createFeedFromActivity');
        $this->getContainer()->set('tb.activity.feed.generator', $generator);
        
        //  get the event dispatcher and dispatch the tb.route_publish manually
        $client = static::createClient();
        $client->request('GET', '/');
        $dispatcher = $this->getContainer()->get('event_dispatcher');
        $event = new FilterUserResponseEvent($user, $client->getRequest(), $client->getResponse());
        $dispatcher->dispatch('fos_user.registration.completed', $event);
        
        $activity = $em
            ->getRepository('TBFrontendBundle:UserRegisterActivity')
            ->findOneByActorId($user->getId());
        
        if (!$activity) {
            $this->fail('UserRegisterActivity was not created for fos_user.registration.completed event');
        }
        
        $this->assertEquals($user->getId(), $activity->getActor()->getId(),
            'UserRegisterActivity with excpected actorId was created');
        
    }
    
    /**
     * Test that a CampaignRouteAcceptActivity is created when the tb.campaign_route_accept event gets dispatched
     */
    public function testOnCampaignRouteAccept()
    {
        // Replace the RabbitMQ Producer Service with a Stub
        $producer = $this->getMockBuilder('OldSound\RabbitMqBundle\RabbitMq\Producer')
            ->disableOriginalConstructor()
            ->getMock();
        // Test that the publish() method gets called one time, two times when two Routes are created from fixtures,
        // and once when the tb.route_publish Event is fired manually in this test
        $producer->expects($this->any())
            ->method('publish')
            ->will($this->returnCallback(array($this, 'assertAMQPMessage'))); // Use this callback to verify AMQP message
        $this->getContainer()->set('old_sound_rabbit_mq.main_producer', $producer);

        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\CampaignData',
        ]);

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $campaign= $this->getCampaign('urbantrails-london');
        $route = $campaign->getCampaignRoutes()[0]->getRoute();

        //  get the event dispatcher and dispatch the tb.route_publish manually
        $dispatcher = $this->getContainer()->get('event_dispatcher');
        $event = new CampaignRouteAcceptEvent($route->getUser(), $route, $campaign);
        $dispatcher->dispatch('tb.campaign_route_accept', $event);

        $activity = $em
            ->getRepository('TBFrontendBundle:CampaignRouteAcceptActivity')
            ->findOneByObjectId($route->getId());

        if (!$activity) {
            $this->fail('CampaignRouteAcceptActivity was not created for tb.campaign_route_accept event');
        }

        $this->assertEquals($route->getUser()->getId(), $activity->getActor()->getId(),
            'CampaignRouteAcceptActivity with excpected actorId was created');
        $this->assertEquals($route->getId(), $activity->getObject()->getId(),
            'CampaignRouteAcceptActivity with excpected objectId was created');
        $this->assertEquals($campaign->getId(), $activity->getTarget()->getId(),
            'CampaignRouteAcceptActivity with excpected targetId was created');
    }
 
    /**
     * Test that a CampaignFollowActivity is created when the tb.campaign_follow event gets dispatched
     */
    public function testOnCampaignFollow()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\CampaignData',
        ]);
            
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $user = $this->getUser('mattallbeury');
        $campaign = $this->getCampaign('urbantrails-london');
        
        // Replace the RabbitMQ Producer Service with a Stub
        $producer = $this->getMockBuilder('OldSound\RabbitMqBundle\RabbitMq\Producer')
            ->disableOriginalConstructor()
            ->getMock();
        // Test that the publish() method gets called exactly once
        $producer->expects($this->once())
            ->method('publish')
            ->will($this->returnCallback(array($this, 'assertAMQPMessage'))); // Use this callback to verify AMQP message AMQPChannel;
        $this->getContainer()->set('old_sound_rabbit_mq.main_producer', $producer);
        
        //  get the event dispatcher and dispatch the tb.campaign_follow event manually
        $dispatcher = $this->getContainer()->get('event_dispatcher');
        $event = new CampaignFollowEvent($user, $campaign);
        $dispatcher->dispatch('tb.campaign_follow', $event);
        
        $activity = $em
            ->getRepository('TBFrontendBundle:CampaignFollowActivity')
            ->findOneByObjectId($campaign->getId());
        
        if (!$activity) {
            $this->fail('CampaignFollowActivity was not created for tb.campaign_follow event');
        }
        
        $this->assertEquals($campaign->getId(), $activity->getObject()->getId(), 
            'CampaignFollowActivity with excpected objectId was created');
        $this->assertEquals($user->getId(), $activity->getActor()->getId(),
            'CampaignFollowActivity with excpected actorId was created');
        
    }
    
    /**
     * Test that a CampaignUnfollowEvent is created when the tb.campaign_unfollow event gets dispatched
     */
    public function testOnCampaignUnfollow()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\CampaignData',
        ]);
        
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $user = $this->getUser('mattallbeury');
        $campaign = $this->getCampaign('urbantrails-london');
        
        // Replace the RabbitMQ Producer Service with a Stub
        $producer = $this->getMockBuilder('OldSound\RabbitMqBundle\RabbitMq\Producer')
            ->disableOriginalConstructor()
            ->getMock();
        // Test that the publish() method gets not called
        $producer->expects($this->never())
            ->method('publish')
            ->will($this->returnCallback(array($this, 'assertAMQPMessage'))); // Use this callback to verify AMQP message AMQPChannel;
        $this->getContainer()->set('old_sound_rabbit_mq.main_producer', $producer);
        
        //  Get the event dispatcher and dispatch the tb.campaign_unfollow event manually
        $dispatcher = $this->getContainer()->get('event_dispatcher');
        $event = new CampaignUnfollowEvent($user, $campaign);
        $dispatcher->dispatch('tb.campaign_unfollow', $event);
        
        $activity = $em
            ->getRepository('TBFrontendBundle:CampaignUnfollowActivity')
            ->findOneByObjectId($campaign->getId());
        
        if (!$activity) {
            $this->fail('CampaignUnfollowActivity was not created for tb.campaign_unfollow event');
        }
        
        $this->assertEquals($campaign->getId(), $activity->getObject()->getId(), 
            'CampaignUnfollowActivity with excpected objectId was created');
        $this->assertEquals($user->getId(), $activity->getActor()->getId(),
            'CampaignUnfollowActivity with excpected actorId was created');
        
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