<?php
// ****************************************************************************
// mailerproxy.php
// ****************************************************************************

define("SERVER_URL", "http://www.trailburning.com/server/sendTrail.php");

$json = "";
if(isset($_POST["json"])){
  $json = stripslashes($_POST["json"]);  
}
//$json = '{"id":56,"name":"Field 1","email":"Field 2","event_name":"Field 3","trail_name":"Field 4","trail_notes":"Field 5","media":[{"date":"Sun, 01 Sep 2013 07:14:54 GMT","lat":-34.88057859242,"lng":138.72867562808,"name":"Pic1"},{"date":"Sun, 01 Sep 2013 09:55:49 GMT","lat":-34.9296467565,"lng":138.71246343479,"name":"Pic2"}]}';   

function post($url, $data){
  $file = @file_get_contents($url, NULL, stream_context_create(array('http' => array('method' => 'POST', 'content' => http_build_query($data)))));
  return $file ? $file : "Error POSTing to $url";
}

echo post(SERVER_URL, array('json' => $json));
?>
