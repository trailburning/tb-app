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
    
    /**
     * @var string
     *
     * @ORM\Column(name="ambassador_location", type="string", length=50, nullable=true)
     */
    private $ambassadorLocation;
     
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


    /**
     * Set isAmbassador
     *
     * @param boolean $isAmbassador
     * @return UserProfile
     */
    public function setIsAmbassador($isAmbassador)
    {
        $this->isAmbassador = $isAmbassador;

        return $this;
    }

    /**
     * Get isAmbassador
     *
     * @return boolean 
     */
    public function getIsAmbassador()
    {
        return $this->isAmbassador;
    }

    /**
     * Set ambassadorTagline
     *
     * @param string $ambassadorTagline
     * @return UserProfile
     */
    public function setAmbassadorTagline($ambassadorTagline)
    {
        $this->ambassadorTagline = $ambassadorTagline;

        return $this;
    }

    /**
     * Get ambassadorTagline
     *
     * @return string 
     */
    public function getAmbassadorTagline()
    {
        return $this->ambassadorTagline;
    }
    
    /**
     * Set ambassadorLocation
     *
     * @param string $ambassadorLocation
     * @return UserProfile
     */
    public function setAmbassadorLocation($ambassadorLocation)
    {
        $this->ambassadorLocation = $ambassadorLocation;

        return $this;
    }

    /**
     * Get ambassadorLocation
     *
     * @return string 
     */
    public function getAmbassadorLocation()
    {
        return $this->ambassadorLocation;
    }

}
