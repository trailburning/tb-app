<?php

namespace TB\Bundle\FrontendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EventRoute
 *
 * @ORM\Table(name="event_route")
 * @ORM\Entity
 */
class EventRoute
{
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\Column(name="event_id", type="integer")
     */
    private $eventId;

    
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\Column(name="route_id", type="integer")
     */
    private $routeId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date", nullable=true)
     */
    private $date;
    
    /**
     * @var Event
     *
     * @ORM\ManyToOne(targetEntity="Event", inversedBy="eventRoutes")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="event_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    private $event;


    /**
     * @var Route
     *
     * @ORM\ManyToOne(targetEntity="Route", inversedBy="eventRoutes")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="route_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    private $route;

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return EventRoute
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
     * Set event_id
     *
     * @param integer $eventId
     * @return EventRoute
     */
    public function setEventId($eventId)
    {
        $this->eventId = $eventId;

        return $this;
    }

    /**
     * Get event_id
     *
     * @return integer 
     */
    public function getEventId()
    {
        return $this->eventId;
    }

    /**
     * Set route_id
     *
     * @param integer $routeId
     * @return EventRoute
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
     * Set event
     *
     * @param \TB\Bundle\FrontendBundle\Entity\Event $event
     * @return EventRoute
     */
    public function setEvent(\TB\Bundle\FrontendBundle\Entity\Event $event = null)
    {
        $this->event = $event;
        $this->setEventId($event->getId());

        return $this;
    }

    /**
     * Get event
     *
     * @return \TB\Bundle\FrontendBundle\Entity\Event 
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Set route
     *
     * @param \TB\Bundle\FrontendBundle\Entity\Route $route
     * @return EventRoute
     */
    public function setRoute(\TB\Bundle\FrontendBundle\Entity\Route $route = null)
    {
        $this->route = $route;
        $this->setRouteId($route->getId());

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
