<?php

namespace TB;

class Route {
  protected $gpx_file_id; // ID of gpx file this route has been imported from (optional)
  protected $name;
  protected $tags;
  public $route_points;
  protected $bbox;
  protected $centroid; //[$long, $lat]
  protected $length;

  public function __construct() {
    $this->route_points = array();
    $this->tags = array();
  }

  public function setGpxFileId($id) { $this->gpx_file_id = $id; }
  public function getGpxFileId() { return $this->gpx_file_id; }
  public function setCentroid($long, $lat) { $this->centroid['long'] = $long; $this->centroid['lat']=$lat; }
  public function getCentroid() { return $this->centroid; }
  public function setLength($length) { $this->length = $length; }
  public function getLength() { return $this->length; }
  public function setName($name) { $this->name = $name; }
  public function getName() { return $this->name; }
  public function setBBox($bbox) { $this->bbox = $bbox; }
  public function setTag($key, $value) { $this->tags[$key] = $value; }
  public function getTags() { return $this->tags; }

  public function addRoutePoint($long, $lat, $tags) {
    $this->route_points[] = new RoutePoint($long, $lat, $tags);
  }

  public function getRoutePoints() {
    return $this->route_points;
  }

  public function calculateAscentDescent() {
    $last_rp_altitude = 0;
    $asc = 0;
    $desc = 0;

    foreach ($this->route_points as $rp) {
      $rp_altitude = $rp->getTag('altitude');
      
      if ($last_rp_altitude != 0) {
        if ($rp_altitude > $last_rp_altitude)
          $asc += $rp_altitude-$last_rp_altitude;
        else
          $desc += $last_rp_altitude-$rp_altitude;
      }

      $last_rp_altitude = $rp_altitude;
    }

    $this->tags['ascent'] = $asc;
    $this->tags['descent'] = $desc;

    return 0;
  }

  public function getNearestPointBytime($unixtimestamp) {
    if (sizeof($this->route_points) < 2)
      throw new \Exception("Route is less than 2 points.");

    if ($unixtimestamp < $this->route_points[0]->tags['datetime'])
      return $this->route_points[0];
    else if ($unixtimestamp > end($this->route_points)->tags['datetime'])
      return end($this->route_points);
    else {
      foreach ($this->route_points as $rp) {
        if ($rp->tags['datetime'] > $unixtimestamp )
          return $rp; 
      }
    }
  }

  public function toJSON() {
    $route = '{';
    $route .= '"name": "'.$this->name.'",';
    $route .= '"length": "'.$this->length.'",';
    $route .= '"centroid": ['.$this->centroid['long'].', '.$this->centroid['lat'].'],';
    $route .= '"bbox": "'.$this->bbox.'",';
    $route .= '"tags": {';
    $i=0;
    foreach ($this->tags as $tag_name => $tag_value) {
      if ($i++ != 0) $route.=',';
      $route .= '"'.$tag_name.'": "'.$tag_value.'"';
    }
    $route .= '},';
    $route .= '"route_points" : [';
    $i=0;
    foreach ($this->route_points as $rp) {
      if ($i++ != 0) $route.=',';
      $coords = $rp->getCoords();
      $route .= '{"coords" : ['.$coords['long'].','.$coords['lat'].'], "tags" : {';
      $rp_tags = $rp->getTags();
      $j=0;
      foreach ($rp_tags as $rp_tag => $rp_tag_value) {
        if ($j++ != 0) $route.=',';
        $route .= '"'.$rp_tag.'" : "'.$rp_tag_value.'"';
      }
      $route .= '}}';
    }
    $route .= ']}';

    return $route;
  }
}

?>
