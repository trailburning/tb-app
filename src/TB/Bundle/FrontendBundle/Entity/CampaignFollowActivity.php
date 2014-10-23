<?php 

namespace TB\Bundle\FrontendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use TB\Bundle\FrontendBundle\Exception\ActivityActorNotFoundException;
use TB\Bundle\FrontendBundle\Exception\ActivityObjectNotFoundException;
use Doctrine\ORM\EntityNotFoundException;

/** @ORM\Entity */
class CampaignFollowActivity extends Activity
{

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToOne(targetEntity="TB\Bundle\FrontendBundle\Entity\User", inversedBy="campaignFollowActivities")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="actor_id", referencedColumnName="id")
     * })
     **/
    protected $actor;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToOne(targetEntity="TB\Bundle\FrontendBundle\Entity\Campaign", inversedBy="campaignFollowActivities")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="object_id", referencedColumnName="id")
     * })
     **/
    protected $object;

    public function __construct(User $user, Campaign $campaign)
    {
        $this->setActor($user);
        $this->setObject($campaign);
    }

    /**
     * Set actor
     *
     * @param \TB\Bundle\FrontendBundle\Entity\User $actor
     * @return CampaignFollowActivity
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
     * @param \TB\Bundle\FrontendBundle\Entity\Campaign $object
     * @return CampaignFollowActivity
     */
    public function setObject(\TB\Bundle\FrontendBundle\Entity\Campaign $object = null)
    {
        $this->object = $object;

        return $this;
    }

    /**
     * Get object
     *
     * @return \TB\Bundle\FrontendBundle\Entity\Campaign 
     */
    public function getObject()
    {
        return $this->object;
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
            $objectData = $this->getObject()->exportAsActivity();
        } catch (EntityNotFoundException $e) {
            throw new ActivityObjectNotFoundException(sprintf('Campaign Entity not found with id %s', $this->getObjectId()));
        }
        
        $data = [
            'published' => $this->getFormatedPublishedDate(),
            'actor' => $actorData,
            'verb' => 'follow',
            'object' => $objectData,
        ];
        
        return $data;
    }
}
