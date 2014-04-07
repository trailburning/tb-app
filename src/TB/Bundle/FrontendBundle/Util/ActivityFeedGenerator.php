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
        // Get all following User's ID
        $query = $this->em
            ->createQuery('
                SELECT f.id FROM TBFrontendBundle:User u
                INNER JOIN u.iFollow f
                WHERE u.id = :userId')
            ->setParameter('userId', $userId);
        
        $results = $query->getResult();
        
        $followingUserIds = [];
        
        foreach ($results as $user) {
            $followingUserIds[] = $user['id'];
        }
        
        // get all RoutePublishActivity activities for following users and UserFollowActivity for user that start following thet given userId
        $query = $this->em
            ->createQuery('
                SELECT a FROM TBFrontendBundle:AbstractActivity a
                WHERE (a.actorId IN (:following) AND a INSTANCE OF TB\Bundle\FrontendBundle\Entity\RoutePublishActivity)
                OR (a.objectId IN (:userId) AND a INSTANCE OF TB\Bundle\FrontendBundle\Entity\UserFollowActivity)
                ORDER BY a.id DESC')
            ->setParameter('following', $followingUserIds)
            ->setParameter('userId', $userId)
            ->setMaxResults(100);
        
        $results = $query->getResult();
        
        $feedData = [
            'items' => [],
            'totalItems' => count($results),
        ];
        
        foreach ($results as $result) {
            $feedData['items'][] = $result->export();
        }
        
        return $feedData;
    }
    
}
