<?php
require('Client.php');
require('GrantType/IGrantType.php');
require('GrantType/AuthorizationCode.php');

const CLIENT_ID     = 'cVN1pXW1Ok3lb7ePOuaCWc3GPIeSjrUb';
const CLIENT_SECRET = 'C1aQhnBC3bgrUzDn1vP46_uF8iO8kQ9UJZTSb6CH3MTCma9j3unun519A_DPlxR6';

const REDIRECT_URI           = 'http://www.eggontop.com/staging/trailburning/playground/moves/OAuth2/callback.php';
const AUTHORIZATION_ENDPOINT = 'moves://app/authorize';
const TOKEN_ENDPOINT         = 'https://api.moves-app.com/oauth/v1/access_token';

$client = new OAuth2\Client(CLIENT_ID, CLIENT_SECRET);
if (!isset($_GET['code']))
{
    $auth_url = $client->getAuthenticationUrl(AUTHORIZATION_ENDPOINT, REDIRECT_URI, array('scope' => 'activity location'));
//    $auth_url = $client->getAuthenticationUrl(AUTHORIZATION_ENDPOINT, REDIRECT_URI, array('scope' => 'activity'));
    header('Location: ' . $auth_url);
    die('Redirect');
}
else
{
    $params = array('code' => $_GET['code'], 'redirect_uri' => REDIRECT_URI);
    $response = $client->getAccessToken(TOKEN_ENDPOINT, 'authorization_code', $params);

    $client->setAccessToken($response['result']['access_token']);
    $response = $client->fetch('https://api.moves-app.com/api/1.1/user/profile');
    var_dump($response, $response['result']);
}
