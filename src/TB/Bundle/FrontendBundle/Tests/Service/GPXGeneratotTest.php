<?php 

namespace TB\Bundle\FrontendBundle\Tests\Service;

use TB\Bundle\FrontendBundle\Tests\AbstractFrontendTest;
use DOMDocument;

class GPXGeneratorTest extends AbstractFrontendTest
{

    public function testGenerateXML()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\RouteData',
        ]);
        
        $gpxGenerator = $this->getContainer()->get('tb.gpx_generator'); 
        $route = $this->getRoute('grunewald');
        
        $expected = '<?xml version="1.0" encoding="UTF-8"?>
<gpx xmlns="http://www.topografix.com/GPX/1/1" version="1.1" creator="www.trailburning.com" xsi:schemaLocation="http://www.topografix.com/GPX/1/1 http://www.topografix.com/GPX/1/1/gpx.xsd" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
  <metadata>
    <name>Grunewald</name>
    <desc>The Grunewald is a forest located in the western side of Berlin on the east side of the river Havel.</desc>
    <author>
      <name>trailburning.com</name>
    </author>
    <copyright author="trailburning.com"/>
    <link href="http://localhost/trail/grunewald">
      <text>Grunewald</text>
    </link>
    <time>' . $route->getPublishedDate()->format('Y-m-d\TH:i:s.000\Z') . '</time>
    <keywords>Park Marathon run</keywords>
  </metadata>
  <trk>
    <name>Grunewald</name>
    <trkseg>
      <trkpt lon="13.257437" lat="52.508006">
        <time>2013-08-17T08:41:17.000Z</time>
      </trkpt>
      <trkpt lon="13.257437" lat="52.508006">
        <time>2013-08-17T08:47:56.000Z</time>
      </trkpt>
      <trkpt lon="13.249617" lat="52.501565">
        <time>2013-08-17T08:47:02.000Z</time>
      </trkpt>
      <trkpt lon="13.248257" lat="52.50296">
        <time>2013-08-17T08:50:46.000Z</time>
      </trkpt>
      <trkpt lon="13.227167" lat="52.496973">
        <time>2013-08-17T09:00:55.000Z</time>
      </trkpt>
      <trkpt lon="13.231805" lat="52.490537">
        <time>2013-08-17T09:05:45.000Z</time>
      </trkpt>
      <trkpt lon="13.233876" lat="52.48959">
        <time>2013-08-17T09:10:12.000Z</time>
      </trkpt>
      <trkpt lon="13.221316" lat="52.489695">
        <time>2013-08-17T09:14:57.000Z</time>
      </trkpt>
      <trkpt lon="13.213987" lat="52.490498">
        <time>2013-08-17T09:17:42.000Z</time>
      </trkpt>
      <trkpt lon="13.203118" lat="52.491101">
        <time>2013-08-17T09:21:59.000Z</time>
      </trkpt>
      <trkpt lon="13.193966" lat="52.485072">
        <time>2013-08-17T09:29:14.000Z</time>
      </trkpt>
      <trkpt lon="13.192097" lat="52.478326">
        <time>2013-08-17T09:33:42.000Z</time>
      </trkpt>
      <trkpt lon="13.196252" lat="52.471298">
        <time>2013-08-17T09:39:15.000Z</time>
      </trkpt>
      <trkpt lon="13.196559" lat="52.477397">
        <time>2013-08-17T09:44:47.000Z</time>
      </trkpt>
      <trkpt lon="13.196279" lat="52.477955">
        <time>2013-08-17T09:45:51.000Z</time>
      </trkpt>
    </trkseg>
  </trk>
</gpx>';
        
        $xml = $gpxGenerator->generateXML($route);   
        $this->assertEquals($expected, $xml);
        
        $document = new DOMDocument(); 
        $document->loadXML($xml); 
        $this->assertTrue($document->schemaValidate(realpath(__DIR__ . '/../../DataFixtures/GPX/gpx.xsd')));
    }

}    