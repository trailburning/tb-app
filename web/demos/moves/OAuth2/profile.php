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

$access_token = 'J9yH2yh9AO7w2CqMAS32M6NxzoJGEc5MeeY7Rhx5ycqoi3T8xNpnM6HBy2O6k6wp'; // mallbeury
$client->setAccessToken($access_token);
//$response = $client->fetch('https://api.moves-app.com/api/1.1/user/profile');

$params = array('trackPoints' => 'true');
$response = $client->fetch('https://api.moves-app.com/api/1.1/user/storyline/daily/20150224', $params);

$segments = $response['result'][0]['segments'];

echo '<?xml version="1.0"?><gpx creator="Trailburning http://www.trailburning.com/"><trk><name>Moves Test</name><trkseg>';

foreach($segments as $segment) { //foreach element in $arr	
	if ($segment['type'] == 'move') {
		$points = $segment['activities'][0]['trackPoints'];
		foreach($points as $point) {
    		echo '<trkpt lat="' . $point['lat'] . '" lon="' . $point['lon'] . '"><ele>0</ele><time>' . $point['time'] . '</time></trkpt>';

		}
	}
}

echo '</trkseg></trk></gpx>';

//$json =  json_encode($response['result']);
//echo $json;
