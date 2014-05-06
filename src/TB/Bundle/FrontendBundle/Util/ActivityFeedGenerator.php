<?php 

namespace TB\Bundle\FrontendBundle\Util;

use Doctrine\ORM\EntityManager;
use TB\Bundle\FrontendBundle\Entity\Activity;
use TB\Bundle\FrontendBundle\Entity\UserActivity;
use TB\Bundle\FrontendBundle\Entity\User;
use TB\Bundle\FrontendBundle\Entity\RouteLikeActivity;
use TB\Bundle\FrontendBundle\Entity\RouteUndoLikeActivity;
use TB\Bundle\FrontendBundle\Exception\ActivityActorNotFoundException;
use TB\Bundle\FrontendBundle\Exception\ActivityObjectNotFoundException;

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
            // Skip activities with missing actor or object relations to prevent
            try {
                $activityItem = $result->export();
            } catch (ActivityActorNotFoundException $e) {
                continue;
            } catch (ActivityObjectNotFoundException $e) {
                continue;
            }
            
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
        } elseif ($activity instanceof \TB\Bundle\FrontendBundle\Entity\RouteLikeActivity) {
            $userActivity = new UserActivity();
            $userActivity->setActivity($activity);
            $user = $activity->getObject()->getUser();
            $userActivity->setUser($user);
            $updatedUsers[] = $user;
            $this->em->persist($userActivity);
            $this->em->flush();
        } elseif ($activity instanceof \TB\Bundle\FrontendBundle\Entity\RouteUndoLikeActivity) {
            // No UserActivity is created for RouteUndoLikeActivity
        } else {
            throw new Exception(sprintf('Unhandled activity item of type "%s"', $activity));
        }
        
        foreach ($updatedUsers as $user) {
            $this->updateUserActivityUnseenCount($user);
        }
    }
    
    public function updateUserActivityUnseenCount(User $user)
    {
        if ($user->getActivityLastViewed() instanceof \DateTime) {
            $q = 'SELECT COUNT(ua.userId) FROM TBFrontendBundle:UserActivity ua 
                  INNER JOIN ua.activity a WITH a.id = ua.activityId
                  WHERE ua.userId = :userId
                  AND (a.published > :lastViewed)';
            $query = $this->em
                ->createQuery($q)
                ->setParameter('userId', $user->getId())
                ->setParameter('lastViewed', $user->getActivityLastViewed()->format('Y-m-d H:i:s'));            
        } else {
            $q = 'SELECT COUNT(ua.userId) FROM TBFrontendBundle:UserActivity ua WHERE ua.userId = :userId';
            $query = $this->em
                ->createQuery($q)
                ->setParameter('userId', $user->getId());
        }
        
        $count = $query->getSingleScalarResult();
        
        $user->setActivityUnseenCount($count);
        $this->em->persist($user);
        $this->em->flush($user);
    }
    
}
