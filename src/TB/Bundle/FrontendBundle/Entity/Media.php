<?php

namespace TB\Bundle\FrontendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Media
 *
 * @ORM\Table(name="medias")
 * @ORM\Entity
 */
class Media
{
    /**
     * @var hstore
     *
     * @ORM\Column(name="tags", type="hstore", nullable=true)
     */
    private $tags;

    /**
     * @var point
     *
     * @ORM\Column(name="coords", type="point", columnDefinition="GEOMETRY(POINT,4326)", nullable=true)
     */
    private $coords;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;
    
    
    #/**
    # * @var \Doctrine\Common\Collections\Collection
    # *
    # * @ORM\OneToMany(targetEntity="RouteMedia", mappedBy="media")
    # **/
    #private $routeMedias;
    

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="TB\Bundle\FrontendBundle\Entity\Route", mappedBy="medias")
     */
    private $routes;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="MediaVersion", mappedBy="media")
     **/
    private $versions;
    

    /**
     * Set tags
     *
     * @param hstore $tags
     * @return Media
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
     * Set coords
     *
     * @param geometry $coords
     * @return Media
     */
    public function setCoords($coords)
    {
        $this->coords = $coords;
    
        return $this;
    }

    /**
     * Get coords
     *
     * @return geometry 
     */
    public function getCoords()
    {
        return $this->coords;
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
     * Constructor
     */
    public function __construct()
    {
        $this->routes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->versions = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add versions
     *
     * @param \TB\Bundle\FrontendBundle\Entity\MediaVersion $versions
     * @return Media
     */
    public function addVersion(\TB\Bundle\FrontendBundle\Entity\MediaVersion $versions)
    {
        $this->versions[] = $versions;

        return $this;
    }

    /**
     * Remove versions
     *
     * @param \TB\Bundle\FrontendBundle\Entity\MediaVersion $versions
     */
    public function removeVersion(\TB\Bundle\FrontendBundle\Entity\MediaVersion $versions)
    {
        $this->versions->removeElement($versions);
    }

    /**
     * Get versions
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getVersions()
    {
        return $this->versions;
    }

    /**
     * Add routes
     *
     * @param \TB\Bundle\FrontendBundle\Entity\Route $routes
     * @return Media
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
}
