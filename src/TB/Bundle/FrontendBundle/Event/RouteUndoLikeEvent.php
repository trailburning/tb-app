<?php 

namespace TB\Bundle\FrontendBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use TB\Bundle\FrontendBundle\Entity\Route;
use TB\Bundle\FrontendBundle\Entity\User;
/**
* 
*/
class RouteUndoLikeEvent extends Event
{
    protected $route;
    
    protected $user;
    
    /**
     * @param Route $route The Route to undo the like
     * @param User $user The User who undoes a like
     */
    public function __construct(Route $route, User $user)
    {
        $this->route = $route;
        $this->user = $user;
    }
    
    public function getRoute()
    {
        return $this->route;
    }
    
    public function getUser()
    {
        return $this->user;
    }
        
}
