<?php 

namespace TB;

require_once 'iDatabase.php';
require_once 'Route.php';
require_once 'JpegMedia.php';
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

  private function updateRouteLength($routeid) {
    $this->beginTransaction();

    $q = "UPDATE routes 
          SET length = (
            SELECT ST_Length(ST_MakeLine(rp.coords ORDER BY rp.point_number ASC)::geography)
            FROM route_points AS rp 
            WHERE rp.route_id = routes.id
          )
          WHERE routes.id=?;";
    $pq = $this->prepare($q);
    $success = $pq->execute(array($routeid));
    if (!$success) {
      $this->rollBack();
      throw (new ApiException("Failed to insert the track into the database - Problem calculating length", 500));
    }

    $this->commit();
  }

  private function updateRouteCentroid($routeid) {
    $this->beginTransaction();
    $q = "UPDATE routes 
          SET centroid = (
              SELECT ST_SetSRID(ST_Centroid(ST_MakeLine(rp.coords ORDER BY rp.point_number ASC)), 4326)
              FROM route_points rp
              WHERE routes.id = rp.route_id )
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
    $q = "INSERT INTO gpx_files (path) VALUES (?)";
    $pq = $this->prepare($q);
    $success = $pq->execute(array($path));
    if (!$success) {
      $this->rollBack();
      throw (new ApiException("Failed to insert GPX data into the database", 500));
    }

    $gpxfileid = intval($this->lastInsertId("gpx_files_id_seq"));
    $this->commit();

    return $gpxfileid;
  }

  public function writeRoute($route) {
    $routeid = 0;
    $this->beginTransaction();

    $q = "INSERT INTO routes (name, gpx_file_id) VALUES (?, ?)";
    $pq = $this->prepare($q);
    $success = $pq->execute(array($route->getName(), $route->getGpxFileId()));
    if (!$success) {
      $this->rollBack();
      throw (new ApiException("Failed to insert the route into the database", 500));
    }

    $routeid = intval($this->lastInsertId("routes_id_seq"));

    $q = "INSERT INTO route_points (route_id, point_number, coords, tags) VALUES (?, ?, ST_SetSRID(ST_MakePoint(?, ?), 4326), ?)";
    $pq = $this->prepare($q);

    $routepts = $route->getRoutePoints();
    $pointnumber = 0;
    foreach ($routepts as $routepoint) {
      $pointnumber++;
      $rpcoords = $routepoint->getCoords();
      $rptags = $routepoint->getTags();
      $rpcoordswkt = 'ST_SetSRID(ST_MakePoint('.$rpcoords['long'].', '.$rpcoords['lat'].'), 4326)';

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
        $rpcoords['long'],
        $rpcoords['lat'], 
        $tags
      ));
      if (!$success) {
        print_r($pq->errorInfo());
        throw (new ApiException("Failed to insert routepoints into the database".$rpcoordswkt, 500));
      }
    }
    $this->commit();
    $this->updateRouteCentroid($routeid);
    $this->updateRouteLength($routeid);

    return $routeid;
  }

  public function readRoute($routeid) {
    $route = new Route();

    $this->beginTransaction();
    $q = "SELECT r.name AS name, 
                 r.length as length,
                 ST_AsText(ST_Centroid(ST_MakeLine(rp.coords ORDER BY rp.point_number ASC))) as centroid,
                 ST_AsText(Box2D(ST_MakeLine(rp.coords ORDER BY rp.point_number ASC))) as bbox
          FROM routes r, route_points rp
          WHERE r.id=? AND rp.route_id=r.id
          GROUP BY name, length ";
    $pq = $this->prepare($q);
    $success = $pq->execute(array($routeid));
    if (!$success) 
      throw (new ApiException("Failed to fetch route from Database", 500));
    
    if ($row = $pq->fetch(\PDO::FETCH_ASSOC)) {
      $route->setName($row['name']);
      $route->setBBox($row['bbox']);
      $route->setLength($row['length']);
      $c = explode(" ", substr(trim($row['centroid']),6,-1));
      $route->setCentroid($c[0], $c[1]); 
    } else {
      throw (new ApiException("Route does not exist", 404));
    }
    $this->commit();

    $this->beginTransaction();
    $q = "SELECT ST_AsText(rp.coords) AS rpcoords,
                 rp.tags as rptags
          FROM route_points rp
          WHERE rp.route_id=?
          GROUP BY rp.point_number, rp.coords, rp.tags
          ORDER BY rp.point_number ASC
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

  public function readRouteAsJSON($routeid, $format) {
    $this->beginTransaction();
    $q = "SELECT r.id AS route_id,
                 r.name AS name,
                 ST_AsGeoJson(ST_MakeLine(rp.coords ORDER BY rp.point_number ASC)) AS line 
          FROM routes r, route_points rp
          WHERE r.id=? AND rp.route_id=r.id
          GROUP BY r.id";
    $pq = $this->prepare($q);
    $success = $pq->execute(array($routeid));
    if (!$success) {
      throw (new ApiException("Failed to fetch route from Database", 500));
    }
    return json_encode($pq->fetchAll());
    $this->commit();
  }


  public function getRouteMedia($routeid) {
    $this->beginTransaction();
    $q = "SELECT mv.media_id AS id,
                 ST_AsText(m.coords) AS coords,
                 m.tags as tags,
                 mv.path AS path,
                 mv.version_size AS size
          FROM  medias m,
                route_medias rm, 
                media_versions mv
          WHERE m.id = rm.media_id
            AND rm.media_id = mv.media_id
            AND rm.route_id=?
          GROUP BY mv.media_id, mv.path, mv.media_size, m.coords, m.tags;";
    $pq = $this->prepare($q);
    $success = $pq->execute(array($routeid));
    if (!$success) 
      throw (new ApiException("Failed to retrieve pictures from the database", 500));
    
    $medias = array();
    while ($row = $pq->fetch(\PDO::FETCH_ASSOC)) {
      if (!isset($medias[$row['id']])) {
        $pic = new JpegMedia();
        $pic->setId($row['id']);
        $coords = explode(" ", substr(trim($row['coords']),6,-1)); //Strips POINT( and trailing )
        $pic->setCoords($coords[0], $coords[1]);
        $tags = json_decode('{' . str_replace('"=>"', '":"', $row['tags']) . '}', true);
        foreach ($tags as $tag => $v) {
          $pic->setTag($tag, $v);
        }
        $pic->addVersion($row['size'], $row['path']);;
        $medias[$row['id']] = $pic;
      }
      else {
        $medias[$row['id']]->addVersion($row['size'], $row['path']);;
      }
    }
    $this->commit();
    return $medias;
  }

  public function attachMediaToRoute($routeid, $media, $linear_position=0) {

    if ($linear_position == 0) {
      $this->beginTransaction();
      $q = "SELECT ST_Line_Locate_Point(
              ST_MakeLine(rp.coords ORDER BY rp.point_number ASC),
              ST_MakePoint(?,?)
            ) AS linear_position
            FROM route_points as rp
            where rp.route_id=?
      ";
      $pq = $this->prepare($q);

      $coords = $media->getCoords();

      $success = $pq->execute(array(
        $coords['long'], 
        $coords['lat'], 
        $routeid
      ));
      if ($row = $pq->fetch(\PDO::FETCH_ASSOC))
        $linear_position = $row['linear_position'];
      $this->commit();
    }

    $this->beginTransaction();
    $q = "INSERT INTO route_medias (route_id, media_id, linear_position) VALUES (?, ?, ?)";
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

  public function importPicture($picture) {
    $this->beginTransaction();

    $coords = $picture->getCoords();

    if (sizeof($coords) < 2) {
      $coords['long'] = 0;
      $coords['lat'] = 0;
    }

    // Build hstore text from associative array
    $tags = "";
    $tagnum = 0;
    $picture_tags = $picture->getTags();
    foreach ($picture_tags as $tagname => $tagvalue) {
      if ($tagnum++ > 0) $tags .= ",";
      $tags .= '"'.$tagname.'" => "'.$tagvalue.'"';
    }

    $q = "INSERT INTO medias (coords, tags) VALUES (ST_SetSRID(ST_MakePoint(?, ?), 4326), ?)";
    $pq = $this->prepare($q);
    $success = $pq->execute(array(
      $coords['long'],
      $coords['lat'],
      $tags
    ));
    if (!$success) {
      throw (new ApiException("Failed to insert media in db", 500));
    }

    $pictureid = intval($this->lastInsertId("medias_id_seq"));

    $q = "INSERT INTO media_versions (media_id, version_size, path) VALUES (?,?,?)";
    $pq = $this->prepare($q);
    $success = $pq->execute(array(
      $pictureid,
      0, // ORIGINAL
      'trailburning-media/'.sha1_file($picture->getTmpPath()).'.jpg'
    ));
    if (!$success) {
      throw (new ApiException("Failed to upload version of media", 500));
    }
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
