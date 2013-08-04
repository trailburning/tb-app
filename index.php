<?php

require_once 'init.php';
require_once 'vendor/autoload.php';

require_once 'ApiException.php';
require_once 'ExceptionHandling.php';

require_once 'databases/postgis.php';

\Slim\Slim::registerAutoloader();
$slim = new \Slim\Slim();

// Debug needs to be set to false for our custom exception handlers to be called
$slim->config(array('log.enable' => true,'debug' => false)); 
$slim->error(function (\Exception $e) { \TB\handleException($e);} ); 

$slim->get('/v1/route/import/gpx', function () use ($slim) {
  $slim->render('ImportGpx.php');
});

$slim->post('/v1/route/import/gpx', function () use ($slim) {
  require_once 'importers/GPX.php';

  global $api_root, $conf_path;

  if (!array_key_exists("gpxfile", $_FILES)) 
    throw (new \TB\ApiException("Gpxfile variable not set", 400));
  if ($_FILES['gpxfile']['error'] != 0) 
    throw (new \TB\ApiException("An error happened uploading the GPX file", 400));

  $gpx_filename = $_FILES["gpxfile"]["name"];
  $gpx_filename = preg_replace('/[^\w\-~_\.]+/u', '-', $gpx_filename);
  $gpx_tmp_name  = $_FILES["gpxfile"]["tmp_name"];

  $gpximporter = new GPXImporter();
  $routes = $gpximporter->parse(file_get_contents($gpx_tmp_name), "gpx");
  
/*
  $aws_client = \Aws\S3\S3Client::factory(array(
    'key'    => $aws_config['AWSAccessKeyId'],
    'secret' => $aws_config['AWSSecretKey']
    ));
  $result = $aws_client->putObject(array(
      'Bucket' => 'trailburning-gpx',
      'Key'    =>  $gpx_filename,
      'Body'   => file_get_contents($gpx_tmp_name)
  ));
*/

  $db = new \TB\Postgis(
    $_SERVER['DB_DRIVER'].':host='.$_SERVER['DB_HOST'].'; port='.$_SERVER['DB_PORT'].';dbname='.$_SERVER['DB_DATABASE'], 
    $_SERVER['DB_USER'], 
    $_SERVER['DB_PASSWORD'], 
    array(PDO::ATTR_PERSISTENT => true, PDO::ERRMODE_EXCEPTION => true)
  );

  $gpxfileid = $db->importGpxFile('s3://trailburning-gpx/'.$gpx_filename);
  $importedRoutesIds = array();
  foreach ($routes as $route){
    $importedRoutesIds[] = $db->writeRoute($gpxfileid, $route);
  }

  $res = $slim->response();
  $res['Content-Type'] = 'application/json';
  $slim->render('ApiReplyView.php', array("value" => '{"routeids": '.json_encode($importedRoutesIds).'}', 'usermsg' => 'GPX successfully imports'), 200);
});

$slim->get('/v1/route/:id', function ($routeid) use ($slim) {
  require_once 'importers/GPX.php';
  
  global $api_root, $conf_path;

  $db = new \TB\Postgis(
    $_SERVER['DB_DRIVER'].':host='.$_SERVER['DB_HOST'].'; port='.$_SERVER['DB_PORT'].';dbname='.$_SERVER['DB_DATABASE'], 
    $_SERVER['DB_USER'], 
    $_SERVER['DB_PASSWORD'], 
    array(PDO::ATTR_PERSISTENT => true, PDO::ERRMODE_EXCEPTION => true)
  );

  $route = $db->readRoute($routeid);
  $res = $slim->response();
  $res['Content-Type'] = 'application/json';
  $slim->render('ApiReplyView.php', array("value" => '{"route": '.$route->ToJSON().'}', 'usermsg' => 'success'), 200);
});

$slim->get('/v1/route/:id/pictures/add', function ($routeid) use ($slim) {
  $slim->render('PicturesNew.php', array('routeid' => $routeid));
});

$slim->post('/v1/route/:id/pictures/add', function ($routeid) use ($slim) {
  require_once 'Picture.php';

  global $api_root, $conf_path;

  $slim = \Slim\Slim::getInstance();
  $res = $slim->response();
  $res['Content-Type'] = 'application/json';

  if (!array_key_exists('pictures', $_FILES)) 
    throw (new \TB\ApiException("Picture variable not set", 400));

  $picturesIds = array();

  for ($i=0; $i<count($_FILES['pictures']['name']); $i++) {

    if ($_FILES['pictures']['error'][$i] != 0)
      throw (new \TB\ApiException("An error happened uploading the picture", 400));    

    $picture_filename = $_FILES["pictures"]["name"][$i];
    $picture_filename = preg_replace('/[^\w\-~_\.]+/u', '-', $picture_filename);
    $picture_tmp_name  =$_FILES["pictures"]["tmp_name"][$i]; 
    /*
    $aws_client = \Aws\S3\S3Client::factory(array(
      'key'    => $aws_config['AWSAccessKeyId'],
      'secret' => $aws_config['AWSSecretKey']
      ));
    $result = $aws_client->putObject(array(
        'Bucket' => 'trailburning-media',
        'Key'    =>  $picture_filename,
        'Body'   => file_get_contents($picture_tmp_name)
    ));
*/
    $pic = new \TB\Picture($picture_filename, $picture_tmp_name);

    $db = new \TB\Postgis(
      $_SERVER['DB_DRIVER'].':host='.$_SERVER['DB_HOST'].'; port='.$_SERVER['DB_PORT'].';dbname='.$_SERVER['DB_DATABASE'], 
      $_SERVER['DB_USER'], 
      $_SERVER['DB_PASSWORD'], 
      array(PDO::ATTR_PERSISTENT => true, PDO::ERRMODE_EXCEPTION => true)
    );

    $r = $db->readRoute($routeid);
    $r_centroid = $r->getCentroid();
    if (($tz = $db->getTimezone($r_centroid[0], $r_centroid[1])) == NULL)
      throw Exception("Error getting timezone");
    $dtz = new DateTimeZone($tz);
    $offset = $dtz->getOffset(DateTime::createFromFormat('U', $r->routepoints[0]->tags['datetime']));
    $pic->tags["datetime"] = intval($pic->tags["datetime"]) - $offset;
    var_dump ($r->getNearestPointByTime($pic->tags['datetime']));

    $picturesIds[] = $db->importPicture($routeid, $pic);
  }

  $slim->response();
  $res['Content-Type'] = 'application/json';
  $slim->render('ApiReplyView.php', array("value" => '{"picturesIds": '.json_encode($picturesIds).'}'), 200);
});

$slim->run();

?>
