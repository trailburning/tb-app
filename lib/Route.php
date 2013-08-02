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

  public function setCentroid($long, $lat) { $this->centroid[0] = $long; $this->centroid[1]=$lat; }
  public function getCentroid() { return $this->centroid; }
  public function setName($name) { $this->name = $name; }
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

    echo \DateTime::createFromFormat('U', $unixtimestamp)->Format('%c')."\n";
    echo \DateTime::createFromFormat('U', $this->routepoints[0]->tags['datetime'])->Format('%c')."\n";
    echo \DateTime::createFromFormat('U', end($this->routepoints)->tags['datetime'])->Format('%c')."\n";
    if ($unixtimestamp < $this->routepoints[0]->tags['datetime'] || $unixtimestamp > end($this->routepoints)->tags['datetime'])
      throw new \Exception("Datetime not in range");

    foreach ($this->routepoints as $rp) {
      if ($rp->tags['datetime'] > $unixtimestamp )
        return $rp; 
    }
  }

  public function toJSON() {
    $route = '{';
    $route .= '"name": "'.$this->name.'",';
    $route .= '"centroid": ['.$this->centroid[0].', '.$this->centroid[1].'],';
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
      $route .= '{"coords" : ['.$coords[0].','.$coords[1].'], "tags" : {';
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
