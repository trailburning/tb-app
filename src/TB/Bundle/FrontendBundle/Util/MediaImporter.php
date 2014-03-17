<?php 

namespace TB\Bundle\FrontendBundle\Util;

use Doctrine\ORM\EntityManager;
use TB\Bundle\FrontendBundle\Entity\Route;
use TB\Bundle\FrontendBundle\Entity\RoutePoint;

/**
* 
*/
class MediaImporter
{

    protected $em;
    
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }
    
    /**
     * Finds the timezone for a Route by querin the tz_world_mp data
     * 
     * @param Route $route the Route to get the timezone for
     * @throws Exception when provided Routes centroid is not set
     * @throws Exception when no timezone was found in tz_world_mp data
     * @return mixed the timezone when found, null when no timezone was found
     */
    public function getRouteTimezone(Route $route)
    {
        if ($route->getCentroid() === null) {
            throw new \Exception('centroid is not set');
        }
        
        $sql = "SELECT tzid FROM tz_world_mp WHERE ST_Contains(geom, ST_MakePoint(:long, :lat));";
        
        $long = $route->getCentroid()->getLongitude();
        $lat = $route->getCentroid()->getLatitude();
        
        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->bindParam(':long', $long, \PDO::PARAM_STR);
        $stmt->bindParam(':lat', $lat, \PDO::PARAM_STR);
        
        if (!$stmt->execute()) {
            throw new \Exception('failed fetching timezone for route');
        }

        if ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $timezone = $row['tzid'];
        } else {
            throw new \Exception(sprintf('Missing timezone for %s %s', $this->getCentroid()->getLongitude(), $this->getCentroid()->getLatitude()));
        }

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
            throw new Exception(sprintf('missing datetime tag for RoutePoint with id: $s', $row['id']));
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
     * @throws Exception when no RoutePoint if found for different reasons
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
                FROM route_points WHERE route_id = :route_id 
                ORDER BY diff ASC LIMIT 1";
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
    
}


