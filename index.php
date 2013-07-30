<?php

require_once 'lib/init.php';  // Set error level, include_paths, etc.
require_once 'ApiException.php';
require_once 'ExceptionHandling.php';
// Autoload of 3rd-party dependancies installed by composer
require_once 'vendor/autoload.php';

\Slim\Slim::registerAutoloader();
$slim = new \Slim\Slim();
// Debug needs to be set to false for our custom exception handlers to be called
$slim->config(array('log.enable' => true,'debug' => false)); 
$slim->error(function (\Exception $e) { \TB\handleException($e);} ); 

$slim->get('/v1/route/import/gpx', function () {
  echo '<form action="/v1/route/import/gpx" method="post" enctype="multipart/form-data"><input type="file" name="gpxfile"><input type="submit"></form>';
  return;
});

$slim->post('/v1/route/import/gpx', function () {
  require_once 'importers/GPX.php';
  require_once 'databases/postgis.php';

  global $api_root, $conf_path;
  //$db_config = Spyc::YAMLLoad($conf_path.'database.yaml');
  //$aws_config = Spyc::YAMLLoad($conf_path.'aws.yaml');

  $slim = \Slim\Slim::getInstance();

  if (!array_key_exists("gpxfile", $_FILES)) 
    throw (new \TB\ApiException("Gpxfile variable not set", 400));
  if ($_FILES['gpxfile']['error'] != 0) 
    throw (new \TB\ApiException("An error happened uploading the GPX file", 400));

  $gpx_filename = $_FILES["gpxfile"]["name"];
  $gpx_filename = preg_replace('/[^\w\-~_\.]+/u', '-', $gpx_filename);
  //$gpx_targetpath  = $api_root.'/tmp/'.$gpx_filename;
  $gpx_targetpath  = $_FILES["gpxfile"]["tmp_name"];
  //move_uploaded_file($_FILES["gpxfile"]["tmp_name"], $gpx_targetpath);

  $gpximporter = new GPXImporter();
  $routes = $gpximporter->parse(file_get_contents($gpx_targetpath), "gpx");
  
  //$db = new \TB\Postgis($db_config['driver'].':host='.$db_config['host'].';dbname='.$db_config['db'], $db_config['user'], $db_config['password'], array(PDO::ATTR_PERSISTENT => true, PDO::ERRMODE_EXCEPTION => true));
  $db = new \TB\Postgis(
    $_SERVER['DB_DRIVER'].':host='.$_SERVER['DB_HOST'].'; port='.$_SERVER['DB_PORT'].';dbname='.$_SERVER['DB_DATABASE'], 
    $_SERVER['DB_USER'], 
    $_SERVER['DB_PASSWORD'], 
    array(PDO::ATTR_PERSISTENT => true, PDO::ERRMODE_EXCEPTION => true)
  );
/*
  $aws_client = \Aws\S3\S3Client::factory(array(
    'key'    => $aws_config['AWSAccessKeyId'],
    'secret' => $aws_config['AWSSecretKey']
    ));
  $result = $aws_client->putObject(array(
      'Bucket' => 'trailburning-gpx',
      'Key'    =>  $gpx_filename,
      'Body'   => file_get_contents($gpx_targetpath)
  ));
*/
  $gpxfileid = $db->importGpxFile('s3://trailburning-gpx/'.$gpx_filename);
  $importedRoutesIds = array();
  foreach ($routes as $route){
    $importedRoutesIds[] = $db->writeRoute($gpxfileid, $route);
  }

  $slim = \Slim\Slim::getInstance();
  $res = $slim->response();
  $res['Content-Type'] = 'application/json';
  $res->status(200);
  $res->body('{message: "GPX successfully imported", routeids:'.json_encode($importedRoutesIds).'}');
});

$slim->get('/v1/route/:id', function ($routeid) {
  require_once 'importers/GPX.php';
  require_once 'databases/postgis.php';
  
  global $api_root, $conf_path;
  //$db_config = Spyc::YAMLLoad($conf_path.'database.yaml');

  $db = new \TB\Postgis(
    $_SERVER['DB_DRIVER'].':host='.$_SERVER['DB_HOST'].'; port='.$_SERVER['DB_PORT'].';dbname='.$_SERVER['DB_DATABASE'], 
    $_SERVER['DB_USER'], 
    $_SERVER['DB_PASSWORD'], 
    array(PDO::ATTR_PERSISTENT => true, PDO::ERRMODE_EXCEPTION => true)
  );
/*
  $db = new \TB\Postgis(
    $db_config['driver'].':host='.$db_config['host'].';dbname='.$db_config['db'], 
    $db_config['user'], 
    $db_config['password'], 
    array(PDO::ATTR_PERSISTENT => true, PDO::ERRMODE_EXCEPTION => true)
  );
*/
  $route = $db->readRoute($routeid);
  $slim = \Slim\Slim::getInstance();
  $res = $slim->response();
  $res['Content-Type'] = 'application/json';
  $res->status(200);
  $res->body('{message: "Success", route:'.$route->ToJSON().'}');
});

$slim->get('/v1/route/:id/pictures/new', function ($routeid) {
  echo '<form action="/v1/route/'.$routeid.'/pictures/new" method="post" enctype="multipart/form-data"><input type="file" name="pictures[]" multiple><input type="submit"></form>';
});

$slim->post('/v1/route/:id/pictures/new', function ($routeid) {
  require_once 'Picture.php';
  require_once 'databases/postgis.php';

  global $api_root, $conf_path;
  //$db_config = Spyc::YAMLLoad($conf_path.'database.yaml');
  //$aws_config = Spyc::YAMLLoad($conf_path.'aws.yaml');

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
    $picture_targetpath  = $api_root.'/tmp/'.$picture_filename;
    move_uploaded_file($_FILES["pictures"]["tmp_name"][$i], $picture_targetpath);
    /*
    $aws_client = \Aws\S3\S3Client::factory(array(
      'key'    => $aws_config['AWSAccessKeyId'],
      'secret' => $aws_config['AWSSecretKey']
      ));
    $result = $aws_client->putObject(array(
        'Bucket' => 'trailburning-media',
        'Key'    =>  $picture_filename,
        'Body'   => file_get_contents($picture_targetpath)
    ));
*/
    $pic = new \TB\Picture($picture_filename, $picture_targetpath);
   // $db = new \TB\Postgis($db_config['driver'].':host='.$db_config['host'].';dbname='.$db_config['db'], $db_config['user'], $db_config['password'], array(PDO::ATTR_PERSISTENT => true, PDO::ERRMODE_EXCEPTION => true));

    $db = new \TB\Postgis(
      $_SERVER['DB_DRIVER'].':host='.$_SERVER['DB_HOST'].'; port='.$_SERVER['DB_PORT'].';dbname='.$_SERVER['DB_DATABASE'], 
      $_SERVER['DB_USER'], 
      $_SERVER['DB_PASSWORD'], 
      array(PDO::ATTR_PERSISTENT => true, PDO::ERRMODE_EXCEPTION => true)
    );
    $picturesIds[] = $db->importPicture($routeid, $pic);
  }

  $slim = \Slim\Slim::getInstance();
  $res = $slim->response();
  $res['Content-Type'] = 'application/json';
  $res->status(200);
  $res->body("{picturesIds: ".json_encode($picturesIds)."}");
});


$slim->run();


?>
