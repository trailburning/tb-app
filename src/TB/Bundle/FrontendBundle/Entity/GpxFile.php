<?php

namespace TB\Bundle\FrontendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @Assert\File(maxSize="6000000")
     */
    private $gpxfile;

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
    
    /**
     * Sets file.
     *
     * @param UploadedFile $file
     */
    public function setGpxfile(UploadedFile $gpxfile = null)
    {
       $this->gpxfile = $gpxfile;
    }

    /**
     * Get file.
     *
     * @return UploadedFile
     */
    public function getGpxfile()
    {
       return $this->gpxfile;
    }
    
    public function upload()
    {
        // the file property can be empty if the field is not required
        if (null === $this->getGpxfile()) {
            return;
        }

        // use the original file name here but you should
        // sanitize it at least to avoid any security issues

        // move takes the target directory and then the
        // target filename to move to
        $this->getGpxfile()->move(
            $this->getUploadRootDir(),
            $this->getGpxfile()->getClientOriginalName()
        );

        // set the path property to the filename where you've saved the file
        $this->path = $this->getGpxfile()->getClientOriginalName();

        // clean up the file property as you won't need it anymore
        $this->file = null;
    }
}