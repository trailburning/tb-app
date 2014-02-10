<?php 

namespace TB\Bundle\APIBundle\Entity;

use TB\Bundle\FrontendBundle\Entity\GpxFile as BaseGpxFile;

/**
* Extensions to the Route Entity for the API
*/
class GpxFile extends BaseGpxFile
{
    
    /**
     * @Assert\File(maxSize="6000000")
     */
    private $gpxfile;

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