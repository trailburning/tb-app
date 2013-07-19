<?php

ini_set('display_errors','1');
error_reporting(E_ALL);

$api_root = $_SERVER["DOCUMENT_ROOT"];
$conf_path = $api_root.'/config/';

$include_path = get_include_path().PATH_SEPARATOR.$api_root.'lib/'.PATH_SEPARATOR.$api_root.'vendor/';
set_include_path($include_path);

// Autoload auf 3rd-party dependancies installed by composer
require_once 'vendor/autoload.php';

require_once 'tbApiException.php';


\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();
$app->config(array('log.enable' => true,'debug' => true));


$app->get('/v1/route/import/gpx', function () {
	echo '<form action="/v1/route/import/gpx" method="post" enctype="multipart/form-data"><input type="file" name="gpxfile"><input type="submit"></form>';
	return;
});

$app->post('/v1/route/import/gpx', function () {
	require_once 'importers/GPX.php';
	require_once 'databases/postgis.php';

	global $api_root, $conf_path;
	$db_config = Spyc::YAMLLoad($conf_path.'database.yaml');
	$aws_config = Spyc::YAMLLoad($conf_path.'aws.yaml');

	$slim = \Slim\Slim::getInstance();
	$res = $slim->response();
	$res['Content-Type'] = 'application/json';

	try {
  	if (array_key_exists("gpxfile", $_FILES)) {
  		if ($_FILES['gpxfile']['error'] == 0) {
  			$gpx_filename = $_FILES["gpxfile"]["name"];
  			$gpx_filename = preg_replace('/[^\w\-~_\.]+/u', '-', $gpx_filename);
  			$gpx_targetpath  = $api_root.'/tmp/upload/gpx/'.$gpx_filename;
  			move_uploaded_file($_FILES["gpxfile"]["tmp_name"], $gpx_targetpath);
  
  			$gpximporter = new GPXImporter();
  			try {
  				$routes = $gpximporter->parse(file_get_contents($gpx_targetpath), "gpx");
  			}
  			catch (Exception $e) {
					throw (new tbApiException("Error parsing GPX file", 500));
				}
  
				/*$aws_client = \Aws\S3\S3Client::factory(array(
					'key'    => $aws_config['AWSAccessKeyId'],
			    'secret' => $aws_config['AWSSecretKey']
					));

        $result = $aws_client->putObject(array(
            'Bucket' => 'trailburning-gpx',
            'Key'    =>  $gpx_filename,
            'Body'   => file_get_contents($gpx_targetpath)
        ));*/
				
				try {
					$db = new TB\Postgis($db_config['driver'].':host='.$db_config['host'].';dbname='.$db_config['db'], $db_config['user'], $db_config['password'], array(PDO::ATTR_PERSISTENT => true, PDO::ERRMODE_EXCEPTION => true));
				}
				catch (PDOException $e) {
					throw (new tbApiException("Failed to establish connection to database.", 500));
				}

				try {
					$importedRoutesIds = array();
  				foreach ($routes as $route){
  					$importedRoutesIds[] = $db->importRoute($route);
  				}
				}
				catch (Exception $e) {
					throw (new tbApiException("Error importing routes in the database:".$e->message, "500"));
				}

				$res->status(200);
				$res->body('{message: "GPX successfully imported", routeids:'.json_encode($importedRoutesIds).'}');
  		}
  		else {
				throw (new tbApiException("Error uploading GPX File", 400));
  		}
  	}
  	else {
			throw (new tbApiException("Gpxfile variable not set", 400));
  	}
  }
	catch (tbApiException $e) {
		$res->status($e->getCode());
		$res->body($e);
    $logger = Logger::getLogger("main");
    $logger->warn($e);
	}
	catch (Exception $e) {
		$res->status(500);
		$res->body('{message: "Unknown Exception occured"}');
	}

  return;
});


$app->get('/v1/route/:id', function ($eventid) {
	require_once 'importers/GPX.php';
	require_once 'databases/postgis.php';
	
	global $api_root, $conf_path;
	$db_config = Spyc::YAMLLoad($conf_path.'database.yaml');

	try {
		$db = new TB\Postgis($db_config['driver'].':host='.$db_config['host'].';dbname='.$db_config['db'], $db_config['user'], $db_config['password'], array(PDO::ATTR_PERSISTENT => true, PDO::ERRMODE_EXCEPTION => true));
	}
	catch (PDOException $e) {
		throw (new tbApiException("Failed to establish connection to database.", 500));
	}

	$jsonroute = $db->exportRoute($eventid, "json");

	$slim = \Slim\Slim::getInstance();
	$res = $slim->response();
	$res['Content-Type'] = 'application/json';
	$res->status(200);
	$res->body($jsonroute);
});


$app->run();


?>
