<?php 

namespace TB\Bundle\FrontendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity */
class UserFollowActivity extends AbstractActivity
{

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToOne(targetEntity="TB\Bundle\FrontendBundle\Entity\User", inversedBy="userFollowActivities")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="actor_id", referencedColumnName="id")
     * })
     **/
    protected $actor;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToOne(targetEntity="TB\Bundle\FrontendBundle\Entity\User", inversedBy="userFollowedActivities")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="object_id", referencedColumnName="id")
     * })
     **/
    protected $object;

    public function __construct(User $followingUser, User $followedUser)
    {
        $this->setActor($followingUser);
        $this->setObject($followedUser);
    }

    /**
     * Set actor
     *
     * @param \TB\Bundle\FrontendBundle\Entity\User $actor
     * @return UserFollowActivity
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
     * @param \TB\Bundle\FrontendBundle\Entity\User $object
     * @return UserFollowActivity
     */
    public function setObject(\TB\Bundle\FrontendBundle\Entity\User $object = null)
    {
        $this->object = $object;

        return $this;
    }

    /**
     * Get object
     *
     * @return \TB\Bundle\FrontendBundle\Entity\User 
     */
    public function getObject()
    {
        return $this->object;
    }
}
