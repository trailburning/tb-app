<?php
// ****************************************************************************
// adjustGPXTime.php
//
// file = FILENAME.GPX
// ****************************************************************************

error_reporting(E_ALL);
ini_set('display_errors', '1');

if(isset($_GET["file"])){  
  $strFile = $_GET["file"];

  // src doc
  $docSrc = new DomDocument('1.0', 'UTF-8');
  $docSrc->load($strFile);
  $xfile = new DomXPath($docSrc);
  $rootNamespace = $docSrc->lookupNamespaceUri($docSrc->namespaceURI);
  $xfile->registerNamespace("gpx", $rootNamespace);
  
  $nPos = 0;
  $date = null;
  
  $nodeList = $xfile->query("//gpx:trkpt");  
  foreach ($nodeList as $node) {
    $nodeTime = $xfile->query("gpx:time", $node);
    if ($nodeTime) {
      if ($nPos == 0) {
        $date = new DateTime(substr($nodeTime->item(0)->nodeValue, 0, 4) . "-" . substr($nodeTime->item(0)->nodeValue, 5, 2) . "-" . substr($nodeTime->item(0)->nodeValue, 8, 2) . " " . substr($nodeTime->item(0)->nodeValue, 11, 2) . ":" . substr($nodeTime->item(0)->nodeValue, 14, 2) . ":" . substr($nodeTime->item(0)->nodeValue, 17, 2), new DateTimeZone('UTC'));  
      }
      else {
        $date->add(new DateInterval('PT1S'));
        echo 'datetime adj : ' . $nPos . ' : ' . $date->format('Y-m-d\TH:i:s\Z') . '<br/>';
        // store new datetime
        $nodeTime->item(0)->nodeValue = $date->format('Y-m-d\TH:i:s\Z');
      }      
      $nPos++;
    }
  }        
  $docSrc->save('TIME_ADJ_' . $strFile);
}      
?>
      