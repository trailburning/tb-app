<?php

namespace TB\Bundle\APIBundle\Service;

abstract class Media 
{
    public $id;
    public $coords; /* ["long" => , "lat" => ]  */
    public $filename;
    public $tmp_path;
    public $tags;
    public $versions;
    public $mimetype;

    abstract protected function readMetadata();
    abstract protected function verifyFileType();

    public function __construct() 
    {
        $this->coords   = array();
        $this->filename = '';
        $this->tmp_path = '';
        $this->tags     = array();
        $this->versions = array();
    }

    public function fromFile($filename="", $tmp_path="") 
    {
        $this->filename = $filename;
        $this->tmp_path = $tmp_path;
        $this->verifyFileType();
        $this->readMetaData();
    }

    public function setCoords($long, $lat) 
    {
        $this->coords["long"] = floatval($long);
        $this->coords["lat"] = floatval($lat);
    }
    
    public function getCoords() 
    {
        return $this->coords;
    }

    public function addVersion($size, $path) 
    {
        $this->versions[] = array('size' => $size, 'path' => $path);
    }

    public function setTmpPath($tmp_path) 
    { 
        $this->tmp_path = $tmp_path; 
    }
    
    public function getTmpPath() 
    { 
        return $this->tmp_path;
    }
    
    public function setFilename($filename) 
    { 
        $this->filename = $filename;
    }
    
    public function getFilename() 
    { 
        return $this->filename;
    }
    
    public function setId($id) 
    { 
        $this->id = $id; 
    }
    
    public function getId() 
    { 
        return $this->id; 
    }
    
    public function setTag($key, $value) 
    { 
        $this->tags[$key] = $value; 
    }
    
    public function getTags() 
    { 
        return $this->tags; 
    }
    
    public function getTag($tag) 
    {
        if (array_key_exists($tag, $this->tags)) {
            return $this->tags[$tag];
        } else {
            return null;
        }
    }
}
