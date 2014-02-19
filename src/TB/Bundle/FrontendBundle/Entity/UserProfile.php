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
     
    public function getTitle()
    {
        return sprintf('%s %s', $this->getFirstName(), $this->getLastName());
    }
    
}
