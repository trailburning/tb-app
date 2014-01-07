<?php

namespace TB\Bundle\FrontendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TzWorldMp
 *
 * @ORM\Table(name="tz_world_mp")
 * @ORM\Entity
 */
class TzWorldMp
{
    /**
     * @var string
     *
     * @ORM\Column(name="tzid", type="string", length=30, nullable=true)
     */
    private $tzid;

    /**
     * @var MultiPolygon
     *
     * @ORM\Column(name="geom", type="geometry", columnDefinition="GEOMETRY(MULTIPOLYGON)", nullable=true)
     */
    private $geom;

    /**
     * @var integer
     *
     * @ORM\Column(name="gid", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $gid;



    /**
     * Set tzid
     *
     * @param string $tzid
     * @return TzWorldMp
     */
    public function setTzid($tzid)
    {
        $this->tzid = $tzid;
    
        return $this;
    }

    /**
     * Get tzid
     *
     * @return string 
     */
    public function getTzid()
    {
        return $this->tzid;
    }

    /**
     * Set geom
     *
     * @param geometry $geom
     * @return TzWorldMp
     */
    public function setGeom($geom)
    {
        $this->geom = $geom;
    
        return $this;
    }

    /**
     * Get geom
     *
     * @return geometry 
     */
    public function getGeom()
    {
        return $this->geom;
    }

    /**
     * Get gid
     *
     * @return integer 
     */
    public function getGid()
    {
        return $this->gid;
    }
}