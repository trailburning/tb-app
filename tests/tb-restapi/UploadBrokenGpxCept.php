<?php
use \ApiGuy;

$I = new ApiGuy($scenario);
$I->wantTo('check if the API behaves correctly with broken/non GPX route file uploads');
$I->SendPost('/v1/route/import/gpx', array(), array('gpxfile' => 'tests/_data/badinput/brokengpx.gpx'));
$I->seeResponseCodeIs(400);
$I->seeResponseIsJson();
$I->SendPost('/v1/route/import/gpx', array(), array('gpxfile' => 'tests/_data/yann.jpg'));
$I->seeResponseCodeIs(400);
$I->seeResponseIsJson();
$I->SendPost('/v1/route/import/gpx', array(), array('gpxfile' => ''));
$I->seeResponseCodeIs(400);
$I->seeResponseIsJson();
$I->SendPost('/v1/route/import/gpx');
$I->seeResponseCodeIs(400);
$I->seeResponseIsJson();
