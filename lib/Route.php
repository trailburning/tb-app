<?php

namespace TB;

class Route {
  protected $name;
  protected $tags;
  public $routepoints;
  protected $bbox;
  protected $centroid; //[$long, $lat]

  public function __construct() {
    $this->routepoints = array();
    $this->tags = array();
  }

  public function setCentroid($long, $lat) { $this->centroid['long'] = $long; $this->centroid['lat']=$lat; }
  public function getCentroid() { return $this->centroid; }
  public function setName($name) { $this->name = $name; }
  public function getName() { return $this->name; }
  public function setBBox($bbox) { $this->bbox = $bbox; }
  public function setTag($key, $value) { $this->tags[$key] = $value; }
  public function getTags() { return $this->tags; }

  public function addRoutePoint($long, $lat, $tags) {
    $this->routepoints[] = new RoutePoint($long, $lat, $tags);
  }

  public function getRoutePoints() {
    return $this->routepoints;
  }

  public function getNearestPointBytime($unixtimestamp) {
    if (sizeof($this->routepoints) < 2)
      throw new \Exception("Route is less than 2 points.");

    if ($unixtimestamp < $this->routepoints[0]->tags['datetime'] || $unixtimestamp > end($this->routepoints)->tags['datetime'])
      throw new \TB\ApiException("One picture doesn't seem to have been taken during the trail", 400);

    foreach ($this->routepoints as $rp) {
      if ($rp->tags['datetime'] > $unixtimestamp )
        return $rp; 
    }
  }

  public function toJSON() {
    $route = '{';
    $route .= '"name": "'.$this->name.'",';
    $route .= '"centroid": ['.$this->centroid['long'].', '.$this->centroid['lat'].'],';
    $route .= '"bbox": "'.$this->bbox.'",';
    $route .= '"tags": [';
    $i=0;
    foreach ($this->tags as $tagname => $tagvalue) {
      if ($i++ != 0) $route.=',';
      $route .= '"'.$tagname.'": "'.$tagvalue.'"';
    }
    $route .= '],';
    $route .= '"routepoints" : [';
    $i=0;
    foreach ($this->routepoints as $rp) {
      if ($i++ != 0) $route.=',';
      $coords = $rp->getCoords();
      $route .= '{"coords" : ['.$coords['long'].','.$coords['lat'].'], "tags" : {';
      $rptags = $rp->getTags();
      $j=0;
      foreach ($rptags as $rptag => $rptagvalue) {
        if ($j++ != 0) $route.=',';
        $route .= '"'.$rptag.'" : "'.$rptagvalue.'"';
      }
      $route .= '}}';
    }
    $route .= ']}';

    return $route;
  }
}

?>
