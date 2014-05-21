<?php 

namespace TB\Bundle\FrontendBundle\Event;

use Symfony\Component\EventDispatcher\Event;

use TB\Bundle\FrontendBundle\Entity\Route;
use TB\Bundle\FrontendBundle\Entity\User;

/**
 * Event for every time a Routes is updated
 */
class RouteUpdateEvent extends Event
{
    
    protected $route;
    
    protected $user;
    
    /**
     * @param Route $route The Route beeing published
     * @param User $user The User owns the Route
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
