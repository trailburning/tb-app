<?php
// ****************************************************************************
// mailerproxy.php
// ****************************************************************************

define("EMAIL_SERVER_URL", "http://augmentedmediaprojectsltd.createsend.com/t/t/s/zjdkk/");

$strEmail = "";
if (isset($_REQUEST['form_email'])) {
  $strEmail = $_REQUEST['form_email'];
}

function post($url, $data){
  $file = @file_get_contents($url, NULL, stream_context_create(array('http' => array('method' => 'POST', 'content' => http_build_query($data)))));
  return $file ? $file : "Error POSTing to $url";
}

echo post(EMAIL_SERVER_URL, array('cm-zjdkk-zjdkk' => $strEmail));
?>
