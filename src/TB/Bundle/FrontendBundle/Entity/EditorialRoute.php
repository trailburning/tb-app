<?php

namespace TB\Bundle\FrontendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * RouteLike
 *
 * @ORM\Table(name="editorial_route")
 * @ORM\Entity
 */
class EditorialRoute
{
    
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\Column(name="editorial_id", type="integer")
     */
    private $editorialId;
    
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\Column(name="route_id", type="integer")
     */
    private $routeId;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="`order`", type="smallint", nullable=true)
     */
    private $order;    
    
    /**
     * @var string
     *
     * @ORM\Column(name="synopsis", type="text", nullable=true)
     */
    private $synopsis;
    
    /**
     * @var string
     *
     * @ORM\Column(name="text", type="text", nullable=true)
     */
    private $text;

    /**
     * @var Editorial
     *
     * @ORM\ManyToOne(targetEntity="Editorial", inversedBy="editorialRoutes")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="editorial_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    private $editorial;

    /**
     * @var Route
     *
     * @ORM\ManyToOne(targetEntity="Route", inversedBy="editorialRoutes")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="route_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    private $route;


    /**
     * Set editorialId
     *
     * @param integer $editorialId
     * @return EditorialRoute
     */
    public function setEditorialId($editorialId)
    {
        $this->editorialId = $editorialId;

        return $this;
    }

    /**
     * Get editorialId
     *
     * @return integer 
     */
    public function getEditorialId()
    {
        return $this->editorialId;
    }

    /**
     * Set routeId
     *
     * @param integer $routeId
     * @return EditorialRoute
     */
    public function setRouteId($routeId)
    {
        $this->routeId = $routeId;

        return $this;
    }

    /**
     * Get routeId
     *
     * @return integer 
     */
    public function getRouteId()
    {
        return $this->routeId;
    }

    /**
     * Set order
     *
     * @param integer $order
     * @return EditorialRoute
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Get order
     *
     * @return integer 
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set synopsis
     *
     * @param string $synopsis
     * @return EditorialRoute
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
     * @return EditorialRoute
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
     * Set editorial
     *
     * @param \TB\Bundle\FrontendBundle\Entity\Editorial $editorial
     * @return EditorialRoute
     */
    public function setEditorial(\TB\Bundle\FrontendBundle\Entity\Editorial $editorial = null)
    {
        $this->editorial = $editorial;

        return $this;
    }

    /**
     * Get editorial
     *
     * @return \TB\Bundle\FrontendBundle\Entity\Editorial 
     */
    public function getEditorial()
    {
        return $this->editorial;
    }

    /**
     * Set route
     *
     * @param \TB\Bundle\FrontendBundle\Entity\Route $route
     * @return EditorialRoute
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
}
