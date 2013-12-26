<?php

require_once 'init.php';

use TBAPI\databases\Postgis;
use TBAPI\exceptions\ApiException;
use TBAPI\importers\GPXImporter;
use TBAPI\entities\JpegMedia;
use Slim\Slim;
use Slim\Log;
use Aws\S3\S3Client;

function getDB()
{
    $db = new Postgis(
        $_SERVER['DB_DRIVER'].':host='.$_SERVER['DB_HOST'].'; port='.$_SERVER['DB_PORT'].';dbname='.$_SERVER['DB_DATABASE'],
        $_SERVER['DB_USER'],
        $_SERVER['DB_PASSWORD'],
        array(\PDO::ATTR_PERSISTENT => true, \PDO::ERRMODE_EXCEPTION => true)
    );

    return $db;
}

$slim = new Slim();

// Debug needs to be set to false for our custom exception handlers to be called
$slim->config(array('log.enable' => true, 'debug' => false)); 
$slim->error(function(\Exception $e) {
    $slim = Slim::getInstance();
    $res = $slim->response();
    $res['Content-Type'] = 'application/json';
    
    try {
        throw $e;
    } catch (ApiException $e) {
        switch (intval($e->getCode())) {
            case 400:
            case 404:
                $slim->log->notice($e->__toString());
                break;
            case 500:
                $slim->log->error($e->__toString());
                break;
        }
        $res->status($e->getCode());
        $res->body($e);
    } catch (\Exception $e) {
        $slim->getLog()->error($e->__toString());
        $res->status(500);
        $res->body('{"message": "An exception has occured", "value": '.json_encode($e->__toString()).'}');
    }
}); 
$slim->log->setLevel(Log::DEBUG);

$slim->get('/v1/import/gpx', function() use ($slim) {
    $slim->render('ImportGpx.php');
});

$slim->post('/v1/import/gpx', function() use ($slim) {

    if (!array_key_exists("gpxfile", $_FILES)) {
        throw (new ApiException("Gpxfile variable not set", 400));
    }
    if ($_FILES['gpxfile']['error'] != 0) {
        throw (new ApiException("An error happened uploading the GPX file", 400));
    }
    $gpx_filename = preg_replace('/[^\w\-~_\.]+/u', '-', $_FILES["gpxfile"]["name"]);
    $gpx_tmp_path = $_FILES["gpxfile"]["tmp_name"];

    $importer = new GPXImporter();
    try {
        $routes = $importer->parse(file_get_contents($gpx_tmp_path));
    } catch (\Exception $e) {
        throw (new ApiException("Problem parsing GPX file - not a valid GPX file?", 400));
    }

    $db = getDB();

    $gpx_file_id = $db->importGpxFile('/trailburning-gpx/'.$gpx_tmp_path);
    $importedRoutesIds = array();
    foreach ($routes as $route) {
        $route->setGpxFileId($gpx_file_id);
        $importedRoutesIds[] = $db->writeRoute($route);
    }

    $aws_client = S3Client::factory(array(
        'key'    => $_SERVER['AWS_ACCESSKEY'],
        'secret' => $_SERVER['AWS_SECRETKEY']
    ));

    $result = $aws_client->putObject(array(
        'Bucket'    => 'trailburning-gpx',
        'Key'       => sha1_file($gpx_tmp_path).'.gpx',
        'Body'      => file_get_contents($gpx_tmp_path)
    ));

    $res = $slim->response();
    $res['Content-Type'] = 'application/json';
    $slim->render(
        'ApiReplyView.php', 
        array("value" => '{"route_ids": '.json_encode($importedRoutesIds).'}', 'usermsg' => 'GPX successfully imports'), 
        200
    );
});

$slim->get('/v1/route/:id', function($route_id) use ($slim) {
    $db = getDB();
    $route = $db->readRoute($route_id);
    $res = $slim->response();
    $res['Content-Type'] = 'application/json';
    $slim->render(
        'ApiReplyView.php', 
        array("value" => '{"route": '.$route->ToJSON().'}', 'usermsg' => 'success'), 
        200
    );
});

$slim->delete('/v1/route/:id', function($route_id) use ($slim) {
    $db = getDB();

    $route = $db->deleteRoute($route_id);
    $res = $slim->response();
    $res['Content-Type'] = 'application/json';
    $slim->render(
        'ApiReplyView.php', 
        array("value" => $route_id, 'usermsg' => 'success'), 
        200
    );
});

