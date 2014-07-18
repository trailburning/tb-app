<?php

namespace TB\Bundle\FrontendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Event
 *
 * @ORM\Table(name="event")
 * @ORM\Entity
 */
class Event implements Exportable
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
     * @ORM\Column(name="title2", type="string", length=150, nullable=true)
     */
    private $title2;

    /**
     * @var string
     *
     * @ORM\Column(name="about", type="text")
     */
    private $about;

    /**
     * @var string
     *
     * @ORM\Column(name="slug", type="string", length=50)
     */
    private $slug;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="TB\Bundle\FrontendBundle\Entity\BrandProfile", inversedBy="sponsoredEvents")
     */
    private $sponsors;
    
    /**
     * @var date
     *
     * @ORM\Column(name="date", type="date")
     */
    private $date;
    
    /**
     * @var date_to
     *
     * @ORM\Column(name="date_to", type="date", nullable=true)
     */
    private $date_to;
    
    /**
     * @var string
     *
     * @ORM\Column(name="subtitle", type="text")
     */
    private $subtitle;
    
    /**
     * @var string
     *
     * @ORM\Column(name="synopsis", type="text")
     */
    private $synopsis;
    
    /**
     * @var Point
     *
     * @ORM\Column(name="location", type="point", columnDefinition="GEOMETRY(POINT,4326)")
     */
    private $location;
    
    /**
     * @var string
     *
     * @ORM\Column(name="logo", type="string", length=50)
     */
    private $logo;
    
    /**
     * @var string
     *
     * @ORM\Column(name="logo_small", type="string", length=50, nullable=true)
     */
    private $logoSmall;
    
    /**
     * @var string
     *
     * @ORM\Column(name="image", type="string", length=50)
     */
    private $image;   
    
    /**
     * @var string
     *
     * @ORM\Column(name="image_credit", type="string", length=50, nullable=true)
     */
    private $image_credit;   
    
    /**
     * @var string
     *
     * @ORM\Column(name="link", type="string", length=255, nullable=true)
     */
    private $link;    
    
    /**
     * @var integer
     *
     * @ORM\Column(name="user_id", type="integer")
     */
    private $user_id;     

    /**
     * @var \TB\Bundle\FrontendBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="TB\Bundle\FrontendBundle\Entity\User", inversedBy="events")
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
     * @ORM\ManyToOne(targetEntity="TB\Bundle\FrontendBundle\Entity\Region", inversedBy="events")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="region_id", referencedColumnName="id")
     * })
     */
    private $region;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="map_zoom", type="smallint", options={"default" = 3})
     */
    private $map_zoom;    
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="EventRoute", mappedBy="event")
     **/
    private $eventRoutes;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="homepage_order", type="smallint", nullable=true)
     */
    private $homepageOrder;
    
    /**
     * @var string
     *
     * @ORM\Column(name="share_image", type="string", length=100, nullable=true)
     */
    private $shareImage;
    

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
     * Set title2
     *
     * @param string $title2
     * @return Event
     */
    public function setTitle2($title2)
    {
        $this->title2 = $title2;

        return $this;
    }

    /**
     * Get title2
     *
     * @return string 
     */
    public function getTitle2()
    {
        return $this->title2;
    }

    /**
     * Set about
     *
     * @param string $about
     * @return Event
     */
    public function setAbout($about)
    {
        $this->about = $about;

        return $this;
    }

    /**
     * Get about
     *
     * @return string 
     */
    public function getAbout()
    {
        return $this->about;
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
     * Set date
     *
     * @param \DateTime $date
     * @return Event
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime 
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set date_to
     *
     * @param \DateTime $dateTo
     * @return Event
     */
    public function setDateTo($dateTo)
    {
        $this->date_to = $dateTo;

        return $this;
    }

    /**
     * Get date_to
     *
     * @return \DateTime 
     */
    public function getDateTo()
    {
        return $this->date_to;
    }

    /**
     * Set subtitle
     *
     * @param string $subtitle
     * @return Event
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
     * Set synopsis
     *
     * @param string $synopsis
     * @return Event
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
     * Set location
     *
     * @param point $location
     * @return Event
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
     * Set logo
     *
     * @param string $logo
     * @return Event
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
     * Set image
     *
     * @param string $image
     * @return Event
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
     * Set link
     *
     * @param string $link
     * @return Event
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
     * Set region
     *
     * @param string $region
     * @return Event
     */
    public function setRegion($region)
    {
        $this->region = $region;

        return $this;
    }

    /**
     * Get region
     *
     * @return string 
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * Set region_about
     *
     * @param string $regionAbout
     * @return Event
     */
    public function setRegionAbout($regionAbout)
    {
        $this->region_about = $regionAbout;

        return $this;
    }

    /**
     * Get region_about
     *
     * @return string 
     */
    public function getRegionAbout()
    {
        return $this->region_about;
    }

    /**
     * Set region_logo
     *
     * @param string $regionLogo
     * @return Event
     */
    public function setRegionLogo($regionLogo)
    {
        $this->region_logo = $regionLogo;

        return $this;
    }

    /**
     * Get region_logo
     *
     * @return string 
     */
    public function getRegionLogo()
    {
        return $this->region_logo;
    }

    /**
     * Set region_image
     *
     * @param string $regionImage
     * @return Event
     */
    public function setRegionImage($regionImage)
    {
        $this->region_image = $regionImage;

        return $this;
    }

    /**
     * Get region_image
     *
     * @return string 
     */
    public function getRegionImage()
    {
        return $this->region_image;
    }

    /**
     * Set region_link
     *
     * @param string $regionLink
     * @return Event
     */
    public function setRegionLink($regionLink)
    {
        $this->region_link = $regionLink;

        return $this;
    }

    /**
     * Get region_link
     *
     * @return string 
     */
    public function getRegionLink()
    {
        return $this->region_link;
    }
    
    /**
     * Set user
     *
     * @param \TB\Bundle\FrontendBundle\Entity\User $user
     * @return Event
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
     * Set userId
     *
     * @param integer $userId
     * @return Event
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
     * Add sponsors
     *
     * @param \TB\Bundle\FrontendBundle\Entity\BrandProfile $sponsors
     * @return Event
     */
    public function addSponsor(\TB\Bundle\FrontendBundle\Entity\BrandProfile $sponsors)
    {
        $this->sponsors[] = $sponsors;

        return $this;
    }

    /**
     * Remove sponsors
     *
     * @param \TB\Bundle\FrontendBundle\Entity\BrandProfile $sponsors
     */
    public function removeSponsor(\TB\Bundle\FrontendBundle\Entity\BrandProfile $sponsors)
    {
        $this->sponsors->removeElement($sponsors);
    }

    /**
     * Get sponsors
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSponsors()
    {
        return $this->sponsors;
    }

    /**
     * Set image_credit
     *
     * @param string $imageCredit
     * @return Event
     */
    public function setImageCredit($imageCredit)
    {
        $this->image_credit = $imageCredit;

        return $this;
    }

    /**
     * Get image_credit
     *
     * @return string 
     */
    public function getImageCredit()
    {
        return $this->image_credit;
    }

    /**
     * Set map_zoom
     *
     * @param integer $mapZoom
     * @return Event
     */
    public function setMapZoom($mapZoom)
    {
        $this->map_zoom = $mapZoom;

        return $this;
    }

    /**
     * Get map_zoom
     *
     * @return integer 
     */
    public function getMapZoom()
    {
        return $this->map_zoom;
    }

    /**
     * Set regionId
     *
     * @param integer $regionId
     * @return Event
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
     * Constructor
     */
    public function __construct()
    {
        $this->sponsors = new \Doctrine\Common\Collections\ArrayCollection();
        $this->eventRoutes = new \Doctrine\Common\Collections\ArrayCollection();        
    }


    /**
     * Add eventRoutes
     *
     * @param \TB\Bundle\FrontendBundle\Entity\EventRoute $eventRoutes
     * @return Event
     */
    public function addEventRoute(\TB\Bundle\FrontendBundle\Entity\EventRoute $eventRoutes)
    {
        $this->eventRoutes[] = $eventRoutes;

        return $this;
    }

    /**
     * Remove eventRoutes
     *
     * @param \TB\Bundle\FrontendBundle\Entity\EventRoute $eventRoutes
     */
    public function removeEventRoute(\TB\Bundle\FrontendBundle\Entity\EventRoute $eventRoutes)
    {
        $this->eventRoutes->removeElement($eventRoutes);
    }

    /**
     * Get eventRoutes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getEventRoutes()
    {
        return $this->eventRoutes;
    }

    /**
     * Set logoSmall
     *
     * @param string $logoSmall
     * @return Event
     */
    public function setLogoSmall($logoSmall)
    {
        $this->logoSmall = $logoSmall;

        return $this;
    }

    /**
     * Get logoSmall
     *
     * @return string 
     */
    public function getLogoSmall()
    {
        return $this->logoSmall;
    }
    
    public function export() 
    {
        $data = [
            'id' => $this->getId(),
            'about' => $this->getAbout(),
            'slug' => $this->getSlug(),
            'title' => $this->getTitle(),
            'title2' => $this->getTitle2(),
            'date' => $this->getDate()->format('Y-m-d'),
            'date_to' => ($this->getDateTo() instanceof \DateTime) ? $this->getDateTo()->format('Y-m-d') : null,
            'subtitle' => $this->getSubtitle(),
            'synopsis' => $this->getSynopsis(),
            'location' => [
                $this->getLocation()->getLongitude(), 
                $this->getLocation()->getLatitude(),
            ],
            'logo' => $this->getLogo(),
            'logo_small' => $this->getLogoSmall(),
            'image' => $this->getImage(),
            'image_credit' => $this->getImageCredit(),
            'link' => $this->getLink(),
        ];                       
        
        if ($this->getRegion() !== null) {
            $data['region'] = $this->getRegion()->export();
        }
        
        return $data;
    }

    /**
     * Set homepageOrder
     *
     * @param integer $homepageOrder
     * @return Event
     */
    public function setHomepageOrder($homepageOrder)
    {
        $this->homepageOrder = $homepageOrder;

        return $this;
    }

    /**
     * Get homepageOrder
     *
     * @return integer 
     */
    public function getHomepageOrder()
    {
        return $this->homepageOrder;
    }

    /**
     * Set shareImage
     *
     * @param string $shareImage
     * @return Event
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
}
