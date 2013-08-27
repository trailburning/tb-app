<?php
use \ApiGuy;

$I = new ApiGuy($scenario);
$I->wantTo('Upload a GPX track, export the first route, attach pictures to it and delete it');
$I->SendPost('/v1/import/gpx', array(), array('gpxfile' => 'tests/_data/gruenewald/activity_317669633.gpx'));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$route_ids = $I->grabDataFromJsonResponse('value.route_ids');
$first_route_id = $route_ids[0];

$I->SendGet('/v1/route/'.$first_route_id);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();

$I->SendPost('/v1/route/'.$first_route_id.'/medias/add', array(), array('medias' => array('tests/_data/gruenewald/P5250773.jpg', 'tests/_data/gruenewald/P5250783.jpg')));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();

$I->SendGet('/v1/route/'.$first_route_id.'/medias');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();

$I->SendDELETE('/v1/route/'.$first_route_id);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
