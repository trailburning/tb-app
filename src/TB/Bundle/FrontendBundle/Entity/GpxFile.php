<?php

namespace TB\Bundle\FrontendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Gaufrette\Filesystem;

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
     * @Assert\File(maxSize="12m")
     */
    private $file;

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
    public function setFile(UploadedFile $file)
    {
       $this->file = $file;
    }

    /**
     * Get file.
     *
     * @return UploadedFile
     */
    public function getFile()
    {
       return $this->file;
    }
    
    /**
     * Move the file to the provided Filesystem
     * Sets the filename to the path field
     *
     * @return the name of the uploaded file
     */
    public function upload(Filesystem $filesystem)
    {
        // the file property can be empty if the field is not required
        if (null === $this->getFile()) {
            throw new \Exception('gpxFile is empty');
        }
        
        $file = $this->getFile();
        
        $filename = sprintf('%s/%s/%s/%s.gpx', date('Y'), date('m'), date('d'), uniqid());
        
        $adapter = $filesystem->getAdapter();
        // $adapter->setMetadata($filename, array('contentType' => $file->getClientMimeType())); // doesn't work with in_memory adapter
        $adapter->write($filename, file_get_contents($file->getPathname()));
        $this->setPath($filename);
        
        // clean up the file property as you won't need it anymore
        $this->file = null;
        
        return $filename;
    }
}