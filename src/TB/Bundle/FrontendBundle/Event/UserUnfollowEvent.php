<?php 

namespace TB\Bundle\FrontendBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use TB\Bundle\FrontendBundle\Entity\User;

/**
* 
*/
class UserUnfollowEvent extends Event
{
    
    protected $unfollowingUser;
    
    protected $unfollowedUser;
    
    /**
     * @param User $unfollowingUser The User who is unfollowing
     * @param User $unfollowedUser The User who gets unfollowed
     */
    public function __construct(User $unfollowingUser, User $unfollowedUser)
    {
        $this->unfollowingUser = $unfollowingUser;
        $this->unfollowedUser = $unfollowedUser;
    }
    
    public function getUnfollowingUser()
    {
        return $this->unfollowingUser;
    }
    
    public function getUnfollowedUser()
    {
        return $this->unfollowedUser;
    }
    
}
