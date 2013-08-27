<?php
use \ApiGuy;

$I = new ApiGuy($scenario);
$I->wantTo('Upload a .jpg file that is not a jpeg');
$I->SendPost('/v1/import/gpx', array(), array('gpxfile' => 'tests/_data/gruenewald/activity_317669633.gpx'));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$routeids = $I->grabDataFromJsonResponse('value.routeids');
$firstrouteid = $routeids[0];

$I->SendPost('/v1/route/'.$firstrouteid.'/medias/add', array(), array('medias' => array('tests/_data/badinput/gpx.jpg')));
$I->seeResponseCodeIs(400);
$I->seeResponseIsJson();
$I->seeResponseContains('not a valid jpeg file');

$I->SendDELETE('/v1/route/'.$firstrouteid);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
