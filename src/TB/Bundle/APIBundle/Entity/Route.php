<?php 

namespace TB\Bundle\ApiBundle\Entity;

use TB\Bundle\FrontendBundle\Entity\Route as BaseRoute;

/**
* Extensions to the Route Entity for the API
*/
class Route extends BaseRoute
{
    
    public function calculateAscentDescent() 
    {
        $lastRpAltitude = 0;
        $asc = 0;
        $desc = 0;
        
        $tags = $this->getTags();

        foreach ($this->getRoutePoints() as $routePoint) {
            $rpTags = $routePoint->getTags();
            if (!isset($rpTags['altitude'])) {
                continue;
            }
            $rpAltitude = $rpTags['altitude'];
            
            if ($lastRpAltitude != 0) {
                if ($rpAltitude > $lastRpAltitude) {
                    $asc += $rpAltitude - $lastRpAltitude;
                } else {
                    $desc += $lastRpAltitude - $rpAltitude;
                }
            }

            $lastRpAltitude = $rpAltitude;
        }

        $tags['ascent'] = $asc;
        $tags['descent'] = $desc;

        $this->setTags($tags);

        return 0;
    }
    
    public function getNearestPointBytime($unixtimestamp) {
        if (sizeof($this->getRoutePoints()) < 2)
            throw new \Exception("Route is less than 2 points.");

        if ($unixtimestamp < $this->getRoutePoints()[0]->getTags()['datetime']) {
            return $this->getRoutePoints()[0];
        } else if ($unixtimestamp > end($this->getRoutePoints())->getTags()['datetime']) {
            return end($this->getRoutePoints());
        } else {
            foreach ($this->getRoutePoints() as $rp) {
                if ($rp->getTags()['datetime'] > $unixtimestamp ) {
                    return $rp; 
                }
            }
        }
    }
    
    
    public function toJSON() 
    {
        $route = '{';
        $route .= '"name": "'.$this->getName().'",';
        $route .= '"slug": "'.$this->getSlug().'",';     
        $route .= '"region": "'.$this->getRegion().'",';     
        $route .= '"length": "'.$this->getLength().'",';
        $route .= '"centroid": ['.$this->getCentroid()->getLongitude().', '.$this->getCentroid()->getLatitude().'],';
        if ($this->getBBox() !== null) {
            $route .= '"bbox": "'.$this->getBBox().'",';
        }

        $route .= '"tags": {';
        $i=0;
        foreach ($this->getTags() as $tag_name => $tag_value) {
            if ($i++ != 0) {
                $route.=',';
            }
            $route .= '"'.$tag_name.'": "'.$tag_value.'"';
        }
        $route .= '}';
        
        if (count($this->getRoutePoints()) > 0) {
            $route .= ',"route_points" : [';
            $i=0;
            foreach ($this->getRoutePoints() as $rp) {
                if ($i++ != 0) {
                    $route.=',';
                }
                $route .= '{"coords" : ['.$rp->getCoords()->getLongitude().','.$rp->getCoords()->getLatitude().'], "tags" : {';
                $j=0;
                foreach ($rp->getTags() as $rp_tag => $rp_tag_value) {
                    if ($j++ != 0) {
                        $route.=',';
                    }
                    $route .= '"'.$rp_tag.'" : "'.$rp_tag_value.'"';
                }
                $route .= '}}';
            }
            $route .= ']';
        }
        
        if ($this->media !== null) {
            $route .= ',"media": ' . json_encode($this->media);
        }

        $route .= '}';
        
        return $route;
    }
    
    private $bbox;
    
    private $media;
    
    public function setBBox($bbox) 
    { 
        $this->bbox = $bbox; 
    }
    
    public function getBBox() 
    { 
        return $this->bbox; 
    }
    
    public function setMedia($media) 
    { 
        $this->media = $media; 
    }
    
    public function getMedia() 
    { 
        return $this->media; 
    }
}
