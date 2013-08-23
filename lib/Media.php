<?php

namespace TB;

class Media {
  public $id;
  public $coords; // [$long, $lat]
  public $tmp_path;
  public $metadata;
  public $versions;

  public function setCoords($long, $lat) {
    $this->coords["long"] = floatval($long);
    $this->coords["lat"] = floatval($lat);
  }

  public function addVersion($size, $path) {
    $this->versions[] = array('size' => $size, 'path' => $path);
  }

  public function setId($id) { $this->id = $id ; }
  public function getId() { return $this->id ; }

}


?>
