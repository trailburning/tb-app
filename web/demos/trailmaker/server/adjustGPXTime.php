<?php
// ****************************************************************************
// adjustGPXTime.php
//
// file = FILENAME.GPX
// time = 2013-12-04T09:00:00.000Z
// ****************************************************************************

error_reporting(E_ALL);
ini_set('display_errors', '1');

function editDatetime($dom, $xfile) {
  $date = null;
  
  $nodeList = $xfile->query("//gpx:trkpt");  
  foreach ($nodeList as $node) {
    $nodeTime = $xfile->query("gpx:time", $node);
    if ($nodeTime) {
      if ($date == null) {
        $date = new DateTime(substr($nodeTime->item(0)->nodeValue, 0, 4) . "-" . substr($nodeTime->item(0)->nodeValue, 5, 2) . "-" . substr($nodeTime->item(0)->nodeValue, 8, 2) . " " . substr($nodeTime->item(0)->nodeValue, 11, 2) . ":" . substr($nodeTime->item(0)->nodeValue, 14, 2) . ":" . substr($nodeTime->item(0)->nodeValue, 17, 2), new DateTimeZone('UTC'));  
      }
      else {
        $date->add(new DateInterval('PT1S'));
        echo 'datetime adj : ' . $date->format('Y-m-d\TH:i:s\Z') . '<br/>';
        // store new datetime
        $nodeTime->item(0)->nodeValue = $date->format('Y-m-d\TH:i:s\Z');
      }      
    }
  }          
}

function createDatetime($dom, $xfile, $strDatetime) {
  $date = new DateTime(substr($strDatetime, 0, 4) . "-" . substr($strDatetime, 5, 2) . "-" . substr($strDatetime, 8, 2) . " " . substr($strDatetime, 11, 2) . ":" . substr($strDatetime, 14, 2) . ":" . substr($strDatetime, 17, 2), new DateTimeZone('UTC'));  

  $nodeList = $xfile->query("//gpx:trkpt");  
  foreach ($nodeList as $node) {
    $date->add(new DateInterval('PT1S'));
    echo 'datetime adj : ' . $date->format('Y-m-d\TH:i:s\Z') . '<br/>';
    $element = $dom->createElement('time', $date->format('Y-m-d\TH:i:s\Z'));
    $node->appendChild($element);
  }            
}

if(isset($_GET["file"])){  
  $strFile = $_GET["file"];
  
  // src doc
  $docSrc = new DomDocument('1.0', 'UTF-8');
  $docSrc->load($strFile);
  $xfile = new DomXPath($docSrc);
  $rootNamespace = $docSrc->lookupNamespaceUri($docSrc->namespaceURI);
  $xfile->registerNamespace("gpx", $rootNamespace);
  
  if(isset($_GET["time"])){    
    $strTime = $_GET["time"];
    createDatetime($docSrc, $xfile, $strTime);
  }  
  else {
    editDatetime($docSrc, $xfile);    
  }
  
  $docSrc->save('TIME_ADJ_' . $strFile);
}      
?>
      