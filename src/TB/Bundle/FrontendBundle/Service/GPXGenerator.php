<?php 

namespace TB\Bundle\FrontendBundle\Service;

use TB\Bundle\FrontendBundle\Entity\Route;

/**
 * Generates a valid GPX structure
 */
class GPXGenerator
{
            
    public function generate(Route $route) 
    {
        $xml = 
    }
    
    protected function generateXMLDeclaration(Route $route) 
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';

        return $xml;
    }
    
    protected function generateRoot() 
    {
        $xml = '
<gpx version="1.1" creator="www.trailburning.com"
    xsi:schemaLocation="http://www.topografix.com/GPX/1/1 http://www.topografix.com/GPX/1/1/gpx.xsd"
    xmlns="http://www.topografix.com/GPX/1/1">
    PLACEHOLDER
</gpx>';
        
        return $xml;
    }
    
    protected function generateMetadata(Route $route) 
    {
        $xml = sprintf('
    <metadata>
        <name>%s</name>
        <desc>%s</desc>
        <link href="%s">
            <text>%s</text>
        </link>
        <time>2013-05-25T08:14:53.000Z</time>
    </metadata>', 
            $route->getName(), 
            $route->getAbout(), 
            'http://www.trailburning.com/trail/' . 
            $route->getSlug(), 
            $route->getName()
        );
        
        return $xml;
    }
    
    
}