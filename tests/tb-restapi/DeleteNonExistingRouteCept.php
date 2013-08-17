<?php
use \ApiGuy;

$I = new ApiGuy($scenario);
$I->wantTo('Delete a route that doesn\'t exist');
$I->SendDELETE('/v1/route/999999');
$I->seeResponseCodeIs(404);
$I->seeResponseIsJson();
