<?php

namespace TB;

class RoutePoint {
  public $lat, $long;
  public $tags;

  public function __construct($long, $lat, $tags) {
    $this->long = $long;
    $this->lat = $lat;
    $this->tags = $tags;
  }

  public function getTags() {
    return $this->tags;
  }

  public function getCoords() {
    return array($this->long, $this->lat);
  }
}

?>
