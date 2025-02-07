<?php

namespace TB\Bundle\FrontendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\File;
use Gaufrette\Filesystem;
use Gaufrette\Adapter\MetadataSupporter;
use Gaufrette\Adapter\AwsS3;
use TB\Bundle\FrontendBundle\Service\MediaImporter;

/**
 * Media
 *
 * @ORM\Table(name="medias")
 * @ORM\Entity
 */
class Media implements Exportable
{
    
    const BUCKET_NAME = '';
    const S3_SERVER = 'http://media.trailburning.com';
    
    const EXIF_ORIENTATION_TOP_LEFT_SIDE = 1;
    const EXIF_ORIENTATION_TOP_RIGHT_SIDE = 2;
    const EXIF_ORIENTATION_BOTTOM_RIGHT_SIDE = 3;
    const EXIF_ORIENTATION_BOTTOM_LEFT_SIDE = 4;
    const EXIF_ORIENTATION_LEFT_SIDE_TOP = 5;
    const EXIF_ORIENTATION_RIGHT_SIDE_TOP = 6;
    const EXIF_ORIENTATION_RIGHT_SIDE_BOTTOM = 7;
    const EXIF_ORIENTATION_LEFT_SIDE_BOTTOM = 8;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;
    
    /**
     * @var hstore
     *
     * @ORM\Column(name="tags", type="hstore", nullable=true)
     */
    private $tags;

    /**
     * @var point
     *
     * @ORM\Column(name="coords", type="point", columnDefinition="GEOMETRY(POINT,4326)", nullable=true)
     */
    private $coords;
    
    /**
     * @var string
     *
     * @ORM\Column(name="path", type="string", length=100)
     */
    private $path;
    
    /**
     * @var string
     *
     * @ORM\Column(name="share_path", type="string", length=100, nullable=true)
     */
    private $sharePath;
    
