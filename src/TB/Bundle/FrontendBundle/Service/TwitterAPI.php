<?php

namespace TB\Bundle\FrontendBundle\Service;

use TwitterAPIExchange;

class TwitterAPI
{

    private $twitterClient;

    public function __construct(TwitterAPIExchange $twitterClient) 
    {
        $this->twitterClient = $twitterClient;
    }
    
    public function searchTweets(array $parameters) 
    {
        $url = 'https://api.twitter.com/1.1/search/tweets.json';
        $requestMethod = 'GET';
        
        $response = $this->twitterClient
            ->setGetfield($this->buildGetField($parameters))
            ->buildOauth($url, $requestMethod)
            ->performRequest();
    
        return json_decode($response);
    }
    
    public function statusesUserTimeline(array $parameters) 
    {
        $url = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
        $requestMethod = 'GET';
        
        $response = $this->twitterClient
            ->setGetfield($this->buildGetField($parameters))
            ->buildOauth($url, $requestMethod)
            ->performRequest();
    
        return json_decode($response);
    }
    
    protected function buildGetField(array $parameters) 
    {
        $fields = [];
        foreach ($parameters as $key => $value) {
            $fields[] = $key . '=' . urlencode($value);
        }
        $getfield = '?' . implode($fields, '&');
        
        return $getfield;
    }

}

