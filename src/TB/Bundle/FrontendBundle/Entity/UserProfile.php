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
    
    /**
     * Returns an data array representatiosn of this entity for the activity feed
     */
    public function exportAsActivity()
    {   
        if ($this->getAvatar()) {
            $imageUrl = sprintf('https://s3-eu-west-1.amazonaws.com/trailburning-assets/images/profile/%s/avatar.jpg', $this->getName());
        } elseif ($this->getAvatarGravatar()) {
            $imageUrl = $this->getAvatarGravatar();
        } else {
            $imageUrl = '/assets/img/avatar_man.jpg';
        }
        
        $data = [
            'url' => '/profile/' . $this->getName(),
            'objectType' => 'person',
            'id' => $this->getId(),
            'displayName' => $this->getTitle(),
            'image' => [
                'url' => $imageUrl,
            ],
        ];
        
        return $data;
    }
    
}
