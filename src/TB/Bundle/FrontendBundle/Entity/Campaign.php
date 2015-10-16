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
     * @ORM\Column(name="region_id", type="integer", nullable=true)
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
     * @var string
     *
     * @ORM\Column(name="link", type="string", length=255, nullable=true)
     */
    private $link;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="CampaignRoute", mappedBy="campaign")
     **/
    private $campaignRoutes;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="CampaignRouteAcceptActivity", mappedBy="target")
     **/
    private $campaignRouteAcceptActivities;
    
    /**
     * @ORM\ManyToMany(targetEntity="User", mappedBy="campaignsIFollow")
     **/
    private $follower;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="CampaignFollowActivity", mappedBy="object")
     **/
    private $campaignFollowActivities;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="CampaignUnfollowActivity", mappedBy="object")
     **/
    private $campaignUnfollowActivities;
    
    /**
     * @var string
     *
     * @ORM\Column(name="share_image", type="string", length=100, nullable=true)
     */
    private $shareImage;
    
    /**
     * @var string
     *
     * @ORM\Column(name="bitly_url", type="string", length=255, nullable=true)
     */
    private $bitlyUrl;
    
    /**
     * @var string
     *
     * @ORM\Column(name="twitter_tags", type="string", length=255, nullable=true)
     */
    private $twitterTags;
    
    /**
     * @var string
     *
     * @ORM\Column(name="twitter_query", type="string", length=255, nullable=true)
     */
    private $twitterQuery;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="competition", type="boolean", options={"default" = false})
     */
    private $competition = false;
    
    /**
     * @var string
     *
     * @ORM\Column(name="popular_title", type="string", length=255, nullable=true)
     */
    private $popularTitle;
    
    /**
     * @var string
     *
     * @ORM\Column(name="popular_text", type="string", length=255, nullable=true)
     */
    private $popularText;

    /**
     * @ORM\Column(name="watermark_image", type="string", length=100, nullable=true)
     */
    private $watermarkImage;
    
    /**
     * Set id
     *
     * @param integer $id
     * @return Route
     */
    public function setId($id)
    {
        $this->id = $id;
    
        return $this;
    }
    
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
        $this->follower = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    public function getDisplayTitle() 
    {
        if ($this->getCampaignGroup()) {
            return sprintf('%s %s', $this->getCampaignGroup()->getName(), $this->getTitle());
        } else {
            return $this->getTitle();
        }
    }

    /**
     * Set link
     *
     * @param string $link
     * @return Campaign
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
     * Add campaignRoutes
     *
     * @param \TB\Bundle\FrontendBundle\Entity\CampaignRoute $campaignRoutes
     * @return Campaign
     */
    public function addCampaignRoute(\TB\Bundle\FrontendBundle\Entity\CampaignRoute $campaignRoutes)
    {
        $this->campaignRoutes[] = $campaignRoutes;

        return $this;
    }

    /**
     * Remove campaignRoutes
     *
     * @param \TB\Bundle\FrontendBundle\Entity\CampaignRoute $campaignRoutes
     */
    public function removeCampaignRoute(\TB\Bundle\FrontendBundle\Entity\CampaignRoute $campaignRoutes)
    {
        $this->campaignRoutes->removeElement($campaignRoutes);
    }

    /**
     * Get campaignRoutes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCampaignRoutes()
    {
        return $this->campaignRoutes;
    }
    
    /**
     * Returns an data array representatiosn of this entity for the activity feed
     */
    public function exportAsActivity()
    {   
        $data = [
            'url' => '/campaign/' . $this->getSlug(),
            'objectType' => 'campaign',
            'id' => $this->getId(),
            'displayName' => $this->getDisplayTitle(),
        ];
        
        return $data;
    }
    
    public function export() 
    {
        $data = [
            'id' => $this->getId(),
            'title' => $this->getDisplayTitle(),
            'slug' => $this->getSlug(),
            'logo' => $this->getLogo(),
            'image' => $this->getImage(),
            'text' => $this->getText(),
            'synopsis' => $this->getSynopsis(),
        ];                       
        
        return $data;
    }

    /**
     * Add campaignRouteAcceptActivities
     *
     * @param \TB\Bundle\FrontendBundle\Entity\CampaignRouteAcceptActivity $campaignRouteAcceptActivities
     * @return Campaign
     */
    public function addCampaignRouteAcceptActivity(\TB\Bundle\FrontendBundle\Entity\CampaignRouteAcceptActivity $campaignRouteAcceptActivities)
    {
        $this->campaignRouteAcceptActivities[] = $campaignRouteAcceptActivities;

        return $this;
    }

    /**
     * Remove campaignRouteAcceptActivities
     *
     * @param \TB\Bundle\FrontendBundle\Entity\CampaignRouteAcceptActivity $campaignRouteAcceptActivities
     */
    public function removeCampaignRouteAcceptActivity(\TB\Bundle\FrontendBundle\Entity\CampaignRouteAcceptActivity $campaignRouteAcceptActivities)
    {
        $this->campaignRouteAcceptActivities->removeElement($campaignRouteAcceptActivities);
    }

    /**
     * Get campaignRouteAcceptActivities
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCampaignRouteAcceptActivities()
    {
        return $this->campaignRouteAcceptActivities;
    }

    /**
     * Add campaignFollowActivities
     *
     * @param \TB\Bundle\FrontendBundle\Entity\CampaignFollowActivity $campaignFollowActivities
     * @return Campaign
     */
    public function addCampaignFollowActivity(\TB\Bundle\FrontendBundle\Entity\CampaignFollowActivity $campaignFollowActivities)
    {
        $this->campaignFollowActivities[] = $campaignFollowActivities;

        return $this;
    }

    /**
     * Remove campaignFollowActivities
     *
     * @param \TB\Bundle\FrontendBundle\Entity\CampaignFollowActivity $campaignFollowActivities
     */
    public function removeCampaignFollowActivity(\TB\Bundle\FrontendBundle\Entity\CampaignFollowActivity $campaignFollowActivities)
    {
        $this->campaignFollowActivities->removeElement($campaignFollowActivities);
    }

    /**
     * Get campaignFollowActivities
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCampaignFollowActivities()
    {
        return $this->campaignFollowActivities;
    }

    /**
     * Add campaignUnfollowActivities
     *
     * @param \TB\Bundle\FrontendBundle\Entity\CampaignUnfollowActivity $campaignUnfollowActivities
     * @return Campaign
     */
    public function addCampaignUnfollowActivity(\TB\Bundle\FrontendBundle\Entity\CampaignUnfollowActivity $campaignUnfollowActivities)
    {
        $this->campaignUnfollowActivities[] = $campaignUnfollowActivities;

        return $this;
    }

    /**
     * Remove campaignUnfollowActivities
     *
     * @param \TB\Bundle\FrontendBundle\Entity\CampaignUnfollowActivity $campaignUnfollowActivities
     */
    public function removeCampaignUnfollowActivity(\TB\Bundle\FrontendBundle\Entity\CampaignUnfollowActivity $campaignUnfollowActivities)
    {
        $this->campaignUnfollowActivities->removeElement($campaignUnfollowActivities);
    }

    /**
     * Get campaignUnfollowActivities
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCampaignUnfollowActivities()
    {
        return $this->campaignUnfollowActivities;
    }

    /**
     * Add follower
     *
     * @param \TB\Bundle\FrontendBundle\Entity\User $follower
     * @return Campaign
     */
    public function addFollower(\TB\Bundle\FrontendBundle\Entity\User $follower)
    {
        $this->follower[] = $follower;

        return $this;
    }

    /**
     * Remove follower
     *
     * @param \TB\Bundle\FrontendBundle\Entity\User $follower
     */
    public function removeFollower(\TB\Bundle\FrontendBundle\Entity\User $follower)
    {
        $this->follower->removeElement($follower);
    }

    /**
     * Get follower
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getFollower()
    {
        return $this->follower;
    }

    /**
     * Set shareImage
     *
     * @param string $shareImage
     * @return Campaign
     */
    public function setShareImage($shareImage)
    {
        $this->shareImage = $shareImage;

        return $this;
    }

    /**
     * Get shareImage
     *
     * @return string 
     */
    public function getShareImage()
    {
        return $this->shareImage;
    }

    /**
     * Set bitlyUrl
     *
     * @param string $bitlyUrl
     * @return Campaign
     */
    public function setBitlyUrl($bitlyUrl)
    {
        $this->bitlyUrl = $bitlyUrl;

        return $this;
    }

    /**
     * Get bitlyUrl
     *
     * @return string 
     */
    public function getBitlyUrl()
    {
        return $this->bitlyUrl;
    }

    /**
     * Set twitterTags
     *
     * @param string $twitterTags
     * @return Campaign
     */
    public function setTwitterTags($twitterTags)
    {
        $this->twitterTags = $twitterTags;

        return $this;
    }

    /**
     * Get twitterTags
     *
     * @return string 
     */
    public function getTwitterTags()
    {
        return $this->twitterTags;
    }

    /**
     * Set twitterQuery
     *
     * @param string $twitterQuery
     * @return Campaign
     */
    public function setTwitterQuery($twitterQuery)
    {
        $this->twitterQuery = $twitterQuery;

        return $this;
    }

    /**
     * Get twitterQuery
     *
     * @return string 
     */
    public function getTwitterQuery()
    {
        return $this->twitterQuery;
    }

    /**
     * Set competition
     *
     * @param boolean $competition
     * @return Campaign
     */
    public function setCompetition($competition)
    {
        $this->competition = $competition;

        return $this;
    }

    /**
     * Get competition
     *
     * @return boolean 
     */
    public function getCompetition()
    {
        return $this->competition;
    }

    /**
     * Set popularTitle
     *
     * @param string $popularTitle
     * @return Campaign
     */
    public function setPopularTitle($popularTitle)
    {
        $this->popularTitle = $popularTitle;

        return $this;
    }

    /**
     * Get popularTitle
     *
     * @return string 
     */
    public function getPopularTitle()
    {
        return $this->popularTitle;
    }

    /**
     * Set popularText
     *
     * @param string $popularText
     * @return Campaign
     */
    public function setPopularText($popularText)
    {
        $this->popularText = $popularText;

        return $this;
    }

    /**
     * Get popularText
     *
     * @return string 
     */
    public function getPopularText()
    {
        return $this->popularText;
    }
    
   /**
    * Set watermarkImage
    *
    * @param string $watermarkImage
    * @return Campaign
    */
   public function setWatermarkImage($watermarkImage)
   {
       $this->watermarkImage = $watermarkImage;
   }

    /**
     * Get watermarkImage
     *
     * @return string 
     */
    public function getWatermarkImage()
    {
        return $this->watermarkImage;
    }
}
