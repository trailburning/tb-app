<?php
use \ApiGuy;

$I = new ApiGuy($scenario);
$I->wantTo('Try to upload a picture to a routeid that does not exist');

$I->SendPost('/v1/route/99999/pictures/add', array(), array('pictures' => array('tests/_data/gruenewald/P5250773.jpg')));
$I->seeResponseCodeIs(404);
$I->seeResponseIsJson();
