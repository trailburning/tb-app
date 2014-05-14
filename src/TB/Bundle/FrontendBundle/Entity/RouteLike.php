<?php

namespace TB\Bundle\FrontendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RouteLike
 *
 * @ORM\Table(name="route_like")
 * @ORM\Entity
 */
class RouteLike
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
     * @ORM\Column(name="user_id", type="integer")
     * @ORM\Id
     */
    private $userId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date")
     */
    private $date;

    /**
     * @var Route
     *
     * @ORM\ManyToOne(targetEntity="Route", inversedBy="routesLikes")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="route_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    private $route;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="routesLikes")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    private $user;


    /**
     * Set routeId
     *
     * @param integer $routeId
     * @return RouteLike
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
     * Set userId
     *
     * @param integer $userId
     * @return RouteLike
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
     * Set date
     *
     * @param \DateTime $date
     * @return RouteLike
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
     * Set route
     *
     * @param \TB\Bundle\FrontendBundle\Entity\Route $route
     * @return RouteLike
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
     * Set user
     *
     * @param \TB\Bundle\FrontendBundle\Entity\User $user
     * @return RouteLike
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
}
