<?php 

namespace TB\Bundle\FrontendBundle\Util;

use Doctrine\ORM\EntityManager;
use TB\Bundle\FrontendBundle\Entity\Activity;
use TB\Bundle\FrontendBundle\Entity\UserActivity;
use TB\Bundle\FrontendBundle\Entity\User;

/**
 * 
 */
class ActivityFeedGenerator
{
    
    protected $em;
    
    protected $serializer;
    
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }
    
    public function getFeedForUser($userId)
    {
        $user = $this->em
            ->getRepository('TBFrontendBundle:User')
            ->findOneById($userId);
        if (!$user) {
            throw new Exception(sprintf('Mising user with id %s', $userId));
        }
        
        $query = $this->em
            ->createQuery('
                SELECT a FROM TBFrontendBundle:Activity a
                INNER JOIN TBFrontendBundle:UserActivity ua
                WITH a.id = ua.activityId
                WHERE ua.userId = :userId
                ORDER BY a.id DESC')
            ->setParameter('userId', $user->getId())
            ->setMaxResults(100);
        
        $results = $query->getResult();
        
        $feedData = [
            'items' => [],
            'totalItems' => count($results),
            'newItems' => 0,
        ];
        
        $decorator = new ActivityFeedSeenDecorator($user);
        
        foreach ($results as $result) {
            $activityItem = $result->export();
            $activityItem = $decorator->decorate($activityItem);
            if (isset($activityItem['seen']) && $activityItem['seen'] === false) {
                $feedData['newItems']++;
            }
            $feedData['items'][] = $activityItem;
        }
        
        return $feedData;
    }
    
    /**
     * 
     */
    public function createFeedFromActivity(Activity $activity)
    {
        $updatedUsers = [];
        if ($activity instanceof \TB\Bundle\FrontendBundle\Entity\RoutePublishActivity) {
            // Create a UserActivity for all User who follow the creator of the Route
            $users = $activity->getActor()->getMyFollower();
            foreach ($users as $user) {
                $userActivity = new UserActivity();
                $userActivity->setActivity($activity);
                $userActivity->setUser($user);
                $updatedUsers[] = $user;
                $this->em->persist($userActivity);
            }
            $this->em->flush();
        } elseif ($activity instanceof \TB\Bundle\FrontendBundle\Entity\UserFollowActivity) {
            // Create a UserActivity for the User who gets followed
            $userActivity = new UserActivity();
            $userActivity->setActivity($activity);
            $userActivity->setUser($activity->getObject());
            $updatedUsers[] = $activity->getObject();
            $this->em->persist($userActivity);
            $this->em->flush();
        } elseif ($activity instanceof \TB\Bundle\FrontendBundle\Entity\UserUnfollowActivity) {
            // No UserActivity is created for UserUnfollowActivity
        } else {
            throw new Exception(sprintf('Unhandled activity item of type "%s"', $activity));
        }
        
        foreach ($updatedUsers as $user) {
            $this->updateUserActivityUnseenCount($user);
        }
    }
    
    public function updateUserActivityUnseenCount(User $user)
    {
        $query = $this->em
            ->createQuery('SELECT COUNT(a.userId) FROM TBFrontendBundle:UserActivity a WHERE a.userId = :userId')
            ->setParameter('userId', $user->getId());
        $count = $query->getSingleScalarResult();
        $user->setActivityUnseenCount($count);
        $this->em->persist($user);
        $this->em->flush($user);
    }
    
}
