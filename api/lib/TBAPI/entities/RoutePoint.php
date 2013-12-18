<?php

namespace TBAPI\entities;

class RoutePoint 
{
    public $coords;
    public $tags;

    public function __construct($long, $lat, $tags) 
    {
        $this->coords['long'] = $long;
        $this->coords['lat'] = $lat;
        $this->tags = $tags;
    }

    public function getTags() 
    {
        return $this->tags;
    }

    public function getTag($tag) 
    {
        if (array_key_exists($tag, $this->tags)) {
            return $this->tags[$tag];
        } else {
            return null;
        }
    }

    public function getCoords() 
    {
        return $this->coords;
    }
}
