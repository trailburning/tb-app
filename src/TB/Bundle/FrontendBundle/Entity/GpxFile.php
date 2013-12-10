<?php

namespace TB\Bundle\FrontendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * GpxFile
 *
 * @ORM\Table(name="gpx_files")
 * @ORM\Entity
 */
class GpxFile
{
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
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="gpx_files_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;



    /**
     * Set path
     *
     * @param string $path
     * @return GpxFile
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
}