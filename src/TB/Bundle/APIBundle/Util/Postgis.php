<?php 

namespace TB\Bundle\APIBundle\Util;

use TB\Bundle\FrontendBundle\Entity\Route;
use TB\Bundle\FrontendBundle\Entity\RouteType;
use TB\Bundle\FrontendBundle\Entity\RouteCategory;
use TB\Bundle\FrontendBundle\Entity\RoutePoint;
use TB\Bundle\FrontendBundle\Entity\Media;
use TB\Bundle\FrontendBundle\Entity\UserProfile;
use TB\Bundle\FrontendBundle\Entity\BrandProfile;
use CrEOF\Spatial\PHP\Types\Geometry\Point;
use TB\Bundle\APIBundle\Util;

class Postgis extends \PDO
{
    
    public function __construct($host, $port, $database, $user, $password, $driver_options=array()) 
    {
        $dsn = 'pgsql:host='.$host.';port='.$port.';dbname='.$database;
        
        try {
            parent::__construct($dsn, $user, $password, $driver_options);
        }
        catch (PDOException $e) {
            throw (new ApiException('Failed to establish connection to Database', 500));
        }
    }

    private function updateRouteLength($route_id) 
    {
        $this->beginTransaction();

        $q = "UPDATE routes 
              SET length = (
                SELECT ST_Length(ST_MakeLine(rp.coords ORDER BY rp.point_number ASC)::geography)
                FROM route_points AS rp 
                WHERE rp.route_id = routes.id
              )
              WHERE routes.id=?;";
        $pq = $this->prepare($q);
        $success = $pq->execute(array($route_id));
        if (!$success) {
            $this->rollBack();
            throw (new ApiException('Failed to insert the track into the database - Problem calculating length', 500));
        }

        $this->commit();
    }

    private function updateRouteCentroid($route_id) 
    {
        $this->beginTransaction();
        $q = "UPDATE routes 
        SET centroid = (
        SELECT ST_SetSRID(ST_Centroid(ST_MakeLine(rp.coords ORDER BY rp.point_number ASC)), 4326)
        FROM route_points rp
        WHERE routes.id = rp.route_id )
        WHERE id=?;";
        $pq = $this->prepare($q);
        $success = $pq->execute(array($route_id));
        if (!$success) {
            $this->rollBack();
            throw (new ApiException('Failed to insert the track into the database - Problem calculating centroid', 500));
        }

        $this->commit();
    }

    public function importGpxFile($path) 
    {
        $this->beginTransaction();
        $q = "INSERT INTO gpx_files (path) VALUES (?)";
        $pq = $this->prepare($q);
        $success = $pq->execute(array($path));
        if (!$success) {
            $this->rollBack();
            throw (new ApiException('Failed to insert GPX data into the database', 500));
        }

        $gpxfileid = intval($this->lastInsertId("gpx_files_id_seq"));
        $this->commit();

        return $gpxfileid;
    }

    public function writeRoute($route) 
    {
        $route_id = 0;
        $route->calculateAscentDescent();
        $tags = self::hstoreFromMap($route->getTags());

        $this->beginTransaction();
        $q = 'INSERT INTO routes (name, gpx_file_id, tags, user_id, region) VALUES (?, ?, ?, ?, ?)';
        $pq = $this->prepare($q);
        $success = $pq->execute(array($route->getName(), $route->getGpxFileId(), $tags, $route->getUserId(), $route->getRegion()));
        if (!$success) {
            $this->rollBack();
            throw (new ApiException("Failed to insert the route into the database", 500));
        }

        $route_id = intval($this->lastInsertId("routes_id_seq"));

        $q = "INSERT INTO route_points (route_id, point_number, coords, tags) VALUES (?, ?, ST_SetSRID(ST_MakePoint(?, ?), 4326), ?)";
        $pq = $this->prepare($q);

        $routepts = $route->getRoutePoints();
        $pointnumber = 0;
        foreach ($routepts as $routepoint) {
            $pointnumber++;
            $rpcoords = $routepoint->getCoords();
            $rptags = $routepoint->getTags();
            $rpcoordswkt = 'ST_SetSRID(ST_MakePoint('.$rpcoords->getLongitude().', '.$rpcoords->getLatitude().'), 4326)';

            $tags = self::hstoreFromMap($rptags);

            $success = $pq->execute(array(
            $route_id, 
            $pointnumber,
            $rpcoords->getLongitude(),
            $rpcoords->getLatitude(), 
            $tags
            ));
            if (!$success) {
                $this->rollBack();
                throw (new ApiException("Failed to insert routepoints into the database".$rpcoordswkt, 500));
            }
        }
        $this->commit();
        $this->updateRouteCentroid($route_id);
        $this->updateRouteLength($route_id);

        return $route_id;
    }

