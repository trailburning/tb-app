<?php
use \ApiGuy;

$I = new ApiGuy($scenario);
$I->wantTo('attach to a route an image that has not been taken during the GPX recording');
$I->SendPost('/v1/import/gpx', array(), array('gpxfile' => 'tests/_data/gruenewald/activity_317669633.gpx'));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$route_ids = $I->grabDataFromJsonResponse('value.route_ids');
$first_route_id = $route_ids[0];

$I->SendPost('/v1/route/'.$first_route_id.'/medias/add', array(), array('medias' => array('tests/_data/yann.jpg')));
$I->seeResponseCodeIs(400);
$I->seeResponseIsJson();

$I->SendDELETE('/v1/route/'.$first_route_id);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
