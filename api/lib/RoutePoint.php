<?php

namespace TB;

class RoutePoint {
  public $coords;
  public $tags;

  public function __construct($long, $lat, $tags) {
    $this->coords['long'] = $long;
    $this->coords['lat'] = $lat;
    $this->tags = $tags;
  }

  public function getTags() {
    return $this->tags;
  }

  public function getCoords() {
    return $this->coords;
  }
}

?>
