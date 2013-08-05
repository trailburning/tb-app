<?php
use \ApiGuy;

$I = new ApiGuy($scenario);
$I->wantTo('Upload a GPX track and attach pictures to it');
$I->SendPost('/v1/route/import/gpx', array(), array('gpxfile' => 'tests/_data/gruenewald/activity_317669633.gpx'));
$routeids = $I->grabDataFromJsonResponse('value.routeids');
$firstrouteid = $routeids[0];
$I->SendGet('/v1/route/'.$firstrouteid);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
