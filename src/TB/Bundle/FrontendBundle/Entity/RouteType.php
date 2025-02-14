<?php

namespace TB\Bundle\FrontendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RouteType
 *
 * @ORM\Table(name="route_type")
 * @ORM\Entity
 */
class RouteType implements Exportable
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=50)
     */
    private $name;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="Route", mappedBy="routeType")
     **/
    private $routes;

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
     * Set id
     *
     * @param integer $id
     * @return RouteType
     */
    public function setId($id)
    {
        $this->id = $id;
    
        return $this;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return RouteType
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
     * Constructor
     */
    public function __construct()
    {
        $this->routes = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add routes
     *
     * @param \TB\Bundle\FrontendBundle\Entity\Route $routes
     * @return RouteType
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

    public function export()
    {
        $data = [
            'id' => $this->getId(),
            'name' => $this->getName(),
        ];
        
        return $data;
    }
}
