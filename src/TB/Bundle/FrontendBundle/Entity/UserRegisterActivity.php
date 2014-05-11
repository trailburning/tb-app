<?php 

namespace TB\Bundle\FrontendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use TB\Bundle\FrontendBundle\Exception\ActivityActorNotFoundException;
use TB\Bundle\FrontendBundle\Exception\ActivityObjectNotFoundException;
use Doctrine\ORM\EntityNotFoundException;

/** @ORM\Entity */
class UserRegisterActivity extends Activity
{

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToOne(targetEntity="TB\Bundle\FrontendBundle\Entity\User", inversedBy="userRegisterActivity")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="actor_id", referencedColumnName="id")
     * })
     **/
    protected $actor;

    public function __construct(User $user)
    {
        $this->setActor($user);
    }

    /**
     * Set actor
     *
     * @param \TB\Bundle\FrontendBundle\Entity\User $actor
     * @return UserRegisterActivity
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
     * Returns a data array that represents this activity item
     *
     * @throws ActivityActorNotFoundException When the related actor entity is missing
     * @return array
     */
    public function export()
    {
        try {
            $actorData = $this->getActor()->exportAsActivity();
        } catch (EntityNotFoundException $e) {
            throw new ActivityActorNotFoundException(sprintf('User Entity not found with id %s', $this->getActorId()));
        }

        $data = [
            'published' => $this->getFormatedPublishedDate(),
            'actor' => $actorData,
            'verb' => 'register',
            'object' => [
                'url' => 'http://www.trailburning.com',
                'objectType' => 'service',
                'id' => 'http://www.trailburning.com',
                'displayName' => 'Trailburning',
            ],
        ];
        
        return $data;
    }
}
