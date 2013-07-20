<?php

class RoutePoint {
  public $lat, $lon;
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

class Route {
  public $tags;
  public $routepoints;
  public $centroid;

  public function __construct($tags = NULL) {
    $this->tags = $tags;
    $this->routepoints = array();
  }

  public function setTag($key, $value) {
    $this->tags[$key] = $value;
  }

  public function getTags() {
    return $this->tags;
  }

  public function addRoutePoint($long, $lat, $tags) {
    $this->routepoints[] = new RoutePoint($long, $lat, $tags);
  }

  public function getRoutePoints() {
    return $this->routepoints;
  }
}

?>
