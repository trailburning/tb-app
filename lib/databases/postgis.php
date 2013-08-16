<?php 

namespace TB;

require_once 'iDatabase.php';
require_once 'Route.php';
require_once 'Picture.php';
require_once 'RoutePoint.php';
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
    $success = $pq->execute(array($route->getName(), $gpxfileid));
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
      $rpcoordswkt = 'POINT('.$rpcoords['long'].' '.$rpcoords['lat'].')';

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
    if (!$success) 
      throw (new ApiException("Failed to fetch route from Database", 500));
    
    if ($row = $pq->fetch(\PDO::FETCH_ASSOC)) {
      $route->setName($row['name']);
      $route->setBBox($row['bbox']);
      $c = explode(" ", substr(trim($row['centroid']),6,-1));
      $route->setCentroid($c[0], $c[1]); 
    } else {
      throw (new ApiException("Route does not exist", 404));
    }
    $this->commit();

    $this->beginTransaction();
    $q = "SELECT ST_AsText(rp.coords) AS rpcoords,
                 rp.tags as rptags
          FROM routepoints rp
          WHERE rp.routeid=?
          GROUP BY rp.pointnumber,rp.coords, rp.tags
          ORDER BY rp.pointnumber ASC
          ";
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

  public function deleteRoute($routeid) {
    $this->beginTransaction();
    $q = "DELETE FROM routes WHERE routes.id = ?";
    $pq = $this->prepare($q);
    $success = $pq->execute(array($routeid));
    if (!$success) 
      throw (new ApiException("Failed to delete route $routeid", 500));

    if ($pq->rowCount() < 1)
      throw (new ApiException("Failed to delete non existing route $routeid", 404));
  
    $this->commit();
  }

  public function getRouteMedia($routeid) {
    $this->beginTransaction();
    $q = "SELECT m.id AS id,
                 ST_AsText(m.coords) as coords, 
                 m.tags AS tags
          FROM media m, 
               routes_medias rm
          WHERE rm.mediaid = m.id AND rm.routeid=?";
    $pq = $this->prepare($q);
    $success = $pq->execute(array($routeid));
    if (!$success) 
      throw (new ApiException("Failed to retrieve pictures from the database", 500));
    
    $medias = array();
    while ($row = $pq->fetch(\PDO::FETCH_ASSOC)) {
      $pic = new Picture();
      $pic->setId($row['id']);
      $coords = explode(" ", substr(trim($row['coords']),6,-1)); //Strips POINT( and trailing )
      $pic->setCoords($coords[0], $coords[1]);
      $tags = json_decode('{' . str_replace('"=>"', '":"', $row['tags']) . '}', true);
      foreach ($tags as $tag => $v) {
        $pic->setTag($tag, $v);
      }
      $medias[] = $pic;
    }
    $this->commit();
    return $medias;
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
    $this->commit();
  }

  public function attachMediaToRoute($routeid, $media, $linear_position=0) {

    if ($linear_position == 0) {
      $this->beginTransaction();
      $q = "SELECT ST_Line_Locate_Point(
              ST_MakeLine(rp.coords ORDER BY rp.pointnumber ASC),
              ST_MakePoint(?,?)
            ) AS linear_position
            FROM routepoints as rp
            where rp.routeid=?
      ";
      $pq = $this->prepare($q);

      $success = $pq->execute(array(
        $media->coords['long'], 
        $media->coords['lat'], 
        $routeid
      ));
      if ($row = $pq->fetch(\PDO::FETCH_ASSOC))
        $linear_position = $row['linear_position'];
      $this->commit();
    }

    $this->beginTransaction();
    $q = "INSERT INTO routes_medias (routeid, mediaid, linear_position) VALUES (?, ?, ?)";
    $pq = $this->prepare($q);
    $success = $pq->execute(array(
      $routeid,
      $media->getId(), 
      $linear_position
    ));

    if (!$success) {
      throw (new ApiException("Failed to link media to route", 500));
    }
    $this->commit();
  }

  public function importPicture($routeid, $picture) {
    $this->beginTransaction();

    if (sizeof($picture->coords) < 2) {
      $picture->coords['long'] = 0;
      $picture->coords['lat'] = 0;
    }

    $pcoordswkt = 'POINT('.$picture->coords['long'].' '.$picture->coords['lat'].')';
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

    $pictureid = intval($this->lastInsertId("media_id_seq"));

    $this->commit();

    return $pictureid;
  }

  public function getTimezone($long, $lat) {
    $this->beginTransaction();
    $q = "SELECT tzid FROM tz_world_mp WHERE ST_Contains(geom, ST_MakePoint(?,?));";
    $pq = $this->prepare($q);
    $success = $pq->execute(array($long, $lat));
    if (!$success) 
      $r = null;

    if ($row = $pq->fetch(\PDO::FETCH_ASSOC))
      $r = $row['tzid'];
    else 
      $r = null;

    $this->commit();

    return $r;
  }
}

?>
