<?php

namespace TB\Bundle\FrontendBundle\Util;

use TB\Bundle\FrontendBundle\Entity\User;

/**
* Add the 'seen' field to the activity feed item
*/
class ActivityFeedSeenDecorator
{
    protected $user;
    
    public function __construct(User $user)
    {
        $this->user = $user;
    }
    
    public function decorate(array $activityItem)
    {
        if (!isset($activityItem['published'])) {
            throw new Exception('missing published field in activity item');
        }
        $publishedDate = new \DateTime($activityItem['published']);
        
        if (/*$this->user->getActivityLastViewed() === null 
            || */$this->user->getActivityLastViewed() < $publishedDate) {
            $activityItem['seen'] = false;
        } else {
            $activityItem['seen'] = true;
        }
        
        return $activityItem;
    }
}