    public function readRoute($route_id) 
    {
        $route = new Route();

        $this->beginTransaction();
        $q = "SELECT r.name AS name, 
                     r.slug AS slug,
                     r.region AS region, 
                     r.length as length,
                     r.tags as rtags,
                     r.about,
                     ST_AsText(ST_Centroid(ST_MakeLine(rp.coords ORDER BY rp.point_number ASC))) as centroid,
                     ST_AsText(Box2D(ST_MakeLine(rp.coords ORDER BY rp.point_number ASC))) as bbox,
                     rt.id AS rt_id,
                     rt.name AS rt_name, 
                     rc.id AS rc_id,
                     rc.name AS rc_name
              FROM routes r
              INNER JOIN route_points rp ON r.id=rp.route_id
              LEFT JOIN route_type rt ON r.route_type_id=rt.id
              LEFT JOIN route_category rc ON r.route_category_id=rc.id
              WHERE r.id=?
              GROUP BY r.id, length, r.tags, r.slug, r.region, r.about, rt.id, rc.id";
        $pq = $this->prepare($q);
        $success = $pq->execute(array($route_id));
        if (!$success) {
            $this->rollBack();
            throw (new ApiException("Failed to fetch route from Database", 500));
        }
        $this->commit();

        if ($row = $pq->fetch(\PDO::FETCH_ASSOC)) {
            $route->setName($row['name']);
            $route->setSlug($row['slug']);
            $route->setRegion($row['region']);
            $route->setBBox($row['bbox']);
            $route->setLength($row['length']);
            $route->setAbout($row['about']);
            $c = explode(" ", substr(trim($row['centroid']),6,-1));
            $route->setCentroid(new Point($c[0], $c[1], 4326)); 
            $tags = json_decode('{' . str_replace('"=>"', '":"', $row['rtags']) . '}', true);
            $route->setTags($tags);
            if ($row['rc_name'] != '') {
                $routeCategory = new RouteCategory();
                $routeCategory->setId($row['rc_id']);
                $routeCategory->setName($row['rc_name']);
                $route->setRouteCategory($routeCategory);
            }
            if ($row['rt_name'] != '') {
                $routeType = new RouteType();
                $routeType->setId($row['rt_id']);
                $routeType->setName($row['rt_name']);
                $route->setRouteType($routeType);
            }
        } else {
            throw (new ApiException(sprintf('Route with id "%s" does not exist', $route_id), 404));
        }

        $this->beginTransaction();
        $q = "SELECT ST_AsText(rp.coords) AS rpcoords,
                     rp.tags as rptags
              FROM route_points rp
              WHERE rp.route_id=?
              GROUP BY rp.point_number, rp.coords, rp.tags
              ORDER BY rp.point_number ASC
              ";
        $pq = $this->prepare($q);
        $success = $pq->execute(array($route_id));
        if (!$success) {
            $this->rollBack();
            throw (new ApiException('Failed to fetch route from Database', 500));
        }
        $this->commit();

        while ($row = $pq->fetch(\PDO::FETCH_ASSOC)) {
            $coords = explode(" ", substr(trim($row['rpcoords']),6,-1)); //Strips POINT( and trailing )
            $rp = new RoutePoint();
            $rp->setCoords(new Point($coords[0], $coords[1], 4326));
            $tags = json_decode('{' . str_replace('"=>"', '":"', $row['rptags']) . '}', true);
            $rp->setTags($tags);
            $route->addRoutePoint($rp);
        }

        return $route;
    }
    
