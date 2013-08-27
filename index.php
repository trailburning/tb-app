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
//$slim->log->setEnabled(true);

$slim->get('/v1/import/gpx', function () use ($slim) {
  $slim->render('ImportGpx.php');
});

$slim->post('/v1/import/gpx', function () use ($slim, $api_root, $conf_path) {
  require_once 'importers/GPX.php';

  if (!array_key_exists("gpxfile", $_FILES)) 
    throw (new \TB\ApiException("Gpxfile variable not set", 400));
  if ($_FILES['gpxfile']['error'] != 0) 
    throw (new \TB\ApiException("An error happened uploading the GPX file", 400));

  $gpx_filename = $_FILES["gpxfile"]["name"];
  $gpx_filename = preg_replace('/[^\w\-~_\.]+/u', '-', $gpx_filename);
  $gpx_tmp_path  = $_FILES["gpxfile"]["tmp_name"];

  $importer = new GPXImporter();
  try {
    $routes = $importer->parse(file_get_contents($gpx_tmp_path));
  } 
  catch (Exception $e) {
    throw (new \TB\ApiException("Problem parsing GPX file - not a valid GPX file?", 400));
  }

  $db = new \TB\Postgis(
    $_SERVER['DB_DRIVER'].':host='.$_SERVER['DB_HOST'].'; port='.$_SERVER['DB_PORT'].';dbname='.$_SERVER['DB_DATABASE'], 
    $_SERVER['DB_USER'], 
    $_SERVER['DB_PASSWORD'], 
    array(PDO::ATTR_PERSISTENT => true, PDO::ERRMODE_EXCEPTION => true)
  );

  $gpx_file_id = $db->importGpxFile('/trailburning-gpx/'.$gpx_tmp_path);
  $importedRoutesIds = array();
  foreach ($routes as $route){
    $route->setGpxFileId($gpx_file_id);
    $importedRoutesIds[] = $db->writeRoute($route);
  }

  $aws_client = \Aws\S3\S3Client::factory(array(
    'key'    => $_SERVER['AWS_ACCESSKEY'],
    'secret' => $_SERVER['AWS_SECRETKEY']
    ));

  $result = $aws_client->putObject(array(
      'Bucket' => 'trailburning-gpx',
      'Key'    => sha1_file($gpx_tmp_path).'.gpx',
      'Body'   => file_get_contents($gpx_tmp_path)
  ));

  $res = $slim->response();
  $res['Content-Type'] = 'application/json';
  $slim->render(
    'ApiReplyView.php', 
    array("value" => '{"routeids": '.json_encode($importedRoutesIds).'}', 'usermsg' => 'GPX successfully imports'), 
    200
  );
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
  $slim->render(
    'ApiReplyView.php', 
    array("value" => '{"route": '.$route->ToJSON().'}', 'usermsg' => 'success'), 
    200
  );
});

$slim->delete('/v1/route/:id', function ($routeid) use ($slim) {
  global $api_root, $conf_path;

  $db = new \TB\Postgis(
    $_SERVER['DB_DRIVER'].':host='.$_SERVER['DB_HOST'].'; port='.$_SERVER['DB_PORT'].';dbname='.$_SERVER['DB_DATABASE'], 
    $_SERVER['DB_USER'], 
    $_SERVER['DB_PASSWORD'], 
    array(PDO::ATTR_PERSISTENT => true, PDO::ERRMODE_EXCEPTION => true)
  );

  $route = $db->deleteRoute($routeid);
  $res = $slim->response();
  $res['Content-Type'] = 'application/json';
  $slim->render(
    'ApiReplyView.php', 
    array("value" => $routeid, 'usermsg' => 'success'), 
    200
  );
});

$slim->get('/v1/route/:id/pictures', function ($routeid) use ($slim) {
  $res = $slim->response();
  $res['Content-Type'] = 'application/json';

  $db = new \TB\Postgis(
    $_SERVER['DB_DRIVER'].':host='.$_SERVER['DB_HOST'].'; port='.$_SERVER['DB_PORT'].';dbname='.$_SERVER['DB_DATABASE'], 
    $_SERVER['DB_USER'], 
    $_SERVER['DB_PASSWORD'], 
    array(PDO::ATTR_PERSISTENT => true, PDO::ERRMODE_EXCEPTION => true)
  );

  $medias = $db->getRouteMedia($routeid);
  $slim->render(
    'ApiReplyView.php', 
    array("value" => json_encode($medias), 'usermsg' => 'success'), 
    200
  );
});

$slim->get('/v1/route/:id/pictures/add', function ($routeid) use ($slim) {
  $slim->render('PicturesNew.php', array('routeid' => $routeid));
});

$slim->post('/v1/route/:id/pictures/add', function ($routeid) use ($slim) {
  require_once 'JpegMedia.php';

  global $api_root, $conf_path;

  $slim = \Slim\Slim::getInstance();
  $res = $slim->response();
  $res['Content-Type'] = 'application/json';

  if (!array_key_exists('pictures', $_FILES)) 
    throw (new \TB\ApiException("Picture variable not set", 400));

  $mediasIds = array();

  $db = new \TB\Postgis(
    $_SERVER['DB_DRIVER'].':host='.$_SERVER['DB_HOST'].'; port='.$_SERVER['DB_PORT'].';dbname='.$_SERVER['DB_DATABASE'], 
    $_SERVER['DB_USER'], 
    $_SERVER['DB_PASSWORD'], 
    array(PDO::ATTR_PERSISTENT => true, PDO::ERRMODE_EXCEPTION => true)
  );

  $r = $db->readRoute($routeid);
  $r_centroid = $r->getCentroid();

  if (($tz = $db->getTimezone($r_centroid['long'], $r_centroid['lat'])) == NULL)
    throw new Exception("Error getting timezone");

  $dtz = new DateTimeZone($tz);
  $offset = $dtz->getOffset(DateTime::createFromFormat('U', $r->route_points[0]->tags['datetime']));

  for ($i=0; $i<count($_FILES['pictures']['name']); $i++) {
    if ($_FILES['pictures']['error'][$i] != 0)
      throw (new \TB\ApiException("An error happened uploading the picture", 400));    

    $picture_filename = preg_replace('/[^\w\-~_\.]+/u', '-', $_FILES["pictures"]["name"][$i]);
    $picture_tmp_path  =$_FILES["pictures"]["tmp_name"][$i]; 
    
    $media = new \TB\JpegMedia();
    $media->fromFile($picture_filename, $picture_tmp_path);
    $media->setTag("datetime", intval($media->getTag('datetime')) - $offset);

    $rp = $r->getNearestPointByTime($media->tags['datetime']);
    $media->setCoords($rp->coords['long'], $rp->coords['lat']);

    $media->setId($db->importPicture($media));
    $mediasIds[] = $media->getId();
    $db->attachMediaToRoute($routeid, $media);

    $aws_client = \Aws\S3\S3Client::factory(array(
      'key'    => $_SERVER['AWS_ACCESSKEY'],
      'secret' => $_SERVER['AWS_SECRETKEY']
    ));
    $result = $aws_client->putObject(array(
        'Bucket' => 'trailburning-media',
        'Key'    => sha1_file($picture_tmp_path).'.jpg',
        'Body'   => file_get_contents($picture_tmp_path),
        'ACL'    => 'public-read'
    ));
  }

  $slim->response();
  $res['Content-Type'] = 'application/json';
  $slim->render(
    'ApiReplyView.php', 
    array("value" => '{"picturesIds": '.json_encode($mediasIds).'}'), 
    200
  );
});

$slim->run();
?>
