<?php 

namespace TB;

require_once 'iDatabase.php';
require_once 'Route.php';
require_once 'ApiException.php';

use TB;

class Postgis 
  extends \PDO 
  implements \TB\iDatabase {

  public function __construct($dsn, $username="", $password="", $driver_options=array()) {
    try {
      parent::__construct($dsn,$username,$password, $driver_options);
    }
    catch (PDOException $e) {
      throw (new ApiException("Failed to establish connection to Database", 500));
    }
  }

  private function updateRouteCentroid($routeid) {
    $this->beginTransaction();
    $q = "UPDATE routes 
          SET centroid = (
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

  public function importGpxFile($path) {
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

  public function writeRoute($gpxfileid, $route) {
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


  public function readRoute($routeid) {
    $route = new Route();

    $this->beginTransaction();
    $q = "SELECT r.name AS name,
                 ST_AsText(ST_Centroid(ST_MakeLine(rp.coords ORDER BY rp.pointnumber ASC))) as centroid,
                 ST_AsText(Box2D(ST_MakeLine(rp.coords ORDER BY rp.pointnumber ASC))) as bbox
          FROM routes r, routepoints rp
          WHERE r.id=? AND rp.routeid=r.id
          GROUP BY name ";
    $pq = $this->prepare($q);
    $success = $pq->execute(array($routeid));
    if (!$success) {
      throw (new ApiException("Failed to fetch route from Database", 500));
    }
    if ($row = $pq->fetch(\PDO::FETCH_ASSOC)) {
      $route->setName($row['name']);
      $route->setBBox($row['bbox']);
      $route->setCentroid($row['centroid']);
    }
    $this->commit();

    $this->beginTransaction();
    $q = "SELECT ST_AsText(rp.coords) AS rpcoords,
                 rp.tags as rptags
          FROM routepoints rp
          WHERE rp.routeid=?
          GROUP BY rp.coords, rp.tags";
    $pq = $this->prepare($q);
    $success = $pq->execute(array($routeid));
    if (!$success) {
      throw (new ApiException("Failed to fetch route from Database", 500));
    }
    
    while ($row = $pq->fetch(\PDO::FETCH_ASSOC)) {
      $coords = explode(" ", substr(trim($row['rpcoords']),6,-1)); //Strips POINT( and trailing )
      $route->addRoutePoint(
        $coords[0], 
        $coords[1], 
        json_decode('{' . str_replace('"=>"', '":"', $row['rptags']) . '}', true)
      );
    }

    $this->commit();
    return $route;
  }

  public function readRouteAsJSON($routeid, $format) {
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

  public function attachMediaToRoute($routeid, $mediaid, $pointnumber=0) {
    $q = "INSERT INTO routes_medias (routeid, mediaid, pointnumber) VALUES (?, ?, ?)";
    $pq = $this->prepare($q);
    $success = $pq->execute(array(
      $routeid,
      $mediaid, 
      $pointnumber
    ));

    if (!$success) {
      throw (new ApiException("Failed to link media to route", 500));
    }
    $this->commit();
  }

  public function importPicture($routeid, $picture) {
    $this->beginTransaction();

    if ($picture->long == NULL || $picture->lat == NULL) {
      $picture->long = 0;
      $picture->lat = 0;
    }

    $pcoordswkt = "POINT($picture->long $picture->lat)";
    // Build hstore text from associative array
    $tags = "";
    $tagnum = 0;
    foreach ($picture->tags as $tagname => $tagvalue) {
      if ($tagnum++ > 0) $tags .= ",";
      $tags .= '"'.$tagname.'" => "'.$tagvalue.'"';
    }

    $q = "INSERT INTO media (coords, tags) VALUES (?, ?)";
    $pq = $this->prepare($q);
    $success = $pq->execute(array(
      $pcoordswkt, 
      $tags
    ));
    if (!$success) {
      throw (new ApiException("Failed to insert media into database", 500));
    }

    $pictureid = intval($this->lastInsertId("media_id_seq"));

    $this->attachMediaToRoute($routeid, $pictureid);

    return $pictureid;
  }
}

?>
