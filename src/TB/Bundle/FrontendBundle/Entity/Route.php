<?php

namespace TB\Bundle\FrontendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\EntityManager;

/**
 * Route
 *
 * @ORM\Table(name="routes")
 * @ORM\Entity
 */
class Route implements Exportable
{
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=50, nullable=true)
     */
    private $name;
    
    /**
     * @var string
     *
     * @ORM\Column(name="short_name", type="string", length=50, nullable=true)
     */
    private $short_name;
	
    /**
     * @var string
     *
     * @ORM\Column(name="region", type="string", length=50, nullable=true)
     */
    private $region;
    
    /**
     * @var string
     *
     * @Gedmo\Slug(fields={"name", "region"}, separator="-")    
     * @ORM\Column(name="slug", type="string", length=50, nullable=true)
     */
    private $slug;

    /**
     * @var integer
     *
     * @ORM\Column(name="length", type="integer", nullable=true)
     */
    private $length;

    /**
     * @var Point point
     *
     * @ORM\Column(name="centroid", type="point", columnDefinition="GEOMETRY(POINT,4326)", nullable=true)
     */
    private $centroid;

    /**
     * @var hstore
     *
     * @ORM\Column(name="tags", type="hstore", nullable=true)
     */
    private $tags;

    /**
     * @var integer
     *
     * @ORM\Column(name="user_id", type="integer")
     */
    private $userId;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="route_type_id", type="integer", nullable=true)
     */
    private $routeTypeId;

    /**
     * @var integer
     *
     * @ORM\Column(name="route_category_id", type="integer", nullable=true)
     */
    private $routeCategoryId;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="gpx_file_id", type="integer")
     */
    private $gpxFileId;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="media_id", type="integer", nullable=true)
     */
    private $mediaId;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="publish", type="boolean", options={"default" = false})
     */
    private $publish = false;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="homepage_order", type="smallint", nullable=true)
     */
    private $homepageOrder;    

    /**
     * @var \TB\Bundle\FrontendBundle\Entity\GpxFile
     *
     * @ORM\OneToOne(targetEntity="TB\Bundle\FrontendBundle\Entity\GpxFile")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="gpx_file_id", referencedColumnName="id")
     * })
     */
    private $gpxFile;

    /**
     * @var \TB\Bundle\FrontendBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="TB\Bundle\FrontendBundle\Entity\User", inversedBy="routes")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    private $user;

    /**
     * @var RouteType
     *
     * @ORM\ManyToOne(targetEntity="RouteType", inversedBy="routes")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="route_type_id", referencedColumnName="id")
     * })
     */
    private $routeType;

    /**
     * @var RouteCategory
     *
     * @ORM\ManyToOne(targetEntity="RouteCategory", inversedBy="routes")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="route_category_id", referencedColumnName="id")
     * })
     */
    private $routeCategory;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="Media", mappedBy="route")
     **/
    private $medias;
    
    /**
     * @ORM\OneToOne(targetEntity="Media")
     * @ORM\JoinColumn(name="media_id", referencedColumnName="id", onDelete="SET NULL")
     **/
    private $media;
    
    /**
     * @var string
     *
     * @ORM\Column(name="about", type="text", nullable=true)
     */
    private $about;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="TB\Bundle\FrontendBundle\Entity\Editorial", mappedBy="routes")
     */
    private $editorials;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="EventRoute", mappedBy="route")
     **/
    private $eventRoutes;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="RoutePoint", mappedBy="route")
     **/
    private $routePoints;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="RoutePublishActivity", mappedBy="object")
     **/
    private $routePublishActivities;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="RouteLikeActivity", mappedBy="object")
     **/
    private $routeLikeActivities;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="RouteUndoLikeActivity", mappedBy="object")
     **/
    private $routeUndoLikeActivities;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="TB\Bundle\FrontendBundle\Entity\User", inversedBy="routeLikes")
     * @ORM\JoinTable(name="route_likes")
     */
    private $userLikes;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="TB\Bundle\FrontendBundle\Entity\Attribute", inversedBy="routes")
     */
    private $attributes;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="approved", type="boolean", options={"default" = true})
     */
    private $approved = true;

    /**
     * Set name
     *
     * @param string $name
     * @return Route
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
     * Set length
     *
     * @param integer $length
     * @return Route
     */
    public function setLength($length)
    {
        $this->length = $length;
    
        return $this;
    }

    /**
     * Get length
     *
     * @return integer 
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * Set centroid
     *
     * @param point $centroid
     * @return Route
     */
    public function setCentroid($centroid)
    {
        $this->centroid = $centroid;
    
        return $this;
    }

    /**
     * Get centroid
     *
     * @return point 
     */
    public function getCentroid()
    {
        return $this->centroid;
    }

    /**
     * Set tags
     *
     * @param hstore $tags
     * @return Route
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    
        return $this;
    }

    /**
     * Get tags
     *
     * @return hstore 
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Set userId
     *
     * @param integer $userId
     * @return Route
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
     * Set id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set gpxFile
     *
     * @param \TB\Bundle\FrontendBundle\Entity\GpxFile $gpxFile
     * @return Route
     */
    public function setGpxFile(\TB\Bundle\FrontendBundle\Entity\GpxFile $gpxFile = null)
    {
        $this->gpxFile = $gpxFile;
    
        return $this;
    }

    /**
     * Get gpxFile
     *
     * @return \TB\Bundle\FrontendBundle\Entity\GpxFile 
     */
    public function getGpxFile()
    {
        return $this->gpxFile;
    }

    /**
     * Set user
     *
     * @param \TB\Bundle\FrontendBundle\Entity\User $user
     * @return Route
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
     * Set region
     *
     * @param string $region
     * @return Route
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
     * Set slug
     *
     * @param string $slug
     * @return Route
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
     * Set about
     *
     * @param string $about
     * @return Route
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
     * Add editorials
     *
     * @param \TB\Bundle\FrontendBundle\Entity\Editorial $editorials
     * @return Route
     */
    public function addEditorial(\TB\Bundle\FrontendBundle\Entity\Editorial $editorials)
    {
        $this->editorials[] = $editorials;

        return $this;
    }

    /**
     * Remove editorials
     *
     * @param \TB\Bundle\FrontendBundle\Entity\Editorial $editorials
     */
    public function removeEditorial(\TB\Bundle\FrontendBundle\Entity\Editorial $editorials)
    {
        $this->editorials->removeElement($editorials);
    }

    /**
     * Get editorials
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getEditorials()
    {
        return $this->editorials;
    }

    /**
     * Set short_name
     *
     * @param string $shortName
     * @return Route
     */
    public function setShortName($shortName)
    {
        $this->short_name = $shortName;

        return $this;
    }

    /**
     * Get short_name
     *
     * @return string 
     */
    public function getShortName()
    {
        return $this->short_name;
    }
    


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->editorials = new \Doctrine\Common\Collections\ArrayCollection();
        $this->medias = new \Doctrine\Common\Collections\ArrayCollection();
        $this->eventRoutes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->routePoints = new \Doctrine\Common\Collections\ArrayCollection();
        $this->routeLikeActivities = new \Doctrine\Common\Collections\ArrayCollection();
        $this->routeUndoLikeActivities = new \Doctrine\Common\Collections\ArrayCollection();        
        $this->attributes = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Set routeTypeId
     *
     * @param integer $routeTypeId
     * @return Route
     */
    public function setRouteTypeId($routeTypeId)
    {
        $this->routeTypeId = $routeTypeId;

        return $this;
    }

    /**
     * Get routeTypeId
     *
     * @return integer 
     */
    public function getRouteTypeId()
    {
        return $this->routeTypeId;
    }

    /**
     * Set routeType
     *
     * @param \TB\Bundle\FrontendBundle\Entity\RouteType $routeType
     * @return Route
     */
    public function setRouteType(\TB\Bundle\FrontendBundle\Entity\RouteType $routeType = null)
    {
        $this->routeType = $routeType;

        return $this;
    }

    /**
     * Get routeType
     *
     * @return \TB\Bundle\FrontendBundle\Entity\RouteType 
     */
    public function getRouteType()
    {
        return $this->routeType;
    }

    /**
     * Add eventRoutes
     *
     * @param \TB\Bundle\FrontendBundle\Entity\EventRoute $eventRoutes
     * @return Route
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
     * Set routeCategoryId
     *
     * @param integer $routeCategoryId
     * @return Route
     */
    public function setRouteCategoryId($routeCategoryId)
    {
        $this->routeCategoryId = $routeCategoryId;

        return $this;
    }

    /**
     * Get routeCategoryId
     *
     * @return integer 
     */
    public function getRouteCategoryId()
    {
        return $this->routeCategoryId;
    }

    /**
     * Set routeCategory
     *
     * @param \TB\Bundle\FrontendBundle\Entity\RouteCategory $routeCategory
     * @return Route
     */
    public function setRouteCategory(\TB\Bundle\FrontendBundle\Entity\RouteCategory $routeCategory = null)
    {
        $this->routeCategory = $routeCategory;

        return $this;
    }

    /**
     * Get routeCategory
     *
     * @return \TB\Bundle\FrontendBundle\Entity\RouteCategory 
     */
    public function getRouteCategory()
    {
        return $this->routeCategory;
    }

    /**
     * Add routePoints
     *
     * @param \TB\Bundle\FrontendBundle\Entity\RoutePoint $routePoints
     * @return Route
     */
    public function addRoutePoint(\TB\Bundle\FrontendBundle\Entity\RoutePoint $routePoints)
    {
        $this->routePoints[] = $routePoints;

        return $this;
    }

    /**
     * Remove routePoints
     *
     * @param \TB\Bundle\FrontendBundle\Entity\RoutePoint $routePoints
     */
    public function removeRoutePoint(\TB\Bundle\FrontendBundle\Entity\RoutePoint $routePoints)
    {
        $this->routePoints->removeElement($routePoints);
    }

    /**
     * Get routePoints
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRoutePoints()
    {
        return $this->routePoints;
    }

    /**
     * Add medias
     *
     * @param \TB\Bundle\FrontendBundle\Entity\Media $medias
     * @return Route
     */
    public function addMedia(\TB\Bundle\FrontendBundle\Entity\Media $medias)
    {
        $this->medias[] = $medias;

        return $this;
    }

    /**
     * Remove medias
     *
     * @param \TB\Bundle\FrontendBundle\Entity\Media $medias
     */
    public function removeMedia(\TB\Bundle\FrontendBundle\Entity\Media $medias)
    {
        $this->medias->removeElement($medias);
    }

    /**
     * Get medias
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMedias()
    {
        return $this->medias;
    }


    /**
     * Set gpxFileId
     *
     * @param integer $gpxFileId
     * @return Route
     */
    public function setGpxFileId($gpxFileId)
    {
        $this->gpxFileId = $gpxFileId;

        return $this;
    }

    /**
     * Get gpxFileId
     *
     * @return integer 
     */
    public function getGpxFileId()
    {
        return $this->gpxFileId;
    }
    

    /**
     * Set publish
     *
     * @throws Exception when published is set true but the name is empty (a slug will be generated)
     * @param boolean $publish
     * @return Route
     */
    public function setPublish($publish)
    {
        if ($publish === true && $this->getName() === null) {
            throw new \Exception('Before publishing a Route, the name field must be set');
            
        }
        
        $this->publish = $publish;

        return $this;
    }

    /**
     * Get publish
     *
     * @return boolean 
     */
    public function getPublish()
    {
        return $this->publish;
    }
    
    
    
    /**
     * Only used by API
     * Updates an existing Route Entity from a JSON object
     *
     * @param $json JSON object
     * @throws Exception for invalid JSON object
     */
    public function updateFromJSON($json)
    {
        $routeObj = json_decode($json);
        if ($routeObj === null) {
            throw new \Exception('Invalid JSON data');
        }
        
        $fields = [
            'name' => 'name',
            'region' => 'region',
            'about' => 'about',
            'publish' => 'publish',
            'route_type_id' => 'routeTypeId',
            'route_category_id' => 'routeCategoryId',
            'media_id' => 'mediaId',
        ];
          
        foreach ($fields as $apiName => $entityName) {
            if (property_exists($routeObj, $apiName)) {
                $method = 'set' . ucfirst($entityName);
                $this->$method($routeObj->$apiName);
            }
        }  
    }
    
    /**
     * Only used by API
     */
    public function calculateAscentDescent() 
    {
        $lastRpAltitude = 0;
        $asc = 0;
        $desc = 0;
        
        $tags = $this->getTags();

        foreach ($this->getRoutePoints() as $routePoint) {
            $rpTags = $routePoint->getTags();
            if (!isset($rpTags['altitude'])) {
                continue;
            }
            $rpAltitude = $rpTags['altitude'];
            
            if ($lastRpAltitude != 0) {
                if ($rpAltitude > $lastRpAltitude) {
                    $asc += $rpAltitude - $lastRpAltitude;
                } else {
                    $desc += $lastRpAltitude - $rpAltitude;
                }
            }

            $lastRpAltitude = $rpAltitude;
        }

        $tags['ascent'] = $asc;
        $tags['descent'] = $desc;

        $this->setTags($tags);

        return 0;
    }
    
    /**
     * Only used by API
     */
    public function getNearestPointByTime($unixtimestamp) {
        $routePoints = $this->getRoutePoints();
        if ($routePoints->count() < 2)
            throw new \Exception("Route is less than 2 points.");
        if ($unixtimestamp < $routePoints[0]->getTags()['datetime']) {
            return $routePoints[0];
        } else if ($unixtimestamp > $routePoints->last()->getTags()['datetime']) {
            return $routePoints->last();
        } else {
            foreach ($routePoints as $rp) {
                if ($rp->getTags()['datetime'] > $unixtimestamp ) {
                    return $rp; 
                }
            }
        }
    }
    
    public function export() 
    {
        $data = [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'slug' => $this->getSlug(),
            'region' => $this->getRegion(),
            'length' => $this->getLength(),
            'about' => $this->getAbout(),
            'centroid' => [
                $this->getCentroid()->getLongitude(), 
                $this->getCentroid()->getLatitude(),
            ],
            'tags' => $this->getTags(),
            'route_points' => [],
        ];                       
        
        if (count($this->getRoutePoints()) > 0) {
            foreach ($this->getRoutePoints() as $rp) {
                $data['route_points'][] = $rp->export();    
            }
        }
        
        if ($this->getBBox() !== null) {
            $data['bbox'] = $this->getBBox();
        }
        
        if ($this->getRouteType() !== null) {
            $data['type'] = $this->getRouteType()->export();
        }
        
        if ($this->getRouteCategory() !== null) {
            $data['category'] = $this->getRouteCategory()->export();
        }
        
        if ($this->getUser() !== null) {
            $data['user'] = $this->getuser()->export();
        }
        
        if ($this->media !== null) {
            $data['media'] = $this->media->export();
        }
        
        // Check the favourite Media for a share image, otherwise pick the first media that has a share image
        $media = $this->getFavouriteMedia();
        if ($media->getSharePath() !== null) {
            $sharemedia = $media;
        } else {
            foreach ($this->getMedias() as $media) {
                if ($media->getSharePath() !== null) {
                    $sharemedia = $media;
                    break;
                }
            }
        }
        
        if (isset($sharemedia)) {
            $data['share_media'] = [
                'mimetype' => 'image/jpeg',
                'path' => Media::BUCKET_NAME . $media->getPath(),
            ];
        }
    
        if (count($this->getAttributes()) > 0) {
            $data['attributes'] = [];
            foreach ($this->getAttributes() as $attribute) {
                $data['attributes'][] = $attribute->export();
            }
        }
        
        return $data;
    }
    
    /**
     * Only used by API
     */
    private $bbox;
    
    /**
     * Only used by API
     */
    public function setBBox($bbox) 
    { 
        $this->bbox = $bbox; 
    }
    
    /**
     * Only used by API
     */
    public function getBBox() 
    { 
        return $this->bbox; 
    }

    /**
     * Add routePublishActivities
     *
     * @param \TB\Bundle\FrontendBundle\Entity\RoutePublishActivity $routePublishActivities
     * @return Route
     */
    public function addRoutePublishActivity(\TB\Bundle\FrontendBundle\Entity\RoutePublishActivity $routePublishActivities)
    {
        $this->routePublishActivities[] = $routePublishActivities;

        return $this;
    }

    /**
     * Remove routePublishActivities
     *
     * @param \TB\Bundle\FrontendBundle\Entity\RoutePublishActivity $routePublishActivities
     */
    public function removeRoutePublishActivity(\TB\Bundle\FrontendBundle\Entity\RoutePublishActivity $routePublishActivities)
    {
        $this->routePublishActivities->removeElement($routePublishActivities);
    }

    /**
     * Get routePublishActivities
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRoutePublishActivities()
    {
        return $this->routePublishActivities;
    }
    
    /**
     * Returns an data array representatiosn of this entity for the activity feed
     */
    public function exportAsActivity()
    {   
        $data = [
            'url' => '/trail/' . $this->getSlug(),
            'objectType' => 'trail',
            'id' => $this->getId(),
            'displayName' => $this->getName(),
        ];
        
        return $data;
    }
    
    /**
     * Set mediaId
     *
     * @param integer $mediaId
     * @return Route
     */
    public function setMediaId($mediaId)
    {
        $this->mediaId = $mediaId;

        return $this;
    }

    /**
     * Get mediaId
     *
     * @return integer 
     */
    public function getMediaId()
    {
        return $this->mediaId;
    }

    /**
     * Set media
     *
     * @param \TB\Bundle\FrontendBundle\Entity\Media $media
     * @return Route
     */
    public function setMedia(\TB\Bundle\FrontendBundle\Entity\Media $media = null)
    {
        $this->media = $media;

        return $this;
    }

    /**
     * Get media
     *
     * @return \TB\Bundle\FrontendBundle\Entity\Media 
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * Add userLikes
     *
     * @param \TB\Bundle\FrontendBundle\Entity\User $userLikes
     * @return Route
     */
    public function addUserLike(\TB\Bundle\FrontendBundle\Entity\User $userLikes)
    {
        $this->userLikes[] = $userLikes;

        return $this;
    }

    /**
     * Remove userLikes
     *
     * @param \TB\Bundle\FrontendBundle\Entity\User $userLikes
     */
    public function removeUserLike(\TB\Bundle\FrontendBundle\Entity\User $userLikes)
    {
        $this->userLikes->removeElement($userLikes);
    }

    /**
     * Get userLikes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUserLikes()
    {
        return $this->userLikes;
    }
    
    /**
     * Checks a User already likes this Route
     *
     * @param User $user The User to check in the follower
     * @return boolean returns true if the User likes this Route, false if not
     */
    public function hasUserLike(User $user)
    {
        foreach ($this->getUserLikes() as $likingUser) {
            if ($likingUser->getId() === $user->getId()) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Add routeUndoLikeActivities
     *
     * @param \TB\Bundle\FrontendBundle\Entity\RouteUndoLikeActivity $routeUndoLikeActivities
     * @return Route
     */
    public function addRouteUndoLikeActivity(\TB\Bundle\FrontendBundle\Entity\RouteUndoLikeActivity $routeUndoLikeActivities)
    {
        $this->routeUndoLikeActivities[] = $routeUndoLikeActivities;

        return $this;
    }

    /**
     * Remove routeUndoLikeActivities
     *
     * @param \TB\Bundle\FrontendBundle\Entity\RouteUndoLikeActivity $routeUndoLikeActivities
     */
    public function removeRouteUndoLikeActivity(\TB\Bundle\FrontendBundle\Entity\RouteUndoLikeActivity $routeUndoLikeActivities)
    {
        $this->routeUndoLikeActivities->removeElement($routeUndoLikeActivities);
    }

    /**
     * Get routeUndoLikeActivities
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRouteUndoLikeActivities()
    {
        return $this->routeUndoLikeActivities;
    }

    /**
     * Add routeLikeActivities
     *
     * @param \TB\Bundle\FrontendBundle\Entity\RouteLikeActivity $routeLikeActivities
     * @return Route
     */
    public function addRouteLikeActivity(\TB\Bundle\FrontendBundle\Entity\RouteLikeActivity $routeLikeActivities)
    {
        $this->routeLikeActivities[] = $routeLikeActivities;

        return $this;
    }

    /**
     * Remove routeLikeActivities
     *
     * @param \TB\Bundle\FrontendBundle\Entity\RouteLikeActivity $routeLikeActivities
     */
    public function removeRouteLikeActivity(\TB\Bundle\FrontendBundle\Entity\RouteLikeActivity $routeLikeActivities)
    {
        $this->routeLikeActivities->removeElement($routeLikeActivities);
    }

    /**
     * Get routeLikeActivities
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRouteLikeActivities()
    {
        return $this->routeLikeActivities;
    }
    
    /**
     * Set homepageOrder
     *
     * @param integer $homepageOrder
     * @return Route
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
     * Retruns the Media that is set as favourite Media, or the first Media. Returns null if no Media for this Route exists
     *
     * @return Media
     */
    public function getFavouriteMedia()
    {
        if ($this->getMedia() !== null) {
            return $this->getMedia();
        } elseif (count($this->getMedias()) > 0) {
            $medias = [];
            foreach ($this->getMedias() as $media) {
                $medias[$media->getId()] = $media;
            }
            ksort($medias);
            return $medias[0];
        } else {
            return null;
        }
    }

    /**
     * Add attributes
     *
     * @param \TB\Bundle\FrontendBundle\Entity\Attribute $attributes
     * @return Route
     */
    public function addAttribute(\TB\Bundle\FrontendBundle\Entity\Attribute $attributes)
    {
        $this->attributes[] = $attributes;

        return $this;
    }

    /**
     * Remove attributes
     *
     * @param \TB\Bundle\FrontendBundle\Entity\Attribute $attributes
     */
    public function removeAttribute(\TB\Bundle\FrontendBundle\Entity\Attribute $attributes)
    {
        $this->attributes->removeElement($attributes);
    }

    /**
     * Get attributes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAttributes()
    {
        return $this->attributes;
    }
    
    /**
     * Checks a User already likes this Route
     *
     * @param Attribute $$attribute The Attribute to check
     * @return boolean returns true if the Route has this Attribute, false if not
     */
    public function hasAttribute(Attribute $attribute)
    {
        foreach ($this->getAttributes() as $routeAttribute) {
            if ($routeAttribute->getId() === $attribute->getId()) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Set approved
     *
     * @param boolean $approved
     * @return Route
     */
    public function setApproved($approved)
    {
        $this->approved = $approved;

        return $this;
    }

    /**
     * Get approved
     *
     * @return boolean 
     */
    public function getApproved()
    {
        return $this->approved;
    }
}
