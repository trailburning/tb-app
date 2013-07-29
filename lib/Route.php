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

class Route {
  protected $name;
  protected $tags;
  protected $routepoints;
  protected $centroid;

  public function __construct($tags = array()) {
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

  public function toJSON() {
    $route = '{';
    $route .= '"name": "'.$this->name.'",';
    $route .= '"centroid": "'.$this->centroid.'",';
    $route .= '"tags": [';
    foreach ($this->tags as $tagname => $tagvalue) {
      $route .= '"'.$tagname.'": "'.$tagvalue.'"';
    }
    $route .= '],';
    $route .= '"routepoints" =[';
    foreach ($this->routepoints as $rp) {
      $route .= '['.$rp->long.','.$rp->lat.']';
    }
    $route .= ']}';

    return $route;
  }
}

?>
