<?php
// ****************************************************************************
// index.php
// ****************************************************************************

include("include/incDefine.php");

session_start();

function addDetails($output, $body) {
  $body .= "<tr><td><strong>Event Details:</strong></td></tr>";
  if (isset($output->id)) {
    $body .= "<tr><td>Trail ID:</td><td>" . $output->id . "</td></tr>";
  }
  if (isset($output->name)) {
    $body .= "<tr><td>Name:</td><td>" . $output->name . "</td></tr>";
  }
  if (isset($output->email)) {  
    $body .= "<tr><td>Email:</td><td>" . $output->email . "</td></tr>";
  }
  if (isset($output->event_name)) {
    $body .= "<tr><td>Event Name:</td><td>" . $output->event_name . "</td></tr>";
  }
  if (isset($output->trail_name)) {
    $body .= "<tr><td>Trail Name:</td><td>" . $output->trail_name . "</td></tr>";
  }
  if (isset($output->trail_notes)) {
    $body .= "<tr><td>Trail Notes:</td><td>" . $output->trail_notes . "</td></tr>";
  }
  $body .= "<tr><td></td></tr>";  
}

function addMediaPoint($mediaPoint, $body) {
  $body .= "<tr><td><strong>Media Point:</strong></td></tr>";
  $body .= "<tr><td>Name:</td><td>" . $mediaPoint->name . "</td></tr>";
  $body .= "<tr><td>Date:</td><td>" . $mediaPoint->date . "</td></tr>";
  $body .= "<tr><td>Lat:</td><td>" . $mediaPoint->lat . "</td></tr>";
  $body .= "<tr><td>Lng:</td><td>" . $mediaPoint->lng . "</td></tr>";  
}

if(isset($_POST["json"])){  
  $json = stripslashes($_POST["json"]);  
//  $json = '{"id":56,"name":"Field 1","email":"Field 2","event_name":"Field 3","trail_name":"Field 4","trail_notes":"Field 5","media":[{"date":"Sun, 01 Sep 2013 07:14:54 GMT","lat":-34.88057859242,"lng":138.72867562808,"name":"Pic1"},{"date":"Sun, 01 Sep 2013 09:55:49 GMT","lat":-34.9296467565,"lng":138.71246343479,"name":"Pic2"}]} ';   
  $output = json_decode($json);

  // build html of fields
  $body = "<table>";
  addDetails($output, &$body);
  foreach ($output->media as $key => $mediaPoint) {
    addMediaPoint($mediaPoint, &$body);
  }  
  $body .= "</table>";

  $strEmailFrom = EMAIL_FROM;
  $strEmailTo = EMAIL_TO;
  $strSubject = EMAIL_SUBJECT;
  
  require_once('include/class.phpmailer.php');
  
  $mail = new PHPMailer(); // defaults to using php "mail()"
  $mail->SetFrom($strEmailFrom);
  
  $mail->AddAddress($strEmailTo);
  $mail->Subject = $strSubject;
  
  $mail->MsgHTML($body);
  // now send
  $mail->Send();
        
  echo $json;
}
?>
