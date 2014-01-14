<?php

$I = new TestGuy($scenario);

$I->loadFixtures([
    'TB\Bundle\FrontendBundle\DataFixtures\ORM\UserProfileData',
    'TB\Bundle\FrontendBundle\DataFixtures\ORM\BrandProfileData',
]);
$I->wantTo('Default Trail page created by a User Profile');
$I->amOnPage('/trail/ttm');

