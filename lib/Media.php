<?php

namespace TB;

abstract class Media {
  public $id;
  public $coords; // [$long, $lat]
  public $tmp_path;
  public $metadata;
  public $versions;

  public function __construct($filename="", $tmp_path="") {
    $this->coords     = array();
    $this->filename = $filename;
    $this->tmp_path = $tmp_path;
    $this->tags     = array();
    $this->versions = array();
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

  abstract public function readMetadata();
}
?>
