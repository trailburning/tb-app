<?php 

namespace TB\Bundle\FrontendBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use TB\Bundle\FrontendBundle\Entity\User;
/**
* 
*/
class UserFollowEvent extends Event
{
    protected $followingUser;
    
    protected $followedUser;
    
    /**
     * @param User $followingUser The User who is following
     * @param User $followedUser The User who gets followed
     */
    public function __construct(User $followingUser, User $followedUser)
    {
        $this->followingUser = $followingUser;
        $this->followedUser = $followedUser;
    }
    
    public function getFollowingUser()
    {
        return $this->followingUser;
    }
    
    public function getFollowedUser()
    {
        return $this->followedUser;
    }
        
}
