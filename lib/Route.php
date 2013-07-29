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
  protected $bbox;
  protected $centroid;

  public function __construct() {
    $this->routepoints = array();
    $this->tags = array();
  }

  public function setCentroid($centroid) {
    $this->centroid = $centroid;
  }

  public function setName($name) {
    $this->name = $name;
  }

  public function setBBox($bbox) {
    $this->bbox = $bbox;
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
    $route .= '"bbox": "'.$this->bbox.'",';
    $route .= '"tags": [';
    $i=0;
    foreach ($this->tags as $tagname => $tagvalue) {
      if ($i++ != 0) $route.=',';
      $route .= '"'.$tagname.'": "'.$tagvalue.'"';
    }
    $route .= '],';
    $route .= '"routepoints" =[';
    $i=0;
    foreach ($this->routepoints as $rp) {
      if ($i++ != 0) $route.=',';
      $coords = $rp->getCoords();
      $route .= '{"coords" = ['.$coords[0].','.$coords[1].'], "tags" = {';
      $rptags = $rp->getTags();
      $j=0;
      foreach ($rptags as $rptag => $rptagvalue) {
        if ($j++ != 0) $route.=',';
        $route .= '"'.$rptag.'" => "'.$rptagvalue.'"';
      }
      $route .= '}}';
    }
    $route .= ']}';

    return $route;
  }
}

?>
