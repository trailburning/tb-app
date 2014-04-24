<?php

namespace TB\Bundle\FrontendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * BrandProfile
 *
 * @ORM\Entity
 */
class BrandProfile extends User
{
    
    /**
     * @var string
     *
     * @Gedmo\Slug(fields={"displayName"}, updatable=false, separator="")
     * @ORM\Column(name="name", type="string", length=50, nullable=true, unique=true)
     */
    protected $name;
    
    /**
     * @var string
     *
     * @ORM\Column(name="display_name", type="string", length=100, nullable=true)
     */
    private $displayName;
    
    /**
     * @var string
     *
     * @ORM\Column(name="abstract", type="string", length=100, nullable=true)
     */
    private $abstract;
    
    /**
     * @var string
     *
     * @ORM\Column(name="subtitle", type="string", length=100, nullable=true)
     */
    private $subtitle;
    
    /**
     * @var string
     *
     * @ORM\Column(name="header_image", type="string", length=100, nullable=true)
     */
    private $headerImage;
    
    /**
     * @var string
     *
     * @ORM\Column(name="logo", type="string", length=100, nullable=true)
     */
    private $logo;
    
    /**
     * @var string
     *
     * @ORM\Column(name="link", type="string", length=100, nullable=true)
     */
    private $link; 
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="TB\Bundle\FrontendBundle\Entity\Event", mappedBy="sponsors")
     */
    private $sponsoredEvents;
    
    public function getTitle()
    {
        return $this->getDisplayName();
    }

    /**
     * Set headerImage
     *
     * @param string $headerImage
     * @return BrandProfile
     */
    public function setHeaderImage($headerImage)
    {
        $this->headerImage = $headerImage;

        return $this;
    }

    /**
     * Get headerImage
     *
     * @return string 
     */
    public function getHeaderImage()
    {
        return $this->headerImage;
    }

    /**
     * Set logo
     *
     * @param string $logo
     * @return BrandProfile
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;

        return $this;
    }

    /**
     * Get logo
     *
     * @return string 
     */
    public function getLogo()
    {
        return $this->logo;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->sponsoredEvents = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add sponsoredEvents
     *
     * @param \TB\Bundle\FrontendBundle\Entity\Event $sponsoredEvents
     * @return BrandProfile
     */
    public function addSponsoredEvent(\TB\Bundle\FrontendBundle\Entity\Event $sponsoredEvents)
    {
        $this->sponsoredEvents[] = $sponsoredEvents;

        return $this;
    }

    /**
     * Remove sponsoredEvents
     *
     * @param \TB\Bundle\FrontendBundle\Entity\Event $sponsoredEvents
     */
    public function removeSponsoredEvent(\TB\Bundle\FrontendBundle\Entity\Event $sponsoredEvents)
    {
        $this->sponsoredEvents->removeElement($sponsoredEvents);
    }

    /**
     * Get sponsoredEvents
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSponsoredEvents()
    {
        return $this->sponsoredEvents;
    }

    /**
     * Set displayName
     *
     * @param string $displayName
     * @return BrandProfile
     */
    public function setDisplayName($displayName)
    {
        $this->displayName = $displayName;

        return $this;
    }

    /**
     * Get displayName
     *
     * @return string 
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * Set abstract
     *
     * @param string $abstract
     * @return BrandProfile
     */
    public function setAbstract($abstract)
    {
        $this->abstract = $abstract;

        return $this;
    }

    /**
     * Get abstract
     *
     * @return string 
     */
    public function getAbstract()
    {
        return $this->abstract;
    }

    /**
     * Set subtitle
     *
     * @param string $subtitle
     * @return BrandProfile
     */
    public function setSubtitle($subtitle)
    {
        $this->subtitle = $subtitle;

        return $this;
    }

    /**
     * Get subtitle
     *
     * @return string 
     */
    public function getSubtitle()
    {
        return $this->subtitle;
    }

    /**
     * Set link
     *
     * @param string $link
     * @return BrandProfile
     */
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * Get link
     *
     * @return string 
     */
    public function getLink()
    {
        return $this->link;
    }
    
    /**
     * Set name
     *
     * @param string $name
     * @return User
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Workaround to get the inheritence working with FOSUserBundle (there is no setter for salt and FOSUser Bundles user model doensn't handle this entity when it is saved)
     */
    public function setPassword($password)
    {
        $this->salt = $password;

        return parent::setPassword($password);
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
            'objectType' => 'brand',
            'id' => $this->getId(),
            'displayName' => $this->getTitle(),
            'image' => [
                'url' => $imageUrl,
            ],
        ];
        
        return $data;
    }
    
}
