<?php
use \ApiGuy;

$I = new ApiGuy($scenario);
$I->wantTo('Try to upload a media to a routeid that does not exist');

$I->SendPost('/v1/route/99999/medias/add', array(), array('medias' => array('tests/_data/gruenewald/P5250773.jpg')));
$I->seeResponseCodeIs(404);
$I->seeResponseIsJson();
