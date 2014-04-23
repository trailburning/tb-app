<?php 

namespace TB\Bundle\FrontendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/** 
 * @ORM\Entity 
 */
class RouteUndoLikeActivity extends Activity
{

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToOne(targetEntity="TB\Bundle\FrontendBundle\Entity\User", inversedBy="routeUndoLikeActivities")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="actor_id", referencedColumnName="id")
     * })
     **/
    protected $actor;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToOne(targetEntity="TB\Bundle\FrontendBundle\Entity\Route", inversedBy="routeUndoLikeActivities")
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
     * @return RouteUndoLikeActivity
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
     * @return RouteUndoLikeActivity
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
            'verb' => 'publish',
            'object' => $this->getObject()->exportAsActivity(),
        ];
        
        return $data;
    }
}