    /**
     * @var string
     *
     * @ORM\Column(name="filename", type="string", length=100)
     */
    private $filename;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="route_id", type="integer")
     */
    private $routeId;
    
    /**
     * @var \TB\Bundle\FrontendBundle\Entity\Route
     *
     * @ORM\ManyToOne(targetEntity="TB\Bundle\FrontendBundle\Entity\Route", inversedBy="medias")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="route_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    private $route;
    
    
    /**
     * @Assert\File(maxSize="6M")
     */
    private $file;

    /**
     * Set tags
     *
     * @param hstore $tags
     * @return Media
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    
        return $this;
    }

    /**
     * Get tags
     *
     * @return hstore 
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Set coords
     *
     * @param geometry $coords
     * @return Media
     */
    public function setCoords($coords)
    {
        $this->coords = $coords;
    
        return $this;
    }

    /**
     * Get coords
     *
     * @return geometry 
     */
    public function getCoords()
    {
        return $this->coords;
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
     * Constructor
     */
    public function __construct()
    {
        
    }
    
    /**
     * Sets file.
     *
     * @param UploadedFile $file
     */
    public function setFile(File $file)
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
     * Sets the path and filename field
     *
     * @param Filesystem $filesystem where the file gets uploaded to
     * @param Int $routeId the ID of the associated Route for the path
     * @return the name of the uploaded file
     */
    public function upload(Filesystem $filesystem)
    {
        if ($this->getFile() === null) {
            throw new \Exception('Not file was provided.');
        }
        
        if ($this->getRoute() === null) {
            throw new \Exception('Route must be set before uploading a file.');
        }
        
        if ($this->getRoute()->getId() == 0) {
            throw new \Exception('The Route must be persisted before uploading a file.');
        }
        
        $file = $this->getFile();
        
        if (filesize($this->file->getPathname()) < 11 || exif_imagetype($this->file->getPathname()) != 2) {
            throw new \Exception(sprintf('Invalid type for %s, only JPEG files are allowed.', $this->file->getClientOriginalName()));
        }
        
        $filename = sprintf('/%s/%s.%s', $this->getRoute()->getId(), sha1_file($this->file->getPathname()), $this->file->getClientOriginalExtension());
        
        $adapter = $filesystem->getAdapter();
        // Store Metadata to S3 (doesn't work in unit tests when using memory filesystem)
        if ($adapter instanceof \Gaufrette\Adapter\MetadataSupporter) {
            $adapter->setMetadata($filename, array('ContentType' => 'image/jpeg', 'ACL' => 'public-read'));
        }
        
        $adapter->write($filename, file_get_contents($this->file->getPathname()));
        
        $this->setPath($filename);
        $this->setFilename($this->file->getClientOriginalName());
        
        // clean up the file property as you won't need it anymore
        $this->file = null;
        
        return $filename;
    }
    
    /**
     * Extract Metadata from a provided Jpeg image and sets filesize, datetime, width and height, latitude as tags 
     * and longitude, latidute as coords
     *
     * @param MediaImporter $mediImporter media importer helper class
     * @throws Excpetion when the route field is empty, the Route was not persisted before, the image field is not set
     * @throws Excpetion No DateTime metadata is found
     */
    public function readMetadata(MediaImporter $mediaImporter)
    {
        if ($this->getFile() === null) {
            throw new \Exception('file is empty');
        }
        
        if ($this->getRoute() === null) {
            throw new \Exception('Route must be set before uploading a file');
        }
        
        if ($this->getRoute()->getId() == 0) {
            throw new \Exception('The Route must be persisted before uploading a file');
        }
        
        if (exif_imagetype($this->file->getPathname()) != 2) {
            throw new \Exception(sprintf('Invalid type for %s, only JPEG files are allowed.', $this->file->getClientOriginalName()));
        }
        
        $tags = $this->getTags();
        
        $exiftags = exif_read_data($this->file->getPathname());

        if (isset($exiftags['FileSize'])) { 
            $tags['filesize'] = $exiftags['FileSize']; 
        }
        
        $geoPoint = $mediaImporter->getGeometryPointFromExif($exiftags);
        
        if ($geoPoint) {
            // When the image has GPS data, get the datetime from the nearest RoutePoint by GPS
            $routePoint = $mediaImporter->getNearestRoutePointByGeoPoint($this->getRoute(), $geoPoint);
            if (!isset($routePoint->getTags()['datetime'])) {
                 throw new \Exception(sprintf('missing datetime tag for RoutePoint with id: %s', $routePoint->getId()));
            }
            $datetime = $routePoint->getTags()['datetime'];
        } else {
            // When the image has no GPS data, get the datetime from the image
            if (isset($exiftags['DateTimeOriginal'])) {
                $datetime = intval(strtotime($exiftags['DateTimeOriginal']));
            } elseif (isset($exiftags['DateTime'])) {
                $datetime = intval(strtotime($exiftags['DateTime']));
            } else {
                // When the image has no datetime, get the datetimt from the first RoutePoint
                $routePoint = $mediaImporter->getFirstRoutePoint($this->getRoute());
                if (!isset($routePoint->getTags()['datetime'])) {
                    throw new \Exception('Image and first RoutePoint have no datetime information');
                }
                $datetime = $routePoint->getTags()['datetime'];
            }
            // the image contains no information about the timezone where the image was taken, the routes timezone is UTC.
            // get a timezone offset by the related Route and substract it from the datetime of the image to get a UTC datetime.
            $timezoneOffset = $mediaImporter->getRouteTimezoneOffset($this->getRoute());
            $datetime = $datetime - $timezoneOffset;
        }
        
        $tags['datetime'] = $datetime;
       
        if (isset($exiftags['Orientation']) 
            && in_array($exiftags['Orientation'], [self::EXIF_ORIENTATION_LEFT_SIDE_TOP, self::EXIF_ORIENTATION_RIGHT_SIDE_TOP, self::EXIF_ORIENTATION_RIGHT_SIDE_BOTTOM, self::EXIF_ORIENTATION_LEFT_SIDE_BOTTOM])) {
            if (isset($exiftags['COMPUTED']) && isset($exiftags['COMPUTED']['Width'])) {
                $tags['height'] = $exiftags['COMPUTED']['Width']; 
            }
            if (isset($exiftags['COMPUTED']) && isset($exiftags['COMPUTED']['Height'])) {
                $tags['width'] = $exiftags['COMPUTED']['Height']; 
            }
        } else {
            if (isset($exiftags['COMPUTED']) && isset($exiftags['COMPUTED']['Width'])) {
                $tags['width'] = $exiftags['COMPUTED']['Width']; 
            }
            if (isset($exiftags['COMPUTED']) && isset($exiftags['COMPUTED']['Height'])) {
                $tags['height'] = $exiftags['COMPUTED']['Height']; 
            }
        }
        
        //get the longitude, latitude and altitude from the nearest RoutePoint by datetime
        $routePoint = $mediaImporter->getNearestRoutePointByTime($this->getRoute(), $datetime);
        $this->setCoords($routePoint->getCoords());
        if (isset($routePoint->getTags()['altitude']) && $routePoint->getTags()['altitude'] != '') {
            // empty values in attributes cauces a problem with postgres hstore field type, therefore check if not empty
            $tags['altitude'] = $routePoint->getTags()['altitude'];
        }
        
        $this->setTags($tags);
    }

    /**
     * Set id
     *
     * @param integer $routeId
     * @return Media
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get routeId
     *
     * @return integer 
     */
    public function getRouteId()
    {
        return $this->routeId;
    }

    /**
     * Set route
     *
     * @param \TB\Bundle\FrontendBundle\Entity\Route $route
     * @return Media
     */
    public function setRoute(\TB\Bundle\FrontendBundle\Entity\Route $route = null)
    {
        $this->route = $route;

        return $this;
    }

    /**
     * Get route
     *
     * @return \TB\Bundle\FrontendBundle\Entity\Route 
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Set path
     *
     * @param string $path
     * @return Media
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
     * Set filename
     *
     * @param string $filename
     * @return Media
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Get filename
     *
     * @return string 
     */
    public function getFilename()
    {
        return $this->filename;
    }

    public function export()
    {
        $data = [
            'id' => $this->getId(),
            'filename' => $this->getFilename(),
            'mimetype' => 'image/jpeg',
            'versions' => [[
                'path' => self::BUCKET_NAME . $this->getPath(),
                'size' => 0,
            ]],
            'coords' => [
                'long' => $this->getCoords()->getLongitude(),
                'lat' => $this->getCoords()->getLatitude(),
            ],
            'tags' => $this->getTags(),
        ];
        
        return $data;
    }
    
    /**
     * Constructs the absolute path to the media file at Amazon S3
     *
     * @return The absolute path to the media file
     * @throws Exception when the path field, that is needed to construct the path, is not set 
     */
    public function getAbsolutePath()
    {
        if ($this->getPath() != '') {
            return sprintf('%s%s%s', self::S3_SERVER, self::BUCKET_NAME, $this->getPath());
        } else {
            throw new \Exception('Missing path for media');
        }
    }

    /**
     * Set sharePath
     *
     * @param string $sharePath
     * @return Media
     */
    public function setSharePath($sharePath)
    {
        $this->sharePath = $sharePath;

        return $this;
    }

    /**
     * Get sharePath
     *
     * @return string 
     */
    public function getSharePath()
    {
        return $this->sharePath;
    }

    /**
     * Constructs the absolute path to the share media file at Amazon S3
     *
     * @return The absolute path to the media file
     * @throws Exception when the path field, that is needed to construct the path, is not set 
     */
    public function getAbsoluteSharePath()
    {
        if ($this->getPath() != '') {
            return sprintf('%s%s%s', self::S3_SERVER, self::BUCKET_NAME, $this->getPath());
        } else {
            return null;
        }
    }

    /**
     * Set routeId
     *
     * @param integer $routeId
     * @return Media
     */
    public function setRouteId($routeId)
    {
        $this->routeId = $routeId;

        return $this;
    }
}