    public function readRoutes($user_id, $count = null, $route_type_id = null, $route_category_id = null, $publish = null) 
    {
        $q = 'SELECT r.id, r.name, r.slug, r.region, r.length, ST_X(r.centroid) AS long, ST_Y(r.centroid) AS lat, r.tags, rt.id AS rt_id, rt.name AS rt_name, rc.id AS rc_id, rc.name AS rc_name, r.about
              FROM routes r
              LEFT JOIN route_type rt ON r.route_type_id=rt.id
              LEFT JOIN route_category rc ON r.route_category_id=rc.id
              WHERE r.user_id=:user_id
              AND r.slug IS NOT NULL';
        if ($route_type_id !== null) {
            $q  .= ' AND r.route_type_id=:route_type_id';
        }      
        if ($route_category_id !== null) {
            $q  .= ' AND r.route_category_id=:route_category_id';
        }
        if ($publish !== null) {
            $q .= ' AND publish=:publish';
        }
        $q.= ' GROUP BY r.id, rt.id, rc.id ';
        if ($count !== null) {
            $q .= ' LIMIT :count';
        }
        
        $pq = $this->prepare($q);
        $pq->bindParam('user_id', $user_id, \PDO::PARAM_INT);
        if ($count !== null) {
            $pq->bindParam('count', $count, \PDO::PARAM_INT);
        }
        if ($route_type_id !== null) {
            $pq->bindParam('route_type_id', $route_type_id, \PDO::PARAM_INT);
        }
        if ($route_category_id !== null) {
            $pq->bindParam('route_category_id', $route_category_id, \PDO::PARAM_INT);
        }     
        if ($publish !== null) {
            $publish = ($publish === true) ? 'true' : 'false';
            $pq->bindParam('publish', $publish, \PDO::PARAM_INT);
        }
        
        $success = $pq->execute();
        if (!$success) {
            throw (new ApiException('Failed to fetch route from Database', 500));
        }

        $routes = array();
        
        while ($row = $pq->fetch(\PDO::FETCH_ASSOC)) {
            
            $route = new Route();
            $route->setId($row['id']);
            $route->setName($row['name']);
            $route->setSlug($row['slug']);
            $route->setRegion($row['region']);
            $route->setLength($row['length']);
            $route->setCentroid(new Point($row['long'], $row['lat'], 4326)); 
            $route->setAbout($row['about']);
            $tags = json_decode('{' . str_replace('"=>"', '":"', $row['tags']) . '}', true);
            $route->setTags($tags);
            if ($row['rc_name'] != '') {
                $routeCategory = new RouteCategory();
                $routeCategory->setId($row['rc_id']);
                $routeCategory->setName($row['rc_name']);
                $route->setRouteCategory($routeCategory);
            }
            if ($row['rt_name'] != '') {
                $routeType = new RouteType();
                $routeType->setId($row['rt_id']);
                $routeType->setName($row['rt_name']);
                $route->setRouteType($routeType);
            }
            $media = $this->getRouteMedia($row['id'], 1);
            if (count($media) > 0) {
                $route->setMedia(array_shift($media));
            }
            
            $routes[] = $route;
        }
        
        return $routes;
    }
    
