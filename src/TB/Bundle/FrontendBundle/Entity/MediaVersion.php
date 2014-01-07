<?php

namespace TB\Bundle\FrontendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MediaVersion
 *
 * @ORM\Table(name="media_versions")
 * @ORM\Entity
 */
class MediaVersion
{
    /**
     * @var integer
     *
     * @ORM\Column(name="version_size", type="smallint", nullable=true)
     */
    private $versionSize;

    /**
     * @var string
     *
     * @ORM\Column(name="path", type="string", length=100, nullable=true)
     */
    private $path;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \TB\Bundle\FrontendBundle\Entity\Media
     *
     * @ORM\ManyToOne(targetEntity="TB\Bundle\FrontendBundle\Entity\Media")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="media_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    private $media;



    /**
     * Set versionSize
     *
     * @param integer $versionSize
     * @return MediaVersion
     */
    public function setVersionSize($versionSize)
    {
        $this->versionSize = $versionSize;
    
        return $this;
    }

    /**
     * Get versionSize
     *
     * @return integer 
     */
    public function getVersionSize()
    {
        return $this->versionSize;
    }

    /**
     * Set path
     *
     * @param string $path
     * @return MediaVersion
     */
    public function setPath($path)
    {
        $this->path = $path;
    
        return $this;
    }

    /**
     * Get path
     *
     * @return string 
     */
    public function getPath()
    {
        return $this->path;
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
     * Set media
     *
     * @param \TB\Bundle\FrontendBundle\Entity\Media $media
     * @return MediaVersion
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