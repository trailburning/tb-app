<?php 

namespace TB\Bundle\FrontendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/** 
 * @ORM\Entity 
 */
class UserUnfollowActivity extends Activity
{

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToOne(targetEntity="TB\Bundle\FrontendBundle\Entity\User", inversedBy="userUnfollowActivities")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="actor_id", referencedColumnName="id")
     * })
     **/
    protected $actor;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToOne(targetEntity="TB\Bundle\FrontendBundle\Entity\User", inversedBy="userUnfollowedActivities")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="object_id", referencedColumnName="id")
     * })
     **/
    protected $object;

    public function __construct(User $unfollowingUser, User $unfollowedUser)
    {
        $this->setActor($unfollowingUser);
        $this->setObject($unfollowedUser);
    }

    /**
     * Set actor
     *
     * @param \TB\Bundle\FrontendBundle\Entity\User $actor
     * @return UserUnfollowActivity
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
     * @return UserUnfollowActivity
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
    
    public function export()
    {
        $data = [
            'published' => $this->getFormatedPublishedDate(),
            'actor' => $this->getActor()->exportAsActivity(),
            'verb' => 'unfollow',
            'object' => $this->getObject()->exportAsActivity(),
        ];
        
        return $data;
    }
}
