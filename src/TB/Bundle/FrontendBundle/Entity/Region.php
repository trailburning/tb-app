<?php

namespace TB\Bundle\FrontendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Region
 *
 * @ORM\Table(name="region")
 * @ORM\Entity
 */
class Region implements Exportable
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
     * @var string
     *
     * @ORM\Column(name="about", type="text")
     */
    private $about;

    /**
     * @var string
     *
     * @ORM\Column(name="image", type="string", length=50)
     */
    private $image;

    /**
     * @var string
     *
     * @ORM\Column(name="logo", type="string", length=50)
     */
    private $logo;

    /**
     * @var string
     *
     * @ORM\Column(name="link", type="string", length=100)
     */
    private $link;
    
    /**
     * @var string
     *
     * @ORM\Column(name="slug", type="string", length=50)
     */
    private $slug;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="Event", mappedBy="region")
     **/
    private $events;


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
     * @return Region
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
     * @return Region
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
     * Set image
     *
     * @param string $image
     * @return Region
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

    /**
     * Set logo
     *
     * @param string $logo
     * @return Region
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;

        return $this;
    }

    /**
     * Get logo
     *
     * @return string 
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * Set link
     *
     * @param string $link
     * @return Region
     */
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * Get link
     *
     * @return string 
     */
    public function getLink()
    {
        return $this->link;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->events = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add events
     *
     * @param \TB\Bundle\FrontendBundle\Entity\Event $events
     * @return Region
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
     * Set slug
     *
     * @param string $slug
     * @return Region
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
    
    public function export()
    {
        $data = [
            'id' => $this->getId(),
            'slug' => $this->getSlug(),
            'name' => $this->getName(),
            'image' => $this->getImage(),
            'logo' => $this->getLogo(),
            'link' => $this->getLink(),
            'about' => $this->getAbout(),
        ];
        
        return $data;
    }
}