    public function searchRoutes($limit = 10, $offset = 0, &$count = 0) 
    {
        $q = 'SELECT COUNT(r.id) AS count
              FROM routes r
              INNER JOIN fos_user u ON r.user_id=u.id
              WHERE r.slug IS NOT NULL
              AND r.publish = true';
        $pq = $this->prepare($q);
        $success = $pq->execute();
        if (!$success) {
            throw (new ApiException('Failed to fetch route from Database', 500));
        }

        $routes = array();
        if ($row = $pq->fetch(\PDO::FETCH_ASSOC)) {
            $count = $row['count'];
            $q = 'SELECT r.id, r.name, r.slug, r.region, r.length, ST_X(r.centroid) AS long, ST_Y(r.centroid) AS lat, r.tags, rt.id AS rt_id, rt.name AS rt_name, rc.id AS rc_id, rc.name AS rc_name, r.about, u.id AS user_id, u.name AS user_name, u.discr, u.first_name, u.last_name, u.display_name, u.avatar, u.avatar_gravatar
                  FROM routes r
                  INNER JOIN fos_user u ON r.user_id=u.id
                  LEFT JOIN route_type rt ON r.route_type_id=rt.id
                  LEFT JOIN route_category rc ON r.route_category_id=rc.id
                  WHERE r.slug IS NOT NULL
                  AND r.publish = true
                  GROUP BY r.id, rt.id, rc.id , u.id
                  ORDER BY r.id DESC
                  LIMIT :limit OFFSET :offset';
            $pq = $this->prepare($q);
            $pq->bindParam('limit', $limit, \PDO::PARAM_INT);
            $pq->bindParam('offset', $offset, \PDO::PARAM_INT);

            $success = $pq->execute();
            if (!$success) {
                throw (new ApiException('Failed to fetch route from Database', 500));
            }

            while ($row = $pq->fetch(\PDO::FETCH_ASSOC)) {
    
                $route = new Route();
                $route->setId($row['id']);
                $route->setName($row['name']);
                $route->setSlug($row['slug']);
                $route->setRegion($row['region']);
                $route->setLength($row['length']);
                $route->setCentroid(new Point($row['long'], $row['lat'], 4326)); 
                $route->setAbout($row['about']);
                $tags = json_decode('{' . str_replace('"=>"', '":"', $row['tags']) . '}', true);
                $route->setTags($tags);
                if ($row['rc_name'] != '') {
                    $routeCategory = new RouteCategory();
                    $routeCategory->setName($row['rc_name']);
                    $route->setRouteCategory($routeCategory);
                }
                if ($row['rt_name'] != '') {
                    $routeType = new RouteType();
                    $routeType->setName($row['rt_name']);
                    $route->setRouteType($routeType);
                }
                if ($row['discr'] == 'user') {
                    $user = new UserProfile();
                    $user->setName($row['user_name']);
                    $user->setFirstName($row['first_name']);
                    $user->setLastName($row['last_name']);
                    $user->setAvatar($row['avatar']);
                    $user->setAvatarGravatar($row['avatar_gravatar']);
                } elseif ($row['discr'] == 'brand') {
                    $user = new BrandProfile();
                    $user->setName($row['user_name']);
                    $user->setDisplayName($row['display_name']);
                    $user->setAvatar($row['avatar']);
                    $user->setAvatarGravatar($row['avatar_gravatar']);
                }
                $route->setUser($user);
                $media = $this->getRouteMedia($row['id'], 1);
                if (count($media) > 0) {
                    $route->setMedia(array_shift($media));
                }
    
                $routes[] = $route;
            }
        }
        
        return $routes;
    }

    public function deleteRoute($route_id) 
    {
        $this->beginTransaction();
        $q = "DELETE FROM routes WHERE routes.id = ?";
        $pq = $this->prepare($q);
        $success = $pq->execute(array($route_id));
        if (!$success) {
            $this->rollBack();
            throw (new ApiException(sprintf('Failed to delete route %s', $route_id), 500));
        }
        if ($pq->rowCount() < 1) {
            throw (new ApiException(sprintf('Failed to delete non existing route with id "%s"', $route_id), 404));
        }
        $this->commit();
    }

    public function readRouteAsJSON($route_id, $format) 
    {
        $this->beginTransaction();
        $q = "SELECT r.id AS route_id,
                     r.name AS name,
                     ST_AsGeoJson(ST_MakeLine(rp.coords ORDER BY rp.point_number ASC)) AS line 
              FROM routes r, route_points rp
              WHERE r.id=? AND rp.route_id=r.id
              GROUP BY r.id";
        $pq = $this->prepare($q);
        $success = $pq->execute(array($route_id));
        if (!$success) {
            throw (new ApiException("Failed to fetch route from Database", 500));
        }
        return json_encode($pq->fetchAll());
        $this->commit();
    }


    public function getRouteMedia($route_id, $count = null) 
    {
        $q = "SELECT id, ST_AsText(m.coords) AS coords, tags, filename, path
              FROM medias m
              WHERE m.route_id=:route_id
              ORDER BY m.tags->'datetime' ASC   
              LIMIT :count"; 

        $pq = $this->prepare($q);
        $pq->bindParam('route_id', $route_id, \PDO::PARAM_INT);
        $pq->bindParam('count', $count, \PDO::PARAM_INT);
        
        $success = $pq->execute();
        if (!$success) {
            throw (new ApiException("Failed to retrieve pictures from the database", 500));
        }

        $medias = array();
        while ($row = $pq->fetch(\PDO::FETCH_ASSOC)) {
            $media = new Media();
            $media->setId($row['id']);
            $media->setPath($row['path']);
            $coords = explode(" ", substr(trim($row['coords']), 6, -1)); 
            $media->setCoords(new Point($coords[0], $coords[1], 4326));
            $media->setFilename($row['filename']);
            $tags = json_decode('{' . str_replace('"=>"', '":"', $row['tags']) . '}', true);
            $media->setTags($tags);
            $medias[$row['id']] = $media;    
        }
        
        return $medias;
    }

