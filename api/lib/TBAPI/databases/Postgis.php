<?php 

namespace TB;

require_once 'iDatabase.php';
require_once 'Route.php';
require_once 'JpegMedia.php';
require_once 'RoutePoint.php';
require_once 'ApiException.php';

use TB;

class Postgis extends \PDO implements \TB\iDatabase 
{

	public function __construct($dsn, $username="", $password="", $driver_options=array()) 
	{
		try {
			parent::__construct($dsn,$username,$password, $driver_options);
		}
		catch (PDOException $e) {
			throw (new ApiException("Failed to establish connection to Database", 500));
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
			throw (new ApiException("Failed to insert the track into the database - Problem calculating length", 500));
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
			throw (new ApiException("Failed to insert the track into the database - Problem calculating centroid", 500));
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
			throw (new ApiException("Failed to insert GPX data into the database", 500));
		}

		$gpxfileid = intval($this->lastInsertId("gpx_files_id_seq"));
		$this->commit();

		return $gpxfileid;
	}

	public function writeRoute($route) 
	{
		$route_id = 0;
		$user_id = 1;
		$route->calculateAscentDescent();
		$tags = \TB\Postgis::hstoreFromMap($route->getTags());

		$this->beginTransaction();
		$q = 'INSERT INTO routes (name, gpx_file_id, tags, user_id, region, slug) VALUES (?, ?, ?, ?, ?, ?)';
		$pq = $this->prepare($q);
		$success = $pq->execute(array($route->getName(), $route->getGpxFileId(), $tags, $user_id, $route->getRegion(), $route->getName()));
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
			$rpcoordswkt = 'ST_SetSRID(ST_MakePoint('.$rpcoords['long'].', '.$rpcoords['lat'].'), 4326)';

			$tags = \TB\Postgis::hstoreFromMap($rptags);

			$success = $pq->execute(array(
			$route_id, 
			$pointnumber,
			$rpcoords['long'],
			$rpcoords['lat'], 
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
					 ST_AsText(ST_Centroid(ST_MakeLine(rp.coords ORDER BY rp.point_number ASC))) as centroid,
					 ST_AsText(Box2D(ST_MakeLine(rp.coords ORDER BY rp.point_number ASC))) as bbox
			  FROM routes r, route_points rp
			  WHERE r.id=? AND rp.route_id=r.id
			  GROUP BY name, length, r.tags, r.slug, r.region";
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
			$c = explode(" ", substr(trim($row['centroid']),6,-1));
			$route->setCentroid($c[0], $c[1]); 
			$t = json_decode('{' . str_replace('"=>"', '":"', $row['rtags']) . '}', true);
			foreach ($t as $tag => $v) {
				$route->setTag($tag, $v);
			}
		} else {
		  	throw (new ApiException("Route does not exist", 404));
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
			throw (new ApiException("Failed to fetch route from Database", 500));
		}
		$this->commit();

		while ($row = $pq->fetch(\PDO::FETCH_ASSOC)) {
			$coords = explode(" ", substr(trim($row['rpcoords']),6,-1)); //Strips POINT( and trailing )
			$route->addRoutePoint(
				$coords[0], 
				$coords[1], 
				json_decode('{' . str_replace('"=>"', '":"', $row['rptags']) . '}', true)
			);
		}

		return $route;
	}
	
	public function readRoutes($user_id, $count = null) 
	{
		$q = 'SELECT r.id, r.name, r.slug, r.region, r.length, ST_X(r.centroid) AS long, ST_Y(r.centroid) AS lat, r.tags 
			  FROM routes r
			  INNER JOIN route_medias rm ON r.id=rm.route_id		
			  WHERE r.user_id=:user_id
			  GROUP BY r.id
			  LIMIT :count';

		$pq = $this->prepare($q);
		$pq->bindParam('user_id', $user_id, \PDO::PARAM_INT);
		$pq->bindParam('count', $count, \PDO::PARAM_INT);
		$success = $pq->execute();
		if (!$success) {
			throw (new ApiException("Failed to fetch route from Database", 500));
		}

		$routes = array();
		
		while ($row = $pq->fetch(\PDO::FETCH_ASSOC)) {
			
			$route = new Route();
			$route->setName($row['name']);
			$route->setSlug($row['slug']);
			$route->setRegion($row['region']);
			$route->setLength($row['length']);
			$route->setCentroid($row['long'], $row['lat']); 
			$t = json_decode('{' . str_replace('"=>"', '":"', $row['tags']) . '}', true);
			foreach ($t as $tag => $v) {
				$route->setTag($tag, $v);
			}
			
			$media = $this->getRouteMedia($row['id'], 1);
			if (count($media) > 0) {
				$route->setMedia(array_shift($media));
			}
			
			$routes[] = $route;
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
			throw (new ApiException("Failed to delete route $route_id", 500));
		}
		if ($pq->rowCount() < 1) {
			throw (new ApiException("Failed to delete non existing route $route_id", 404));
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
		$q = "SELECT mv.media_id AS id, ST_AsText(m.coords) AS coords, m.tags as tags, mv.path AS path, mv.version_size AS size
			  FROM medias m, route_medias rm, media_versions mv
			  WHERE m.id = rm.media_id AND rm.media_id = mv.media_id AND rm.route_id=:route_id
			  GROUP BY mv.media_id, mv.path, mv.version_size, m.coords, m.tags
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
			} else {
				$medias[$row['id']]->addVersion($row['size'], $row['path']);;
			}
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

		$tags = \TB\Postgis::hstoreFromMap($picture->getTags());

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
