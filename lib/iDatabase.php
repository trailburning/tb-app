<?php 

namespace TB;

interface iDatabase {

  public function __construct($dsn, $username="", $password="", $driver_options=array());
  public function writeRoute($gpxfileid, $route);
  public function readRoute($routeid);
}

?>
