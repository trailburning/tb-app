<?php

namespace TB\Bundle\FrontendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserProfile
 *
 * @ORM\Entity
 */
class UserProfile extends User
{    
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="is_ambassador", type="boolean", options={"default" = false})
     */
    private $isAmbassador = false;
    
    /**
     * @var string
     *
     * @ORM\Column(name="ambassador_tagline", type="string", length=255, nullable=true)
     */
    private $ambassadorTagline;
     
    public function getTitle()
    {
        return sprintf('%s %s', $this->getFirstName(), $this->getLastName());
    }
    
    /**
     * Returns an data array representatiosn of this entity for the activity feed
     */
    public function exportAsActivity()
    {   
        
        $data = [
            'url' => '/profile/' . $this->getName(),
            'objectType' => 'person',
            'id' => $this->getId(),
            'displayName' => $this->getTitle(),
            'image' => [
                'url' => $this->getAvatarUrl(),
            ],
        ];
        
        return $data;
    }

}
