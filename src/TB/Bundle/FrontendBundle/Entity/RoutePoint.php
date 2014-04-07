<?php

namespace TB\Bundle\FrontendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RoutePoint
 *
 * @ORM\Table(name="route_points")
 * @ORM\Entity
 */
class RoutePoint implements \JsonSerializable
{
    /**
     * @var integer
     *
     * @ORM\Column(name="point_number", type="integer", nullable=false)
     */
    private $pointNumber;

    /**
     * @var hstore
     *
     * @ORM\Column(name="tags", type="hstore", nullable=true)
     */
    private $tags;

    /**
     * @var Point point
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
     * @var integer
     *
     * @ORM\Column(name="route_id", type="integer")
     */
    private $routeId;

    /**
     * @var \TB\Bundle\FrontendBundle\Entity\Route
     *
     * @ORM\ManyToOne(targetEntity="TB\Bundle\FrontendBundle\Entity\Route", inversedBy="routePoints")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="route_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    private $route;

    /**
     * Set pointNumber
     *
     * @param integer $pointNumber
     * @return RoutePoint
     */
    public function setPointNumber($pointNumber)
    {
        $this->pointNumber = $pointNumber;
    
        return $this;
    }

    /**
     * Get pointNumber
     *
     * @return integer 
     */
    public function getPointNumber()
    {
        return $this->pointNumber;
    }

    /**
     * Set tags
     *
     * @param hstore $tags
     * @return RoutePoint
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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set route
     *
     * @param \TB\Bundle\FrontendBundle\Entity\Route $route
     * @return RoutePoint
     */
    public function setRoute(\TB\Bundle\FrontendBundle\Entity\Route $route = null)
    {
        $this->route = $route;
    
        return $this;
    }

    /**
     * Get route
     *
     * @return \TB\Bundle\FrontendBundle\Entity\Route 
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Set coords
     *
     * @param point $coords
     * @return RoutePoint
     */
    public function setCoords($coords)
    {
        $this->coords = $coords;

        return $this;
    }

    /**
     * Get coords
     *
     * @return point 
     */
    public function getCoords()
    {
        return $this->coords;
    }
    
    public function jsonSerialize() 
    {
        $data = [
            'coords' => [
                $this->getCoords()->getLongitude(), 
                $this->getCoords()->getLatitude(),
            ],
            'tags' => $this->getTags(),
        ];                       
        
        return json_encode($data);
    }
}
