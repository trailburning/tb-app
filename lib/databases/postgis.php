<?php 

namespace TB;

require_once 'iDatabase.php';
require_once 'ApiException.php';

use TB;

class Postgis 
  extends \PDO 
  implements \TB\iDatabase {


  function __construct($dsn, $username="", $password="", $driver_options=array()) {
    try {
      parent::__construct($dsn,$username,$password, $driver_options);
    }
    catch (PDOException $e) {
      throw (new ApiException("Failed to establish connection to Database", 500));
    }
  }


  function updateRouteCentroid($routeid) {
    $this->beginTransaction();
    $q = "UPDATE routes 
          SET center = (
              SELECT  ST_AsBinary(ST_Centroid(ST_MakeLine(rp.coords ORDER BY rp.pointnumber ASC)))
              FROM routepoints rp
              WHERE routes.id = rp.routeid )
          WHERE id=?;";
    $pq = $this->prepare($q);
    $success = $pq->execute(array($routeid));
    if (!$success) {
      $this->rollBack();
      throw (new ApiException("Failed to insert the track into the database - Problem calculating centroid", 500));
    }

    $this->commit();
  }

  function importGpxFile($path) {
    $this->beginTransaction();
    $q = "INSERT INTO gpxfiles (path) VALUES (?)";
    $pq = $this->prepare($q);
    $success = $pq->execute(array($path));
    if (!$success) {
      $this->rollBack();
      throw (new ApiException("Failed to insert GPX data into the database", 500));
    }

    $gpxfileid = intval($this->lastInsertId("gpxfiles_id_seq"));
    $this->commit();

    return $gpxfileid;
  }

  function importRoute($gpxfileid, $route) {
    $routeid = 0;
    $this->beginTransaction();

    $q = "INSERT INTO routes (name, gpxfileid) VALUES (?, ?)";
    $pq = $this->prepare($q);
    $success = $pq->execute(array("random", $gpxfileid));
    if (!$success) {
      $this->rollBack();
      throw (new ApiException("Failed to insert the route into the database", 500));
    }

    $routeid = intval($this->lastInsertId("routes_id_seq"));

    $q = "INSERT INTO routepoints (routeid, pointnumber, coords, tags) VALUES (?, ?, ?, ?)";
    $pq = $this->prepare($q);

    $routepts = $route->getRoutePoints();
    $pointnumber = 0;
    foreach ($routepts as $routepoint) {
      $pointnumber++;
      $rpcoords = $routepoint->getCoords();
      $rptags = $routepoint->getTags();
      $rpcoordswkt = "POINT($rpcoords[0] $rpcoords[1])";

      // Build hstore text from associative array
      $tags = "";
      $tagnum = 0;
      foreach ($rptags as $tagname => $tagvalue) {
        if ($tagnum++ > 0) $tags .= ",";
        $tags .= '"'.$tagname.'" => "'.$tagvalue.'"';
      }

      $success = $pq->execute(array(
        $routeid, 
        $pointnumber,
        $rpcoordswkt,
        $tags,
      ));
      if (!$success) {
        $this->rollBack();
        throw (new ApiException("Failed to insert routepoints into the database", 500));
      }
    }
    $this->commit();
    $this->updateRouteCentroid($routeid);

    return $routeid;
  }



  function exportRoute($routeid, $format) {
    $this->beginTransaction();
    $q = "SELECT r.id AS routeid, 
                 r.name AS name, 
                 ST_AsGeoJson(ST_MakeLine(rp.coords ORDER BY rp.pointnumber ASC)) AS line 
          FROM routes r, routepoints rp
          WHERE r.id=? AND rp.routeid=r.id
          GROUP BY r.id";
    $pq = $this->prepare($q);
    $success = $pq->execute(array($routeid));
    if (!$success) {
      throw (new ApiException("Failed to fetch route from Database", 500));
    }
    return json_encode($pq->fetchAll());
  }
}

?>
