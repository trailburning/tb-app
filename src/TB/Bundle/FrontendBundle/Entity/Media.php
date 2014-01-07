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
    
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="RouteMedia", mappedBy="media")
     **/
    private $routeMedias;
    

    #/**
    # * @var \Doctrine\Common\Collections\Collection
    # *
    # * @ORM\ManyToMany(targetEntity="TB\Bundle\FrontendBundle\Entity\Route", mappedBy="medias")
    # */
    #private $routes;
    

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
     * Add routeMedias
     *
     * @param \TB\Bundle\FrontendBundle\Entity\RouteMedia $routeMedias
     * @return Media
     */
    public function addRouteMedia(\TB\Bundle\FrontendBundle\Entity\RouteMedia $routeMedias)
    {
        $this->routeMedias[] = $routeMedias;

        return $this;
    }

    /**
     * Remove routeMedias
     *
     * @param \TB\Bundle\FrontendBundle\Entity\RouteMedia $routeMedias
     */
    public function removeRouteMedia(\TB\Bundle\FrontendBundle\Entity\RouteMedia $routeMedias)
    {
        $this->routeMedias->removeElement($routeMedias);
    }

    /**
     * Get routeMedias
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRouteMedias()
    {
        return $this->routeMedias;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->routeMedias = new \Doctrine\Common\Collections\ArrayCollection();
    }

}
