<?php 

namespace TB;

require_once 'iDatabase.php';

class Postgis 
	extends \PDO 
	implements \TB\iDatabase {

  function __construct($dsn, $username="", $password="", $driver_options=array()) {
      parent::__construct($dsn,$username,$password, $driver_options);
  }

	function importRoute($route) {
		$routeid = 0;
		$this->beginTransaction();

		$q = "INSERT INTO routes (name) VALUES (?)";
		$pq = $this->prepare($q);
		$success = $pq->execute(array("random"));
		if (!$success) {echo "FAILED TO INSERT:".print_r($pq->errorInfo())."<br>";}

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
				echo "FAILED TO INSERT POINT:".print_r($pq->errorInfo())."<br>";
			}
		}

		$this->commit();
		return $routeid;
	}



	function exportRoute($routeid, $format) {
		$this->beginTransaction();
		$q = "SELECT route.id AS routeid, 
		    	  route.name AS name, 
					  ST_AsGeoJson(ST_MakeLine(routepoint.coords)) AS route
					FROM routes as route,
			  		( SELECT point.routeid,
			      		point.pointnumber,
			      		point.coords
			   			FROM routepoints as point
			   			WHERE routeid=?
			   			ORDER BY pointnumber ASC)
			   	AS routepoint
					WHERE route.id=?
					GROUP BY route.id, routepoint.routeid";

		$pq = $this->prepare($q);
		$success = $pq->execute(array(16,16));
		if (!$success) {echo "FAILED TO SELECT:".print_r($pq->errorInfo())."<br>";}
		var_dump ($pq->fetchAll());
	}
}

?>
