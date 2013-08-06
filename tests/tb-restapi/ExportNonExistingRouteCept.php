<?php
use \ApiGuy;

$I = new ApiGuy($scenario);
$I->wantTo('Try to export a route that doesn\'t exist');
$I->SendGet('/v1/route/999999');
$I->seeResponseCodeIs(404);
$I->seeResponseIsJson();
