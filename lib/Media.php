<?php

namespace TB;

abstract class Media {
  public $id;
  public $coords; /* ["long" => , "lat" => ]  */
  public $tmp_path;
  public $metadata;
  public $versions;

  abstract public function readMetadata();

  public function __construct() {
    $this->coords     = array();
    $this->filename = '';
    $this->tmp_path = '';
    $this->tags     = array();
    $this->versions = array();
  }

  public function fromFile($filename="", $tmp_path="") {
    $this->filename = $filename;
    $this->tmp_path = $tmp_path;
    $this->readMetaData();
  }

  public function setCoords($long, $lat) {
    $this->coords["long"] = floatval($long);
    $this->coords["lat"] = floatval($lat);
  }

  public function addVersion($size, $path) {
    $this->versions[] = array('size' => $size, 'path' => $path);
  }

  public function setId($id) { $this->id = $id ; }
  public function getId() { return $this->id ; }
  public function setTag($key, $value) { $this->tags[$key] = $value; }
  public function getTags() { return $this->tags; }
  public function getTag($tag) { 
    if (array_key_exists($tag, $this->tags))
      return $this->tags[$tag]; 
    else
      return null;
  }
}
?>
