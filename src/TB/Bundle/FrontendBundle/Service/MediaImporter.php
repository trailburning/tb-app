<?php 

namespace TB\Bundle\FrontendBundle\Service;

use Doctrine\ORM\EntityManager;
use TB\Bundle\FrontendBundle\Entity\Route;
use TB\Bundle\FrontendBundle\Entity\RoutePoint;
use CrEOF\Spatial\PHP\Types\Geometry\Point;
use Guzzle\Http\Client;

/**
* 
*/
class MediaImporter
{

    protected $em;
    protected $httpClient;
    
    public function __construct(EntityManager $em, Client $httpClient)
    {
        $this->em = $em;
        $this->httpClient = $httpClient;
    }
    
    /**
     * Finds the timezone for a Route by looking up the google timezone api
     * 
     * @param Route $route the Route to get the timezone for
     * @throws Exception when provided Routes centroid is not set
     * @throws Exception when no timezone a call to the google timezne api leads to an error
     * @return string the timezone
     */
    public function getRouteTimezone(Route $route)
    {
        if ($route->getCentroid() === null) {
            throw new \Exception('centroid is not set');
        }
        
        $firstRoutePoint = $this->getFirstRoutePoint($route);
                
        $url = sprintf('https://maps.googleapis.com/maps/api/timezone/json?location=%s,%s&timestamp=%s', $firstRoutePoint->getCoords()->getLatitude(), $firstRoutePoint->getCoords()->getLongitude(), $firstRoutePoint->getTags()['datetime']);
        
        $request = $this->httpClient->get($url);
        $request->send();
        $response = $request->getResponse();
        
        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Unable to get timezone from google timezone api, http status code %s for URL: %s', $response->getStatusCode(), $url);
        }
        
        $timezone = $response->json()['timeZoneId'];
        
