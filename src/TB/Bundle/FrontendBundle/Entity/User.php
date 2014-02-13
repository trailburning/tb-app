<?php

namespace TB\Bundle\FrontendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;

/**
 * User
 *
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"user" = "UserProfile", "brand" = "BrandProfile"})
 */
abstract class User extends BaseUser
{
    
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;
    
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=50, nullable=true)
     */
    protected $name;
    
    /**
     * @var string
     *
     * @ORM\Column(name="about", type="text", nullable=true)
     */
    private $about;
    
    /**
     * @var string
     *
     * @ORM\Column(name="synopsis", type="text", nullable=true)
     */
    private $synopsis;

    /**
     * @var string
     *
     * @ORM\Column(name="avatar", type="string", length=100, nullable=true)
     */
    private $avatar;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="Route", mappedBy="user")
     **/
    private $routes;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="Event", mappedBy="user")
     **/
    private $events;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="Editorial", mappedBy="user")
     **/
    private $editorials;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->routes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->events = new \Doctrine\Common\Collections\ArrayCollection();
        $this->editorials = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    abstract public function getTitle();

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
     * @return User
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
     * Set about
     *
     * @param string $about
     * @return User
     */
    public function setAbout($about)
    {
        $this->about = $about;

        return $this;
    }

    /**
     * Get about
     *
     * @return string 
     */
    public function getAbout()
    {
        return $this->about;
    }

    /**
     * Set synopsis
     *
     * @param string $synopsis
     * @return User
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
     * Set avatar
     *
     * @param string $avatar
     * @return User
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;

        return $this;
    }

    /**
     * Get avatar
     *
     * @return string 
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * Add routes
     *
     * @param \TB\Bundle\FrontendBundle\Entity\Route $routes
     * @return User
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
     * Add events
     *
     * @param \TB\Bundle\FrontendBundle\Entity\Event $events
     * @return User
     */
    public function addEvent(\TB\Bundle\FrontendBundle\Entity\Event $events)
    {
        $this->events[] = $events;

        return $this;
    }

    /**
     * Remove events
     *
     * @param \TB\Bundle\FrontendBundle\Entity\Event $events
     */
    public function removeEvent(\TB\Bundle\FrontendBundle\Entity\Event $events)
    {
        $this->events->removeElement($events);
    }

    /**
     * Get events
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * Add editorials
     *
     * @param \TB\Bundle\FrontendBundle\Entity\Editorial $editorials
     * @return User
     */
    public function addEditorial(\TB\Bundle\FrontendBundle\Entity\Editorial $editorials)
    {
        $this->editorials[] = $editorials;

        return $this;
    }

    /**
     * Remove editorials
     *
     * @param \TB\Bundle\FrontendBundle\Entity\Editorial $editorials
     */
    public function removeEditorial(\TB\Bundle\FrontendBundle\Entity\Editorial $editorials)
    {
        $this->editorials->removeElement($editorials);
    }

    /**
     * Get editorials
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getEditorials()
    {
        return $this->editorials;
    }
}
