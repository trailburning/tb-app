<?php
use \ApiGuy;

$I = new ApiGuy($scenario);
$I->wantTo('attach to a route an image that has not been taken during the GPX recording');
$I->SendPost('/v1/import/gpx', array(), array('gpxfile' => 'tests/_data/gruenewald/activity_317669633.gpx'));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$routeids = $I->grabDataFromJsonResponse('value.routeids');
$firstrouteid = $routeids[0];

$I->SendPost('/v1/route/'.$firstrouteid.'/pictures/add', array(), array('pictures' => array('tests/_data/yann.jpg')));
$I->seeResponseCodeIs(400);
$I->seeResponseIsJson();

$I->SendDELETE('/v1/route/'.$firstrouteid);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
