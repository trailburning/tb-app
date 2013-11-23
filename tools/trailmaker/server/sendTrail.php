<?php
// ****************************************************************************
// index.php
// ****************************************************************************

error_reporting(E_ALL);
ini_set('display_errors', '1');

session_start();

if(isset($_POST["json"])){  
  $json = stripslashes($_POST["json"]);
  $output = json_decode($json);

  $arr = array();  

  $arrPoints = array();
  
  $arrPoints[0] = array();
  $arrPoints[0]['lat'] = 1;
  $arrPoints[0]['lng'] = 2;
  
  $arr['points'] = $arrPoints;
        
//  echo json_encode($arr);
  echo $json;
}
?>
