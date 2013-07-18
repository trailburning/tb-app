<?php
/*
 * Copyright (c) Yann Hamon
 * Inspired from code Copyright (c) Patrick Hayes under BSD License
 */

require_once 'importers/route.php';

class GPXImporter
{
  private $namespace = FALSE;
  private $nss = ''; // Name-space string. eg 'georss:'

  /**
   * Parses GPX file - returns array of routes
   * @param string $gpx A GPX string
   * @return Routes array
   */
  public function parse($text) {
    // Change to lower-case and strip all CDATA
    $text = strtolower($text);
    $text = preg_replace('/<!\[cdata\[(.*?)\]\]>/s','',$text);
    
    // Load into DOMDocument
    $xmlobj = new DOMDocument();
    @$xmlobj->loadXML($text);
    if ($xmlobj === false) {
      throw new Exception("Invalid GPX: ". $text);
    }
    
    $this->xmlobj = $xmlobj;
    try {
      $geom = $this->parseGPXFeatures();
    } catch(InvalidText $e) {
        throw new Exception("Cannot Read Geometry From GPX: ". $text);
    } catch(Exception $e) {
        throw $e;
    }

    return $geom;
  }
  
  protected function parseGPXFeatures() {
    $routes = array();
    $routes = array_merge($routes, $this->parseTracks());
    $routes = array_merge($routes, $this->parseRoutes());
    
    if (empty($routes)) {
      throw new Exception("Invalid / Empty GPX");
    }
    
    return $routes; 
  }
  
  protected function childElements($xml, $nodename = '') {
    $children = array();
    foreach ($xml->childNodes as $child) {
      if ($child->nodeName == $nodename) {
        $children[] = $child;
      }
		}
    return $children;
  }
  

  protected function parsePointTags($node) {
		$tags = array();
		if ($node->hasChildNodes()) {
			foreach ($node->childNodes as $child) {
			  switch ($child->nodeName) {
			  	case "ele":
						$tags["altitude"] = $child->nodeValue;
			  		break;

			  	case "time":
						$tags["time"] = $child->nodeValue;
			  		break;

			  	default:
			  	  break;
				}
			}
		}
		return $tags;
	}
  

  protected function parseTracks() {
    $tracks = array();

    $trk_elements = $this->xmlobj->getElementsByTagName('trk');
    foreach ($trk_elements as $trk) {
			$track = new Route();
      foreach ($this->childElements($trk, 'trkseg') as $trkseg) {
        foreach ($this->childElements($trkseg, 'trkpt') as $trkpt) {
          $track->addRoutePoint(
            $trkpt->attributes->getNamedItem("lon")->nodeValue,
					  $trkpt->attributes->getNamedItem("lat")->nodeValue,
            $this->parsePointTags($trkpt)
					);
		    }
      }
			$tracks[] = $track;
    }
    return $tracks;
  }
 
  protected function parseRoutes() {
    $routes = array();
    $rte_elements = $this->xmlobj->getElementsByTagName('rte');
    foreach ($rte_elements as $rte) {
			$route = new Route();

      foreach ($this->childElements($rte, 'rtept') as $rtept) {
        $route->addRoutePoint(
          $rtept->attributes->getNamedItem("lon")->nodeValue,
          $rtept->attributes->getNamedItem("lat")->nodeValue,
          $this->parsePointTags($rtept)
        );
      }
      $routes[] = $route;
    }
    return $routes;
  }
}