$slim->get('/v1/route/:id/medias', function($route_id) use ($slim) {
    $db = getDB();

    $medias = $db->getRouteMedia($route_id);

    $res = $slim->response();
    $res['Content-Type'] = 'application/json';
    $slim->render(
        'ApiReplyView.php', 
        array("value" => json_encode($medias), 'usermsg' => 'success'), 
        200
    );
});

$slim->get('/v1/route/:id/medias/add', function($route_id) use ($slim) {
    $slim->render('MediasNew.php', array('routeid' => $route_id));
});

$slim->post('/v1/route/:id/medias/add', function($route_id) use ($slim) {

    if (!array_key_exists('medias', $_FILES)) {
        throw (new ApiException("Medias variable not set", 400));
    }

    $db = getDB();

    $r = $db->readRoute($route_id);

    $r_centroid = $r->getCentroid();
    if (($tz = $db->getTimezone($r_centroid['long'], $r_centroid['lat'])) == NULL) {
        throw new \Exception("Error getting timezone");     
    }

    $dtz = new \DateTimeZone($tz);
    $offset = $dtz->getOffset(\DateTime::createFromFormat('U', $r->route_points[0]->tags['datetime']));

    $medias = array();
    for ($i = 0; $i < count($_FILES['medias']['name']); $i++) {
        if ($_FILES['medias']['error'][$i] != 0) {
            throw (new \ApiException("An error happened uploading the medias", 400));
        }

        $media_filename = preg_replace('/[^\w\-~_\.]+/u', '-', $_FILES["medias"]["name"][$i]);
        switch (strtolower(pathinfo($media_filename, PATHINFO_EXTENSION))) {
            case "jpg":
            case "jpeg":
                $media = new JpegMedia();
                break;
            default:
                throw (new ApiException("Tried to upload file with non recognised extension", 400));        
                break;
        }

        $media_tmp_path = $_FILES["medias"]["tmp_name"][$i]; 
        
        $media->fromFile($media_filename, $media_tmp_path);
        $media->setTag("datetime", intval($media->getTag('datetime')) - $offset);

        $rp = $r->getNearestPointByTime($media->tags['datetime']);
        $media->setCoords($rp->coords['long'], $rp->coords['lat']);
        if ($rp->getTag('altitude') != null) {
            $media->setTag('altitude', $rp->getTag('altitude'));
        }

        $db->importPicture($media);
        $db->attachMediaToRoute($route_id, $media);

        $aws_client = \Aws\S3\S3Client::factory(array(
            'key'         => $_SERVER['AWS_ACCESSKEY'],
            'secret'      => $_SERVER['AWS_SECRETKEY']
        ));
        $result = $aws_client->putObject(array(
            'Bucket'      => 'trailburning-media',
            'Key'         => sha1_file($media_tmp_path).'.jpg',
            'Body'        => file_get_contents($media_tmp_path),
            'ContentType' => $media->mimetype,
            'ACL'         => 'public-read'
        ));

        $medias[] = $media;
    }

    $slim->response();
    $res['Content-Type'] = 'application/json';
    $slim->render(
        'ApiReplyView.php', 
        array("value" => json_encode($medias), 'usermsg' => 'success'), 
        200
    );
});


/* Please note: this doesn't actually remove the file from Amazon S3 */
$slim->delete('/v1/media/:id', function($media_id) use ($slim) {
    $db = getDB();

    $media = $db->deleteMedia($media_id);
    $res = $slim->response();
    $res['Content-Type'] = 'application/json';
    $slim->render(
        'ApiReplyView.php',
        array("value" => $media_id, 'usermsg' => 'success'),
        200
    );
});

$slim->get('/v1/routes/user/:id', function($route_id) use ($slim) {

    $db = getDB();

    $routes = $db->readRoutes($route_id, 10);
    $res = $slim->response();
    $res['Content-Type'] = 'application/json';
    $json_routes = array();
    foreach ($routes as $route) {
        $json_routes[] = $route->toJSON();
    }
    $slim->render(
        'ApiReplyView.php', 
        array("value" => '{"routes": ['. implode(',', $json_routes).']}', 'usermsg' => 'success'), 
        200
    );
});

// return HTTP 200 for HTTP OPTIONS requests
$slim->map('/:x+', function($x) {
    http_response_code(200);
})->via('OPTIONS');

$slim->run();

