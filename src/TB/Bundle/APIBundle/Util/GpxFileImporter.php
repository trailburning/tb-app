<?php
/*
 * Copyright (c) Yann Hamon
 * Inspired from code Copyright (c) Patrick Hayes under BSD License
 */

namespace TB\Bundle\APIBundle\Util;

use TB\Bundle\FrontendBundle\Entity\Route;
use TB\Bundle\FrontendBundle\Entity\RoutePoint;
use CrEOF\Spatial\PHP\Types\Geometry\Point;

class GpxFileImporter
{
    private $namespace = false;
    private $nss = ''; // Name-space string. eg 'georss:'

    /**
     * Parses GPX data - returns array of routes
     * @param string $gpx A GPX string
     * @return Routes array
     */
    public function parse($text) 
    {
        // Change to lower-case and strip all CDATA
        $text = strtolower($text);
        $text = preg_replace('/<!\[cdata\[(.*?)\]\]>/s','',$text);

        // Load into DOMDocument
        $xmlobj = new \DOMDocument();
        @$xmlobj->loadXML($text);
        if ($xmlobj === false) {
            throw new \Exception("Invalid GPX: ". $text);
        }

        $this->xmlobj = $xmlobj;
        try {
            $routes = $this->parseGPXFeatures();
        } catch(\Exception $e) {
            throw $e;
        }

        return $routes;
    }

    protected function parseGPXFeatures() 
    {
        $routes = array();
        $routes = array_merge($routes, $this->parseTracks());
        $routes = array_merge($routes, $this->parseRoutes());

        if (empty($routes)) {
            throw new \Exception("Invalid / Empty GPX");
        }

        return $routes; 
    }

    protected function childElements($xml, $nodename = '') 
    {
        $children = array();
        foreach ($xml->childNodes as $child) {
            if ($child->nodeName == $nodename) {
                $children[] = $child;
            }
        }
        
        return $children;
    }

    protected function parsePointTags($node) 
    {
        $tags = array();
        if ($node->hasChildNodes()) {
            foreach ($node->childNodes as $child) {
                switch ($child->nodeName) {
                    case "ele":
                        $tags["altitude"] = $child->nodeValue;
                        break;

                    case "time":
                        $tags['datetime'] = strtotime($child->nodeValue);
                        break;

                    default:
                        break;
                }
            }
        }
        
        // set altitude to null if it is empty, or doesn't exist at all
        if (!isset($tags['altitude']) || (isset($tags['altitude']) && $tags['altitude'] === '')) {
            $tags['altitude'] = null;
        }
        
        return $tags;
    }

    protected function parseTracks() 
    {
        $routes = array();

        $trk_elements = $this->xmlobj->getElementsByTagName('trk');
        foreach ($trk_elements as $trk) {
            $route = new Route();
            $names = $trk->getElementsByTagName('name');
            foreach ($names as $name) {
                $route->setName($name->nodeValue);
            } 
            
            foreach ($this->childElements($trk, 'trkseg') as $trkseg) {
                foreach ($this->childElements($trkseg, 'trkpt') as $trkpt) {
                    $routePoint = new RoutePoint();
                    $routePoint->setCoords(new Point($trkpt->attributes->getNamedItem("lon")->nodeValue, $trkpt->attributes->getNamedItem("lat")->nodeValue, 4326));
                    $routePoint->setTags($this->parsePointTags($trkpt));
                    $route->addRoutePoint($routePoint);
                }
            }
            $routes[] = $route;
        }
        
        return $routes;
    }

    protected function parseRoutes() 
    {
        $routes = array();
        $rte_elements = $this->xmlobj->getElementsByTagName('rte');
        foreach ($rte_elements as $rte) {
            $route = new Route();

            foreach ($this->childElements($rte, 'rtept') as $rtept) {
                $routePoint = new RoutePoint();
                $routePoint->setCoords(new Point($rtept->attributes->getNamedItem("lon")->nodeValue, $rtept->attributes->getNamedItem("lat")->nodeValue, 4326));
                $routePoint->setTags($this->parsePointTags($rtept));
                $route->addRoutePoint($routePoint);
            }
            $routes[] = $route;
        }
        
        return $routes;
    }
}
