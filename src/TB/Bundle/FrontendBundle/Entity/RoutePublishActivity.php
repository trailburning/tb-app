<?php 

namespace TB\Bundle\FrontendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use TB\Bundle\FrontendBundle\Exception\ActivityActorNotFoundException;
use TB\Bundle\FrontendBundle\Exception\ActivityObjectNotFoundException;
use Doctrine\ORM\EntityNotFoundException;

/** 
 * @ORM\Entity 
 */
class RoutePublishActivity extends Activity
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
     * @ORM\ManyToOne(targetEntity="TB\Bundle\FrontendBundle\Entity\Route", inversedBy="routePublishActivities")
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
     * @return RoutePublishActivity
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
     * @return RoutePublishActivity
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
            throw new ActivityObjectNotFoundException(sprintf('Route Entity not found with id %s', $this->getObjectId()));
        }
        
        $data = [
            'published' => $this->getFormatedPublishedDate(),
            'actor' => $actorData,
            'verb' => 'publish',
            'object' => $objectData,
        ];
        
        return $data;
    }
}
