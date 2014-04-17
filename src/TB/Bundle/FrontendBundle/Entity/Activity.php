<?php

namespace TB\Bundle\FrontendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="activity")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="verb", type="string")
 * @ORM\DiscriminatorMap({
 *    "user_follow"   = "UserFollowActivity",
 *    "user_unfollow" = "UserUnfollowActivity",
 *    "route_publish" = "RoutePublishActivity",
 * })
 */
abstract class Activity implements Exportable
{
   
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;
    
    /**
     * @var datetime $published
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    protected $published;
    
    /**
     * @var integer
     * 
     * @ORM\Column(name="actor_id", type="integer")
     */
    protected $actorId;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="object_id", type="integer")
     */
    protected $objectId;
    
    
    /**
     * @var integer
     *
     * @ORM\Column(name="target_id", type="integer", nullable=true)
     */
    protected $targetId;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="UserActivity", mappedBy="activity")
     **/
    private $userActivities;
    
    protected $actor;
    protected $object;
    protected $target;
    

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set published
     *
     * @param \DateTime $published
     * @return Activity
     */
    public function setPublished(\DateTime $published)
    {
        $this->published = $published;

        return $this;
    }

    /**
     * Get published
     *
     * @return \DateTime 
     */
    public function getPublished()
    {
        return $this->published;
    }

    /**
     * Set actorId
     *
     * @param integer $actorId
     * @return Activity
     */
    public function setActorId($actorId)
    {
        $this->actorId = $actorId;

        return $this;
    }

    /**
     * Get actorId
     *
     * @return integer 
     */
    public function getActorId()
    {
        return $this->actorId;
    }

    /**
     * Set objectId
     *
     * @param integer $objectId
     * @return Activity
     */
    public function setObjectId($objectId)
    {
        $this->objectId = $objectId;

        return $this;
    }

    /**
     * Get objectId
     *
     * @return integer 
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     * Set targetId
     *
     * @param integer $targetId
     * @return Activity
     */
    public function setTargetId($targetId)
    {
        $this->targetId = $targetId;

        return $this;
    }

    /**
     * Get targetId
     *
     * @return integer 
     */
    public function getTargetId()
    {
        return $this->targetId;
    }
    
    protected function getFormatedPublishedDate()
    {
        if ($this->getPublished() !== null) {
            return $this->getPublished()->format('Y-m-d\TH:i:s\Z');
        } 
        
        return null;
    }
    
    /**
     * Returns a data array to create an AMQP message
     */
    public function exportMessage()
    {
        $reflection = new \ReflectionClass(get_class($this));
        
        $data = [
            'type' => $reflection->getShortName(),
            'id' => $this->getId(),
        ];
        
        return $data;
    }
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->userActivities = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add userActivities
     *
     * @param \TB\Bundle\FrontendBundle\Entity\UserActivity $userActivities
     * @return Activity
     */
    public function addUserActivity(\TB\Bundle\FrontendBundle\Entity\UserActivity $userActivities)
    {
        $this->userActivities[] = $userActivities;

        return $this;
    }

    /**
     * Remove userActivities
     *
     * @param \TB\Bundle\FrontendBundle\Entity\UserActivity $userActivities
     */
    public function removeUserActivity(\TB\Bundle\FrontendBundle\Entity\UserActivity $userActivities)
    {
        $this->userActivities->removeElement($userActivities);
    }

    /**
     * Get userActivities
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUserActivities()
    {
        return $this->userActivities;
    }
}
