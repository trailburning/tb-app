<?php

namespace TB\Bundle\FrontendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BrandProfile
 *
 * @ORM\Entity
 * @ORM\Table(name="`user`")
 */
class BrandProfile extends User
{
    
    
    /**
     * @var string
     *
     * @ORM\Column(name="display_name", type="string", length=100, nullable=true)
     */
    private $display_name;
    
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
    private $header_image;
    
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
    

    /**
     * Set header_image
     *
     * @param string $headerImage
     * @return BrandProfile
     */
    public function setHeaderImage($headerImage)
    {
        $this->header_image = $headerImage;

        return $this;
    }

    /**
     * Get header_image
     *
     * @return string 
     */
    public function getHeaderImage()
    {
        return $this->header_image;
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
     * Set display_name
     *
     * @param string $displayName
     * @return BrandProfile
     */
    public function setDisplayName($displayName)
    {
        $this->display_name = $displayName;

        return $this;
    }

    /**
     * Get display_name
     *
     * @return string 
     */
    public function getDisplayName()
    {
        return $this->display_name;
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
}