        return $timezone;
    }
    
    /**
     * Calculates the time offset in seconds between this Routes timezone and UTC.
     * This method is used when matching Route Media with RoutePoints by datetime
     *
     * @param Route $route the Route to get the timezone for
     * @throws Exception when no RoutePoints were found for this Route
     * @throws Exception when the first RoutePoint has no datetime tag
     * @return int timezone offset
     */
    public function getRouteTimezoneOffset(Route $route)
    {
        if ($route->getId() == 0) {
            throw new \Exception('The Route must be persisted before calculating a timezone offset');
        }
        
        // get the datetime tag of the first RoutePoint from this Media's Route to calculate the Routes timezone offset
        $sql = "SELECT id, tags FROM route_points rp 
                WHERE rp.route_id=:route_id
                ORDER BY rp.point_number ASC LIMIT 1";
        $stmt = $this->em->getConnection()->prepare($sql);
        $routeId = $route->getId();
        $stmt->bindParam(':route_id', $routeId, \PDO::PARAM_INT);
        if (!$stmt->execute()) {
            throw new \Exception('failed to fetch first RoutePoint for Route');
        }
        
        if ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $tags = json_decode('{' . str_replace('"=>"', '":"', $row['tags']) . '}', true);
        } else {
            throw new \Exception(sprintf('Missing RoutePoints for Route with id: %s', $route->getId()));
        }
        
        if (!isset($tags['datetime'])) {
            throw new \Exception(sprintf('missing datetime tag for RoutePoint with id: %s', $row['id']));
        }
        
        // get the timezone from this Route
        $timezone = $this->getRouteTimezone($route);
        $dtz = new \DateTimeZone($timezone);
        
        // calculate the datetimezone offset
        $offset = $dtz->getOffset(\DateTime::createFromFormat('U', $tags['datetime']));
        
        return $offset;
    }
    
    /**
     * Finds the RoutePoint from a given Route whos datetime is nearest to a given timestamp
     *
     * @param Route $route The Route to search for the RoutePoint
     * @param int $unixtimestamp The timestamp to search for the nearest RoutePoint
     * @throws Exception when no RoutePoint if found for various reasons
     * @return RoutePoint
     */
    public function getNearestRoutePointByTime(Route $route, $unixtimestamp) 
    {
        $sql = "SELECT CASE 
                WHEN (tags->'datetime')::integer > :timestamp 
                    THEN (tags->'datetime')::integer - :timestamp
                WHEN (tags->'datetime')::integer < :timestamp 
                    THEN :timestamp - (tags->'datetime')::integer
                WHEN (tags->'datetime')::integer = :timestamp 
                    THEN 0
                END AS diff, id
                FROM route_points 
                WHERE route_id = :route_id 
                ORDER BY diff 
                ASC LIMIT 1";
        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->bindValue(':route_id', $route->getId(), \PDO::PARAM_INT);
        $stmt->bindParam(':timestamp', $unixtimestamp, \PDO::PARAM_INT);
        if (!$stmt->execute()) {
            throw new \Exception('Failed to fetch nearest RoutePoint by datetime');
        }

        if ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $id = $row['id'];
        } else {
            throw new \Exception(sprintf('Route with id %s has no RoutePoints', $route->getId()));
        } 

        $routePoint = $this->em->getRepository('TBFrontendBundle:RoutePoint')->findOneById($id);
        if (!$routePoint) {
            throw new \Exception(sprintf('failed to fetch RoutePoint with id: %s', $id));
        }
        
        return $routePoint;
    }
    
    /**
     * Finds the RoutePoint from a given Route that is nearest to a given Geometry Point
     *
     * @param Route $route The Route to search for the RoutePoint
     * @param Point $point The Geometry Point to search for the nearest RoutePoint
     * @throws Exception when no RoutePoint if found for various reasons
     * @return RoutePoint
     */
    public function getNearestRoutePointByGeoPoint(Route $route, Point $point) 
    {
        $sql = "SELECT id
                FROM route_points 
                WHERE route_id = :route_id 
                ORDER BY ST_Distance(coords, ST_GeomFromText('POINT(" . $point->getLongitude() . " " . $point->getLatitude() . ")', 4326)) ASC 
                LIMIT 1";
        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->bindValue(':route_id', $route->getId(), \PDO::PARAM_INT);
        // $stmt->bindValue(':long', $point->getLongitude(), \PDO::PARAM_STR);
        // $stmt->bindParam(':lat', $point->getLatitude(), \PDO::PARAM_STR);
        if (!$stmt->execute()) {
            throw new \Exception('Failed to fetch nearest RoutePoint by Point');
        }

        if ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $id = $row['id'];
        } else {
            throw new \Exception(sprintf('Route with id %s has no RoutePoints', $route->getId()));
        } 

        $routePoint = $this->em->getRepository('TBFrontendBundle:RoutePoint')->findOneById($id);
        if (!$routePoint) {
            throw new \Exception(sprintf('failed to fetch RoutePoint with id: %s', $id));
        }
        
        return $routePoint;
    }
    
    /**
     * @param Route $route The Route to look for the first RoutePoint
     */
    public function getFirstRoutePoint(Route $route)
    {
        $query = $this->em
            ->createQuery('
                SELECT rp FROM TBFrontendBundle:RoutePoint rp
                WHERE rp.routeId=:routeId
                ORDER BY rp.id ASC')
            ->setParameter('routeId', $route->getId())
            ->setMaxResults(1);
        try {
            $routePoint = $query->getSingleResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            throw new \Exception(sprintf('First RoutePoint not found for Route with id: %s', $route->getId()));
        }
        
        return $routePoint;
    }
    
    /**
     * extracts Latitude and Longitude from EXIF data array
     * 
     * @param array EXIF tags array, the result of exif_read_data()
     * @return mixed returns a CrEOF\Spatial\PHP\Types\Geometry\Point, or null wehn no GPS data was found
     */
    public function getGeometryPointFromExif(array $exif)
    {
        if (!isset($exif['GPSLongitude']) || !isset($exif['GPSLongitudeRef']) || !isset($exif['GPSLatitude']) || !isset($exif['GPSLatitudeRef'])) {
            return null;
        }
        
        $longitude = $this->getGps($exif["GPSLongitude"], $exif['GPSLongitudeRef']);
        $latitude = $this->getGps($exif["GPSLatitude"], $exif['GPSLatitudeRef']);
        $point = new Point($longitude, $latitude, 4326);
        
        return $point;
    }
    
    /**
     * Helper function to extract and format GPS coordinates from EXIF data
     */
    protected function getGps($exifCoord, $hemi) 
    {
        $degrees = count($exifCoord) > 0 ? $this->gps2Num($exifCoord[0]) : 0;
        $minutes = count($exifCoord) > 1 ? $this->gps2Num($exifCoord[1]) : 0;
        $seconds = count($exifCoord) > 2 ? $this->gps2Num($exifCoord[2]) : 0;

        $flip = ($hemi == 'W' or $hemi == 'S') ? -1 : 1;

        return $flip * ($degrees + $minutes / 60 + $seconds / 3600);
    }

    /**
     * Helper function to extract and format GPS coordinates from EXIF data
     */
    protected function gps2Num($coordPart) 
    {
        $parts = explode('/', $coordPart);

        if (count($parts) <= 0) {
            return 0;
        }

        if (count($parts) == 1) {
            return $parts[0];
        }

        return floatval($parts[0]) / floatval($parts[1]);
    }
    
}


