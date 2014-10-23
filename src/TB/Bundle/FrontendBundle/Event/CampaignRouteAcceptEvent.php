<?php 

namespace TB\Bundle\FrontendBundle\Event;

use Symfony\Component\EventDispatcher\Event;

use TB\Bundle\FrontendBundle\Entity\User;
use TB\Bundle\FrontendBundle\Entity\Route;
use TB\Bundle\FrontendBundle\Entity\Campaign;

/**
 * Event for a Route being accepted in a Campaign
 */
class CampaignRouteAcceptEvent extends Event
{
    
    protected $user;
    
    protected $route;
    
    protected $campaign;
    
    /**
     * @param User $user The User who submited the Route to the Campaign
     * @param Route $route The Route beeing accepted
     * @param Campaign $campaign The Campaign in that the Route is accepted
     */
    public function __construct(User $user, Route $route, Campaign $campaign)
    {
        $this->user = $user;
        $this->route = $route;
        $this->campaign = $campaign;
    }
    
    public function getUser()
    {
        return $this->user;
    }
    
    public function getRoute()
    {
        return $this->route;
    }
    
    public function getCampaign()
    {
        return $this->campaign;
    }
}
