<?php 

namespace TB\Bundle\FrontendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/** 
 * @ORM\Entity 
 */
class RouteLikeActivity extends Activity
{

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToOne(targetEntity="TB\Bundle\FrontendBundle\Entity\User", inversedBy="routeLikeActivities")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="actor_id", referencedColumnName="id")
     * })
     **/
    protected $actor;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToOne(targetEntity="TB\Bundle\FrontendBundle\Entity\Route", inversedBy="routeLikeActivities")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="object_id", referencedColumnName="id")
     * })
     **/
    protected $object;

    public function __construct(Route $route, User $user)
    {
        $this->setActor($user);
        $this->setObject($route);
    }

    /**
     * Set actor
     *
     * @param \TB\Bundle\FrontendBundle\Entity\User $actor
     * @return RouteLikeActivity
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
     * @return RouteLikeActivity
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

    public function export()
    {
        $data = [
            'published' => $this->getFormatedPublishedDate(),
            'actor' => $this->getActor()->exportAsActivity(),
            'verb' => 'like',
            'object' => $this->getObject()->exportAsActivity(),
        ];
        
        return $data;
    }
}
