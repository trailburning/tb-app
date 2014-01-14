<?php

namespace TB\Bundle\FrontendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RouteMedias
 *
 * @ORM\Table(name="route_medias")
 * @ORM\Entity
 */
class RouteMedia
{

    /**
     * @var integer
     *
     * @ORM\Column(name="route_id", type="integer")
     * @ORM\Id
     */
    private $routeId;

    
    /**
     * @var integer
     *
     * @ORM\Column(name="media_id", type="integer")
     * @ORM\Id
     */
    private $mediaId;


    /**
     * @var integer
     *
     * @ORM\Column(name="linear_position", type="float", nullable=true)
     */
    private $linear_position;
    
    
    /**
     * @var \TB\Bundle\FrontendBundle\Entity\Route
     *
     * @ORM\ManyToOne(targetEntity="TB\Bundle\FrontendBundle\Entity\Route", inversedBy="routeMedias")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="route_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    private $route;
    
    /**
     * @var \TB\Bundle\FrontendBundle\Entity\Media
     *
     * @ORM\ManyToOne(targetEntity="TB\Bundle\FrontendBundle\Entity\Media", inversedBy="routeMedias")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="media_id", referencedColumnName="id")
     * })
     */
    private $media;
    

    /**
     * Set route_id
     *
     * @param integer $routeId
     * @return RouteMedia
     */
    public function setRouteId($routeId)
    {
        $this->routeId = $routeId;

        return $this;
    }

    /**
     * Get route_id
     *
     * @return integer 
     */
    public function getRouteId()
    {
        return $this->routeId;
    }

    /**
     * Set media_id
     *
     * @param integer $mediaId
     * @return RouteMedia
     */
    public function setMediaId($mediaId)
    {
        $this->mediaId = $mediaId;

        return $this;
    }

    /**
     * Get media_id
     *
     * @return integer 
     */
    public function getMediaId()
    {
        return $this->mediaId;
    }

    /**
     * Set linear_position
     *
     * @param float $linearPosition
     * @return RouteMedia
     */
    public function setLinearPosition($linearPosition)
    {
        $this->linear_position = $linearPosition;

        return $this;
    }

    /**
     * Get linear_position
     *
     * @return float 
     */
    public function getLinearPosition()
    {
        return $this->linear_position;
    }

    /**
     * Set route
     *
     * @param \TB\Bundle\FrontendBundle\Entity\Route $route
     * @return RouteMedia
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
     * Set media
     *
     * @param \TB\Bundle\FrontendBundle\Entity\Media $media
     * @return RouteMedia
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
}
