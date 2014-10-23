<?php 

namespace TB\Bundle\FrontendBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use TB\Bundle\FrontendBundle\Entity\User;
use TB\Bundle\FrontendBundle\Entity\Campaign;
/**
* 
*/
class CampaignFollowEvent extends Event
{
    protected $user;
    
    protected $campaign;
    
    /**
     * @param User $user The User who is following
     * @param Campaign $campaign The Campaign who gets followed
     */
    public function __construct(User $user, Campaign $campaign)
    {
        $this->user = $user;
        $this->campaign = $campaign;
    }
    
    public function getUser()
    {
        return $this->user;
    }
    
    public function getCampaign()
    {
        return $this->campaign;
    }
        
}