    public function attachMediaToRoute($route_id, $media, $linear_position=0) 
    {
        if ($linear_position == 0) {
            $this->beginTransaction();
            $q = "SELECT ST_Line_Locate_Point(
                  ST_MakeLine(rp.coords ORDER BY rp.point_number ASC),
                  ST_SetSRID(ST_MakePoint(?,?), 4326)
                ) AS linear_position
                FROM route_points as rp
                where rp.route_id=?
            ";
            $pq = $this->prepare($q);

            $coords = $media->getCoords();
            $success = $pq->execute(array(
                $coords['long'], 
                $coords['lat'], 
                $route_id
            ));

            if (!$success) {
                $this->rollBack();
                throw (new ApiException("Failed to locate image on route", 500));
            }

            if ($row = $pq->fetch(\PDO::FETCH_ASSOC)) {
                $linear_position = $row['linear_position'];
            }

            $this->commit();
        }

        $this->beginTransaction();
        $q = "INSERT INTO route_medias (route_id, media_id, linear_position) VALUES (?, ?, ?)";
        $pq = $this->prepare($q);
        $success = $pq->execute(array(
            $route_id,
            $media->getId(), 
            $linear_position
        ));

        if (!$success) {
            $this->rollBack();
            throw (new ApiException("Failed to link media to route", 500));
        }
        $this->commit();
    }

    public function deleteMedia($media_id) 
    {
        $this->beginTransaction();
        $q = "DELETE FROM medias WHERE medias.id = ?";
        $pq = $this->prepare($q);
        $success = $pq->execute(array($media_id));
        if (!$success) {
            $this->rollBack();
            throw (new ApiException("Failed to delete media $media_id", 500));
        }
        
        if ($pq->rowCount() < 1) {
            throw (new ApiException("Failed to delete non existing media $route_id", 404));
        }
        
        $this->commit();
    }

    public function importPicture($picture) 
    {
        $this->beginTransaction();

        $coords = $picture->getCoords();

        if (sizeof($coords) < 2) {
            $coords['long'] = 0;
            $coords['lat'] = 0;
        }

        $tags = self::hstoreFromMap($picture->getTags());

        $q = "INSERT INTO medias (coords, tags) VALUES (ST_SetSRID(ST_MakePoint(?, ?), 4326), ?)";
        $pq = $this->prepare($q);
        $success = $pq->execute(array(
            $coords['long'],
            $coords['lat'],
            $tags
        ));
        if (!$success) {
            $this->rollBack();
            throw (new ApiException('Failed to insert media in db', 500));
        }

        $picture_id = intval($this->lastInsertId("medias_id_seq"));
        $picture_s3_path = 'trailburning-media/'.sha1_file($picture->getTmpPath()).'.jpg';

        $q = "INSERT INTO media_versions (media_id, version_size, path) VALUES (?,?,?)";
        $pq = $this->prepare($q);
        $success = $pq->execute(array(
            $picture_id,
            0, // ORIGINAL
            $picture_s3_path
        ));
        if (!$success) {
            $this->rollBack();
            throw (new ApiException("Failed to upload version of media", 500));
        }
        $this->commit();

        $picture->setId($picture_id);
        $picture->addVersion(0, $picture_s3_path);

        return $picture_id;
    }

    public function getTimezone($long, $lat) 
    {
        $this->beginTransaction();
        $q = "SELECT tzid FROM tz_world_mp WHERE ST_Contains(geom, ST_MakePoint(?,?));";
        $pq = $this->prepare($q);
        $success = $pq->execute(array($long, $lat));
        if (!$success) {
            $this->rollBack();
            $r = null;
        }

        if ($row = $pq->fetch(\PDO::FETCH_ASSOC)) {
            $r = $row['tzid'];
        } else {
            $r = null;
        }

        $this->commit();

        return $r;
    }

    protected static function hstoreFromMap($map) 
    {
        $hstore = "";
        $n = 0;
        foreach ($map as $k => $v) {
            if ($n++ > 0) { 
                $hstore .= ",";
            }
            $hstore .= '"'.$k.'" => "'.$v.'"';
        }
        return $hstore;
    }
    
}

