<?php

namespace TB\Bundle\FrontendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Route
 *
 * @ORM\Table(name="routes")
 * @ORM\Entity
 */
class Route
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
     * @ORM\Column(name="user_id", type="integer", nullable=false)
     */
    private $userId;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="routes_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var \TB\Bundle\FrontendBundle\Entity\GpxFile
     *
     * @ORM\ManyToOne(targetEntity="TB\Bundle\FrontendBundle\Entity\GpxFile")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="gpx_file_id", referencedColumnName="id")
     * })
     */
    private $gpxFile;

    /**
     * @var \TB\Bundle\FrontendBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="TB\Bundle\FrontendBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    private $user;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="TB\Bundle\FrontendBundle\Entity\Media", inversedBy="routes")
     * @ORM\JoinTable(name="route_medias")
     */
    private $medias;
    
    /**
     * @var string
     *
     * @ORM\Column(name="about", type="text", nullable=true)
     */
    private $about;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="TB\Bundle\FrontendBundle\Entity\Event", mappedBy="routes")
     */
    private $events;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="TB\Bundle\FrontendBundle\Entity\Editorial", mappedBy="routes")
     */
    private $editorial;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->media = new \Doctrine\Common\Collections\ArrayCollection();
    }
    

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
     * Get id
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
     * Add events
     *
     * @param \TB\Bundle\FrontendBundle\Entity\Event $events
     * @return Route
     */
    public function addEvent(\TB\Bundle\FrontendBundle\Entity\Event $events)
    {
        $this->events[] = $events;

        return $this;
    }

    /**
     * Remove events
     *
     * @param \TB\Bundle\FrontendBundle\Entity\Event $events
     */
    public function removeEvent(\TB\Bundle\FrontendBundle\Entity\Event $events)
    {
        $this->events->removeElement($events);
    }

    /**
     * Get events
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getEvents()
    {
        return $this->events;
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
     * Add editorial
     *
     * @param \TB\Bundle\FrontendBundle\Entity\Editorial $editorial
     * @return Route
     */
    public function addEditorial(\TB\Bundle\FrontendBundle\Entity\Editorial $editorial)
    {
        $this->editorial[] = $editorial;

        return $this;
    }

    /**
     * Remove editorial
     *
     * @param \TB\Bundle\FrontendBundle\Entity\Editorial $editorial
     */
    public function removeEditorial(\TB\Bundle\FrontendBundle\Entity\Editorial $editorial)
    {
        $this->editorial->removeElement($editorial);
    }

    /**
     * Get editorial
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getEditorial()
    {
        return $this->editorial;
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
    
    
}
