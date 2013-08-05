<?php

namespace TB;

class Media {
  public $id;
  public $coords; // [$long, $lat]
  public $filename;
  public $url;
  public $tmp_path;
  public $metadata;

  public function setCoords($long, $lat) {
    $this->coords["long"] = floatval($long);
    $this->coords["lat"] = floatval($lat);
  }

  public function setId($id) { $this->id = $id ; }
  public function getId() { return $this->id ; }

  

}


?>
