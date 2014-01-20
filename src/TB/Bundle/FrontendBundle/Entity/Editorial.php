<?php

namespace TB\Bundle\FrontendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Editorial
 *
 * @ORM\Table(name="editorial")
 * @ORM\Entity
 */
class Editorial
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
     * @ORM\Column(name="slug", type="string", length=50)
     */
    private $slug;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="TB\Bundle\FrontendBundle\Entity\Route", inversedBy="editorials")
     */
    private $routes;
    
    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=50)
     */
    private $title;
    
    /**
     * @var string
     *
     * @ORM\Column(name="synopsis", type="text")
     */
    private $synopsis;
    
    /**
     * @var string
     *
     * @ORM\Column(name="text", type="text")
     */
    private $text;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="user_id", type="integer")
     */
    private $userId;
    
    /**
     * @var \TB\Bundle\FrontendBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="TB\Bundle\FrontendBundle\Entity\User", inversedBy="editorials")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    private $user;
    
    /**
     * @var date
     *
     * @ORM\Column(name="date", type="date")
     */
    private $date;
    
    /**
     * @var string
     *
     * @ORM\Column(name="image", type="string", length=50)
     */
    private $image;
    
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
     * Set slug
     *
     * @param string $slug
     * @return Editorial
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string 
     */
    public function getSlug()
    {
        return $this->slug;
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
     * @return Editorial
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
     * Set title
     *
     * @param string $title
     * @return Editorial
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set synopsis
     *
     * @param string $synopsis
     * @return Editorial
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
     * @return Editorial
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
     * Set userId
     *
     * @param integer $userId
     * @return Editorial
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
     * Set user
     *
     * @param \TB\Bundle\FrontendBundle\Entity\User $user
     * @return Editorial
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

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return Editorial
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
     * Set image
     *
     * @param string $image
     * @return Editorial
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return string 
     */
    public function getImage()
    {
        return $this->image;
    }
}
