<?php 

namespace TB\Bundle\FrontendBundle\Tests\Service;

use TB\Bundle\FrontendBundle\Tests\AbstractFrontendTest;
use TB\Bundle\FrontendBundle\Entity\RoutePublishActivity;
use TB\Bundle\FrontendBundle\Entity\UserFollowActivity;
use TB\Bundle\FrontendBundle\Entity\UserUnfollowActivity;
use TB\Bundle\FrontendBundle\Entity\UserRegisterActivity;
use TB\Bundle\FrontendBundle\Entity\CampaignFollowActivity;
use TB\Bundle\FrontendBundle\Entity\CampaignRouteAcceptActivity;

class ActivityFeedGeneratorTest extends AbstractFrontendTest
{

    /**
     * Test JSON serialization of RoutePublishActivity
     */
    public function testSerializeRoutePublishActivity()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\ActivityStreamData',
        ]); 
        
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $user = $this->getUser('paultran');
        
        $activities = $em
            ->getRepository('TBFrontendBundle:Activity')
            ->findAll();
    
        if (count($activities) == 0) {
            $this->fail('Missing Activity items in test DB');
        }
        
        $generator = $this->getContainer()->get('tb.activity.feed.generator');
        
        foreach ($activities as $activity) {
            $generator->createFeedFromActivity($activity);
        }
        
        $feed = $generator->getFeedForUser($user->getId());
            
        $this->assertTrue(isset($feed['items']), 'Activity contains items array');
        $this->assertInternalType('array', $feed['items'], 'Activity field items is of type array');
        $this->assertTrue(isset($feed['totalItems']), 'Activity contains totalItems field');
        $this->assertEquals(3, $feed['totalItems'], 'Activity totalItems field value is"3"');
        $this->assertTrue(isset($feed['newItems']), 'Activity contains newItems field');
        $this->assertEquals(3, $feed['newItems'], 'Activity newItems field value is"3"');
    }
    
    public function testCreateFeedFromRoutePublishActivity()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\ActivityStreamData',
        ]);
        
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
    
        $query = $em->createQuery('SELECT a FROM TBFrontendBundle:RoutePublishActivity a ORDER BY a.id ASC');
        try {
            $activity = $query->setMaxResults(1)->getSingleResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            $this->fail('No RoutePublishActivity found in Test DB');
        }
        
        $generator = $this->getContainer()->get('tb.activity.feed.generator');
        
        $generator->createFeedFromActivity($activity);
        
        // Every follower of the creator of the route get a UserActivity item
        $this->assertNotEquals(0, count($activity->getActor()->getMyFollower()),
            'The creator of the Route has more than 0 follower');
        // check for every follower if the UserActivity was created
        foreach ($activity->getActor()->getMyFollower() as $follower) {
            $userActivity = $follower->getUserActivities()[0];
            $this->assertEquals($activity->getId(), $userActivity->getActivity()->getId(),
                'The UserActivity was created');
            $this->assertEquals(1, $follower->getActivityUnseenCount(),
                'The Users activityUnseenCount was upated');
        }
    }
    
    public function testCreateFeedFromUserFollowActivity()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\ActivityStreamData',
        ]);
        
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        
        $query = $em->createQuery('SELECT a FROM TBFrontendBundle:UserFollowActivity a ORDER BY a.id ASC');
        try {
            $activity = $query->setMaxResults(1)->getSingleResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            $this->fail('No UserFollowActivity found in Test DB');
        }
        
        $generator = $this->getContainer()->get('tb.activity.feed.generator');
        
        $generator->createFeedFromActivity($activity);
        
        $followedUser = $activity->getObject();
        
        $this->assertEquals(1, count($followedUser->getUserActivities()),
            'THe followed User has one UserActivity');
        $this->assertEquals($activity->getId(), $followedUser->getUserActivities()[0]->getActivity()->getId(),
            'The UserActivity was created');    
        $this->assertEquals(1, $followedUser->getActivityUnseenCount(),
                'The followed Users activityUnseenCount was upated');
    }
    
    public function testCreateFeedFromRouteLikeActivity()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\ActivityStreamData',
        ]);
        
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        
        $query = $em->createQuery('SELECT a FROM TBFrontendBundle:RouteLikeActivity a ORDER BY a.id ASC');
        try {
            $activity = $query->setMaxResults(1)->getSingleResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            $this->fail('No RouteLikeActivity found in Test DB');
        }
        
        $generator = $this->getContainer()->get('tb.activity.feed.generator');
        
        $generator->createFeedFromActivity($activity);
        
        $route = $activity->getObject();
        $notifiedUser = $route->getUser();
        
        $this->assertEquals(1, count($route->getRouteLikes()),
            'The liked Route has one UserActivity');
        $activities = $route->getRouteLikeActivities();
        
        $this->assertEquals($activity->getId(), $notifiedUser->getUserActivities()[0]->getActivity()->getId(),
            'The UserActivity was created');    
        $this->assertEquals(1, $notifiedUser->getActivityUnseenCount(),
                'The followed Users activityUnseenCount was upated');
    }
    
    public function testCreateFeedFromUserRegisterActivity()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\UserProfileData',
        ]);
        
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $user = $this->getUser('mattallbeury');
        
        $activity = new UserRegisterActivity($user);
        $em->persist($activity);
        $em->flush();
        
        $generator = $this->getContainer()->get('tb.activity.feed.generator');
        $generator->createFeedFromActivity($activity);
        
        $em->refresh($user);
        
        $this->assertEquals($activity->getId(), $user->getUserActivities()[0]->getActivity()->getId(),
            'The UserActivity was created');    
        $this->assertEquals(1, $user->getActivityUnseenCount(),
                'The followed Users activityUnseenCount was upated');
    }
    
    public function testCreateFeedFromCampaignRouteAcceptActivity()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\CampaignData',
        ]);
        
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        
        $user = $this->getUser('paultran');
        $campaign = $this->getCampaign('urbantrails-london');
        
        // add the user as follower to the campaign
        $user->addCampaignsIFollow($campaign);
        $em->persist($user);
        $em->flush();
        
        // create the activity
        $activity = new CampaignRouteAcceptActivity($campaign->getUser(), $campaign->getCampaignRoutes()[0]->getRoute(), $campaign);
        $em->persist($activity);
        $em->flush();
        
        $generator = $this->getContainer()->get('tb.activity.feed.generator');
        
        $generator->createFeedFromActivity($activity);
        
        // The follower of the campaign gets a UserActivity item

        $userActivity = $user->getUserActivities()[0];
        $this->assertEquals($activity->getId(), $user->getUserActivities()[0]->getActivity()->getId(),
            'The UserActivity was created');
        $this->assertEquals(1, $user->getActivityUnseenCount(),
            'The Users activityUnseenCount was upated');
        
    }
    
    public function testCreateFeedFromCampaignFollowActivity()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\CampaignData',
        ]);
        
        $user = $this->getUser('paultran');
        $campaign = $this->getCampaign('urbantrails-london');
        
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        
        $activity = new CampaignFollowActivity($user, $campaign);
        $em->persist($activity);
        $em->flush();
        
        $generator = $this->getContainer()->get('tb.activity.feed.generator');
        
        $generator->createFeedFromActivity($activity);
        
        $this->assertEquals(1, count($campaign->getUser()->getUserActivities()),
            'The User who owns the campaign has one UserActivity');
        $this->assertEquals($activity->getId(), $campaign->getUser()->getUserActivities()[0]->getActivity()->getId(),
            'The UserActivity was created');    
        $this->assertEquals(1, $campaign->getUser()->getActivityUnseenCount(),
                'The Users activityUnseenCount was upated');
    }
    
}    