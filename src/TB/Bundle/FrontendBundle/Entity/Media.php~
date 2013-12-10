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
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="medias_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="TB\Bundle\FrontendBundle\Entity\Route", mappedBy="media")
     */
    private $route;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->route = new \Doctrine\Common\Collections\ArrayCollection();
    }
    

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
     * Add route
     *
     * @param \TB\Bundle\FrontendBundle\Entity\Route $route
     * @return Media
     */
    public function addRoute(\TB\Bundle\FrontendBundle\Entity\Route $route)
    {
        $this->route[] = $route;
    
        return $this;
    }

    /**
     * Remove route
     *
     * @param \TB\Bundle\FrontendBundle\Entity\Route $route
     */
    public function removeRoute(\TB\Bundle\FrontendBundle\Entity\Route $route)
    {
        $this->route->removeElement($route);
    }

    /**
     * Get route
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRoute()
    {
        return $this->route;
    }
}