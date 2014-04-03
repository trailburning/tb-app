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
abstract class AbstractActivity
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
     * @return AbstractActivity
     */
    public function setPublished($published)
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
     * @return AbstractActivity
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
     * @return AbstractActivity
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
     * @return AbstractActivity
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
}
