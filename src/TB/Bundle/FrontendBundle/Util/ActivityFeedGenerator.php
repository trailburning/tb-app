<?php 

namespace TB\Bundle\FrontendBundle\Util;

use Doctrine\ORM\EntityManager;

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
        
        // Get all following User's ID
        $followingUserIds = [];
        foreach ($user->getIFollow() as $followUser) {
            $followingUserIds[] = $followUser->getId();
        }
        
        // get all RoutePublishActivity activities for following users and UserFollowActivity for user that start following thet given userId
        $query = $this->em
            ->createQuery('
                SELECT a FROM TBFrontendBundle:AbstractActivity a
                WHERE (a.actorId IN (:following) AND a INSTANCE OF TB\Bundle\FrontendBundle\Entity\RoutePublishActivity)
                OR (a.objectId IN (:userId) AND a INSTANCE OF TB\Bundle\FrontendBundle\Entity\UserFollowActivity)
                ORDER BY a.id DESC')
            ->setParameter('following', $followingUserIds)
            ->setParameter('userId', $user->getId())
            ->setMaxResults(100);
        
        $results = $query->getResult();
        
        $feedData = [
            'items' => [],
            'totalItems' => count($results),
        ];
        
        $decorator = new ActivityFeedSeenDecorator($user);
        
        foreach ($results as $result) {
            $activityItem = $result->export();
            $activityItem = $decorator->decorate($activityItem);
            $feedData['items'][] = $activityItem;
        }
        
        return $feedData;
    }
    
}
