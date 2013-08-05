<?php
use \ApiGuy;

$I = new ApiGuy($scenario);
$I->wantTo('Upload a GPX track and attach pictures to it');
$I->SendPost('/v1/route/import/gpx', array(), array('gpxfile' => 'tests/_data/gruenewald/activity_317669633.gpx'));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$routeids = $I->grabDataFromJsonResponse('value.routeids');
$firstrouteid = $routeids[0];
$I->SendPost('/v1/route/'.$firstrouteid.'/pictures/add', array(), array('pictures' => array('tests/_data/gruenewald/P5250773.jpg', 'tests/_data/gruenewald/P5250783.jpg')));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
