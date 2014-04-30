<?php

namespace TB\Bundle\FrontendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Attribute
 *
 * @ORM\Table(name="attribute", uniqueConstraints={@ORM\UniqueConstraint(name="unique_attribute", columns={"name", "type"})})
 * @ORM\Entity
 */
class Attribute implements Exportable
{
    
    private static $validTypes = [
        'activity',
    ];
    
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
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=50)
     */
    private $type;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="TB\Bundle\FrontendBundle\Entity\Route", mappedBy="tags")
     */
    private $routes;

    public function export()
    {
        $data = [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'type' => $this->getType(),
        ];
        
        return $data;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->routes = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set name
     *
     * @param string $name
     * @return Attribute
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
     * Add routes
     *
     * @param \TB\Bundle\FrontendBundle\Entity\Route $routes
     * @return Attribute
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

    /**
     * Set type
     *
     * @param string $type
     * @return Attribute
     */
    public function setType($type)
    {
        
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }
    
    static public function isValidType($type)
    {
        return (in_array($type, self::$validTypes)) ? true : false;
    }
    
}
