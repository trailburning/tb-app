<?php 

namespace TB\Bundle\FrontendBundle\Service;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use TB\Bundle\FrontendBundle\Entity\Route;
use TB\Bundle\FrontendBundle\Entity\RoutePoint;
use DOMDocument;
use DateTime;

/**
 * Generates a valid GPX structure
 */
class GPXGenerator
{
            
    protected $router;
    
    protected $datetimeFormat = 'Y-m-d\TH:i:s.000\Z';
    
    public function __construct(UrlGeneratorInterface $router) 
    {
        $this->router = $router;
    }
        
    public function generateXML(Route $route) 
    {
        $document = new DOMDocument('1.0', 'UTF-8');
        $document->preserveWhiteSpace = false;
        $document->formatOutput = true;
        
        $gpx = $this->buildGpxTag($document);
        $document->appendChild($gpx);
        
        $metadata = $this->buildMetadataTag($route, $document);
        $gpx->appendChild($metadata);
        
        $trk = $this->buildTrkTag($route, $document);
        $gpx->appendChild($trk);
        
        return trim($document->saveXML());
    }
    
    protected function buildGpxTag(DOMDocument $document) 
    {
         $gpx = $document->createElement('gpx');
         $gpx->setAttribute('version', '1.1');
         $gpx->setAttribute('creator', 'www.trailburning.com');
         $gpx->setAttribute('xsi:schemaLocation', 'http://www.topografix.com/GPX/1/1 http://www.topografix.com/GPX/1/1/gpx.xsd');
         $gpx->setAttribute('xmlns', 'http://www.topografix.com/GPX/1/1');
         $gpx->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        
        return $gpx;
    }
    
    protected function buildMetadataTag(Route $route, DOMDocument $document) 
    {
        $metadata = $document->createElement('metadata');        
        $metadata->appendChild($this->buildMetadataNameTag($route, $document));
        $metadata->appendChild($this->buildMetadataDescTag($route, $document));
        $metadata->appendChild($this->buildMetadataAuthorTag($route, $document));
        $metadata->appendChild($this->buildMetadataCopyrightTag($route, $document));
        $metadata->appendChild($this->buildMetadataLinkTag($route, $document));
        $metadata->appendChild($this->buildMetadataTimeTag($route, $document));
        if ($keywords = $this->buildMetadataKeywordsTag($route, $document)) {
            $metadata->appendChild($keywords);
        }
        
        return $metadata;
    }
    
    protected function buildMetadataNameTag(Route $route, DOMDocument $document) 
    {
        return $document->createElement('name', $route->getName());
    }
    
    protected function buildMetadataDescTag(Route $route, DOMDocument $document) 
    {
        return $document->createElement('desc', $route->getAbout());
    }
    
    protected function buildMetadataAuthorTag(Route $route, DOMDocument $document) 
    {
        $author = $document->createElement('author');
        $author->appendChild($document->createElement('name', 'trailburning.com'));
        
        return $author;
    }
    
    protected function buildMetadataCopyrightTag(Route $route, DOMDocument $document) 
    {
        $copyright = $document->createElement('copyright');
        $copyright->setAttribute('author', 'trailburning.com');
        
        return $copyright;
    }
    
    protected function buildMetadataLinkTag(Route $route, DOMDocument $document) 
    {
        $link = $document->createElement('link');        
        $link->setAttribute('href', $this->router->generate('trail', ['trailSlug' => $route->getSlug()], true));
        $linkName = $document->createElement('text', $route->getName());
        $link->appendChild($linkName);
        
        return $link;
    }
    
    protected function buildMetadataTimeTag(Route $route, DOMDocument $document) 
    {
        return $document->createElement('time', $route->getPublishedDate()->format($this->datetimeFormat));        
    }
    
    protected function buildMetadataKeywordsTag(Route $route, DOMDocument $document) 
    {
        $keywordList = $this->generateKeywordListFromRoute($route);
        if (count($keywordList) > 0) {
            $keywords = $document->createElement('keywords', implode(' ', $keywordList));        
            
            return $keywords;
        } else {
            return false;
        }
    }
           
    protected function generateKeywordListFromRoute(Route $route) 
    {
        $keywordList = [];
        if ($route->getRouteCategory()) {
            $keywordList[] = $route->getRouteCategory()->getName();
        }
        if ($route->getRouteType()) {
            $keywordList[] = $route->getRouteType()->getName();
        }
        foreach ($route->getAttributes() as $attribute) {
            $keywordList[] = $attribute->getName();
        }

        return $keywordList;
    }
    
    protected function buildTrkTag(Route $route, DOMDocument $document) 
    {
        $trk = $document->createElement('trk');
        $name = $document->createElement('name', $route->getName());
        $trk->appendChild($name);
        $trk->appendChild($this->buildTrksegTag($route, $document));
        
        return $trk;
    }
    
    protected function buildTrksegTag(Route $route, DOMDocument $document) 
    {
        $trkseg = $document->createElement('trkseg');
        foreach ($route->getRoutePoints() as $routePoint) {
            $trkseg->appendChild($this->buildTrkptTag($routePoint, $document));
        }
            
        return $trkseg;
    }
    
    protected function buildTrkptTag(RoutePoint $routePoint, DOMDocument $document) 
    {
        $trkpt = $document->createElement('trkpt');
        $trkpt->setAttribute('lon', $routePoint->getCoords()->getLongitude());
        $trkpt->setAttribute('lat', $routePoint->getCoords()->getLatitude());
        
        $tags = $routePoint->getTags();
        if (isset($tags['datetime'])) {
            $trkpt->appendChild($document->createElement('time', date($this->datetimeFormat, $tags['datetime'])));
        }
        
        return $trkpt;
    }
}