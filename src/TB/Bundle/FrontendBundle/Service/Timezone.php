<?php 

namespace TB\Bundle\FrontendBundle\Service;
    
use Guzzle\Http\Client;
use Exception;

/**
 * 
 */
class Timezone
{
    
    protected $httpClient;
    
    function __construct(Client $httpClient)
    {
        $this->httpClient = $httpClient;
    }
    
    public function getTimezoneForGeoPoint($long, $lat, $timestamp) 
    {
        $url = sprintf('https://maps.googleapis.com/maps/api/timezone/json?location=%s,%s&timestamp=%s', $lat, $long, $timestamp);
        
        $request = $this->httpClient->get($url);
        $request->send();
        $response = $request->getResponse();
        
        if ($response->getStatusCode() !== 200) {
            throw new Exception('Unable to get timezone from google timezone api, http status code %s for URL: %s', $response->getStatusCode(), $url);
        }
        
        $timezone = $response->json()['timeZoneId'];
        
        return $timezone;
    }
}