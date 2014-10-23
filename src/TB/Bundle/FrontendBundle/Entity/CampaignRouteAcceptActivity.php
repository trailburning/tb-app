<?php 

namespace TB\Bundle\FrontendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use TB\Bundle\FrontendBundle\Exception\ActivityActorNotFoundException;
use TB\Bundle\FrontendBundle\Exception\ActivityObjectNotFoundException;
use Doctrine\ORM\EntityNotFoundException;

/** 
 * @ORM\Entity 
 */
class CampaignRouteAcceptActivity extends Activity
{

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToOne(targetEntity="TB\Bundle\FrontendBundle\Entity\User", inversedBy="routePublishActivities")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="actor_id", referencedColumnName="id")
     * })
     **/
    protected $actor;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToOne(targetEntity="TB\Bundle\FrontendBundle\Entity\Route", inversedBy="campaignRouteAcceptActivities")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="object_id", referencedColumnName="id")
     * })
     **/
    protected $object;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToOne(targetEntity="TB\Bundle\FrontendBundle\Entity\Campaign", inversedBy="campaignRouteAcceptActivities")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="target_id", referencedColumnName="id")
     * })
     **/
    protected $target;   

    public function __construct(User $user, Route $route, Campaign $campaign)
    {
        $this->setActor($user);
        $this->setObject($route);
        $this->setTarget($campaign);
    }

    /**
     * Set actor
     *
     * @param \TB\Bundle\FrontendBundle\Entity\User $actor
     * @return CampaignRouteAcceptActivity
     */
    public function setActor(\TB\Bundle\FrontendBundle\Entity\User $actor = null)
    {
        $this->actor = $actor;

        return $this;
    }

    /**
     * Get actor
     *
     * @return \TB\Bundle\FrontendBundle\Entity\User 
     */
    public function getActor()
    {
        return $this->actor;
    }

    /**
     * Set object
     *
     * @param \TB\Bundle\FrontendBundle\Entity\Route $object
     * @return CampaignRouteAcceptActivity
     */
    public function setObject(\TB\Bundle\FrontendBundle\Entity\Route $object = null)
    {
        $this->object = $object;

        return $this;
    }

    /**
     * Get object
     *
     * @return \TB\Bundle\FrontendBundle\Entity\Route 
     */
    public function getObject()
    {
        return $this->object;
    }
    
    /**
     * Set target
     *
     * @param \TB\Bundle\FrontendBundle\Entity\Campaign $target
     * @return CampaignRouteAcceptActivity
     */
    public function setTarget(\TB\Bundle\FrontendBundle\Entity\Campaign $target = null)
    {
        $this->target = $target;

        return $this;
    }

    /**
     * Get target
     *
     * @return \TB\Bundle\FrontendBundle\Entity\Campaign 
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Returns a data array that represents this activity item
     *
     * @throws ActivityActorNotFoundException When the related actor entity is missing
     * @throws ActivityObjectNotFoundException When the related object entity is missing
     * @return array
     */
    public function export()
    {
        try {
            $actorData = $this->getActor()->exportAsActivity();
        } catch (EntityNotFoundException $e) {
            throw new ActivityActorNotFoundException(sprintf('User Entity not found with id %s', $this->getActorId()));
        }
        
        try {
            $route = $this->getObject();
            foreach ($route->getCampaignRoutes() as $cr) {
                if ($cr->getCampaignId() == $this->getTarget()->getId()) {
                    $campaignRoute = $cr;
                    break;
                }
            }
            if (!isset($campaignRoute)) {
                throw new ActivityObjectNotFoundException(sprintf('CampaignRoute Entity not found for campaign_id $s and route_id %s', $this->getTargetId(), $this->getObjectId()));
            }
            $objectData = $campaignRoute->exportAsActivity();
        } catch (EntityNotFoundException $e) {
            throw new ActivityObjectNotFoundException(sprintf('Route Entity not found with id %s', $this->getObjectId()));
        }
        
        try {
            $targetData = $this->getTarget()->exportAsActivity();
        } catch (EntityNotFoundException $e) {
            throw new ActivityActorNotFoundException(sprintf('Campaign Entity not found with id %s', $this->getTargetId()));
        }
        
        $data = [
            'published' => $this->getFormatedPublishedDate(),
            'actor' => $actorData,
            'verb' => 'accept',
            'object' => $objectData,
            'target' => $targetData,
        ];
        
        return $data;
    }
}
