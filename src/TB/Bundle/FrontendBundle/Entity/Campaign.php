<?php

namespace TB\Bundle\FrontendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Campaign
 *
 * @ORM\Table(name="campaign")
 * @ORM\Entity
 */
class Campaign
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=150)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="slug", type="string", length=50)
     */
    private $slug;
    
    /**
     * @var string
     *
     * @ORM\Column(name="image", type="string", length=100)
     */
    private $image;
    
    /**
     * @var string
     *
     * @ORM\Column(name="logo", type="string", length=100)
     */
    private $logo;
    
    /**
     * @var string
     *
     * @ORM\Column(name="synopsis", type="text")
     */
    private $synopsis;
    
    /**
     * @var string
     *
     * @ORM\Column(name="text", type="text")
     */
    private $text;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="campaign_group_id", type="integer", nullable=true)
     */
    private $campaignGroupId;
    
    /**
     * @var CampaignGroup
     *
     * @ORM\ManyToOne(targetEntity="CampaignGroup", inversedBy="campaigns")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="campaign_group_id", referencedColumnName="id")
     * })
     */
    private $campaignGroup;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="user_id", type="integer")
     */
    private $userId;
    
    /**
     * @var \TB\Bundle\FrontendBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="TB\Bundle\FrontendBundle\Entity\User", inversedBy="campaigns")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    private $user;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="region_id", type="integer")
     */
    private $regionId;
    
    /**
     * @var \TB\Bundle\FrontendBundle\Entity\Region
     *
     * @ORM\ManyToOne(targetEntity="TB\Bundle\FrontendBundle\Entity\Region", inversedBy="campaigns")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="region_id", referencedColumnName="id")
     * })
     */
    private $region;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="TB\Bundle\FrontendBundle\Entity\Route", inversedBy="campaigns")
     */
    private $routes;
    
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Event
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set slug
     *
     * @param string $slug
     * @return Event
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string 
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set image
     *
     * @param string $image
     * @return Campaign
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return string 
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set logo
     *
     * @param string $logo
     * @return Campaign
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
     * Set synopsis
     *
     * @param string $synopsis
     * @return Campaign
     */
    public function setSynopsis($synopsis)
    {
        $this->synopsis = $synopsis;

        return $this;
    }

    /**
     * Get synopsis
     *
     * @return string 
     */
    public function getSynopsis()
    {
        return $this->synopsis;
    }

    /**
     * Set text
     *
     * @param string $text
     * @return Campaign
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string 
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set campaignGroupId
     *
     * @param integer $campaignGroupId
     * @return Campaign
     */
    public function setCampaignGroupId($campaignGroupId)
    {
        $this->campaignGroupId = $campaignGroupId;

        return $this;
    }

    /**
     * Get campaignGroupId
     *
     * @return integer 
     */
    public function getCampaignGroupId()
    {
        return $this->campaignGroupId;
    }

    /**
     * Set userId
     *
     * @param integer $userId
     * @return Campaign
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return integer 
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set campaignGroup
     *
     * @param \TB\Bundle\FrontendBundle\Entity\CampaignGroup $campaignGroup
     * @return Campaign
     */
    public function setCampaignGroup(\TB\Bundle\FrontendBundle\Entity\CampaignGroup $campaignGroup = null)
    {
        $this->campaignGroup = $campaignGroup;

        return $this;
    }

    /**
     * Get campaignGroup
     *
     * @return \TB\Bundle\FrontendBundle\Entity\CampaignGroup 
     */
    public function getCampaignGroup()
    {
        return $this->campaignGroup;
    }

    /**
     * Set user
     *
     * @param \TB\Bundle\FrontendBundle\Entity\User $user
     * @return Campaign
     */
    public function setUser(\TB\Bundle\FrontendBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \TB\Bundle\FrontendBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set regionId
     *
     * @param integer $regionId
     * @return Campaign
     */
    public function setRegionId($regionId)
    {
        $this->regionId = $regionId;

        return $this;
    }

    /**
     * Get regionId
     *
     * @return integer 
     */
    public function getRegionId()
    {
        return $this->regionId;
    }

    /**
     * Set region
     *
     * @param \TB\Bundle\FrontendBundle\Entity\Region $region
     * @return Campaign
     */
    public function setRegion(\TB\Bundle\FrontendBundle\Entity\Region $region = null)
    {
        $this->region = $region;

        return $this;
    }

    /**
     * Get region
     *
     * @return \TB\Bundle\FrontendBundle\Entity\Region 
     */
    public function getRegion()
    {
        return $this->region;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->routes = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add routes
     *
     * @param \TB\Bundle\FrontendBundle\Entity\Route $routes
     * @return Campaign
     */
    public function addRoute(\TB\Bundle\FrontendBundle\Entity\Route $routes)
    {
        $this->routes[] = $routes;

        return $this;
    }

    /**
     * Remove routes
     *
     * @param \TB\Bundle\FrontendBundle\Entity\Route $routes
     */
    public function removeRoute(\TB\Bundle\FrontendBundle\Entity\Route $routes)
    {
        $this->routes->removeElement($routes);
    }

    /**
     * Get routes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Add pendingRoutes
     *
     * @param \TB\Bundle\FrontendBundle\Entity\User $pendingRoutes
     * @return Campaign
     */
    public function addPendingRoute(\TB\Bundle\FrontendBundle\Entity\User $pendingRoutes)
    {
        $this->pendingRoutes[] = $pendingRoutes;

        return $this;
    }

    /**
     * Remove pendingRoutes
     *
     * @param \TB\Bundle\FrontendBundle\Entity\User $pendingRoutes
     */
    public function removePendingRoute(\TB\Bundle\FrontendBundle\Entity\User $pendingRoutes)
    {
        $this->pendingRoutes->removeElement($pendingRoutes);
    }

    /**
     * Get pendingRoutes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPendingRoutes()
    {
        return $this->pendingRoutes;
    }
    
    public function getDisplayTitle() 
    {
        if ($this->getCampaignGroup()) {
            return sprintf('%s %s', $this->getCampaignGroup()->getName(), $this->getTitle());
        } else {
            return $this->getTitle();
        }
    }
}
