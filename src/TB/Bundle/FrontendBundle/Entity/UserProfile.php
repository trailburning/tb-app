<?php

namespace TB\Bundle\FrontendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserProfile
 *
 * @ORM\Entity
 * @ORM\Table(name="`user`")
 */
class UserProfile extends User
{    
    
    /**
     * @var string
     *
     * @ORM\Column(name="first_name", type="string", length=50, nullable=true)
     */
    private $first_name;

    /**
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", length=50, nullable=true)
     */
    private $last_name;

    /**
     * @var Point
     *
     * @ORM\Column(name="location", type="point", columnDefinition="GEOMETRY(POINT,4326)", nullable=true)
     */
    private $location; 
    

    /**
     * Set last_name
     *
     * @param string $lastName
     * @return UserProfile
     */
    public function setLastName($lastName)
    {
        $this->last_name = $lastName;

        return $this;
    }

    /**
     * Get last_name
     *
     * @return string 
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * Set location
     *
     * @param point $location
     * @return UserProfile
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location
     *
     * @return point 
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set first_name
     *
     * @param string $firstName
     * @return UserProfile
     */
    public function setFirstName($firstName)
    {
        $this->first_name = $firstName;

        return $this;
    }

    /**
     * Get first_name
     *
     * @return string 
     */
    public function getFirstName()
    {
        return $this->first_name;
    }
    
    public function getFullName()
    {
        return sprintf('%s %s', $this->getFirstName(), $this->getLastName());
    }
    
}
