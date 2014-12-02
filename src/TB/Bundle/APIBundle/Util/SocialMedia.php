<?php

namespace TB\Bundle\APIBundle\Util;

use TB\Bundle\FrontendBundle\Util\TwitterAPI;

class SocialMedia
{
    const TYPE_TWITTER = 'twitter';
    
    protected $twitterAPI;
    
    public function __construct(TwitterAPI $twitterAPI) 
    {
        $this->twitterAPI = $twitterAPI;
    }
    
    public function search($term) 
    {   
        $twitterResult = $this->twitterAPI->searchTweets([
            'q' => $term, 
            'result_type' => 'recent', 
            'count' => 3,
            'lang' => 'en',
        ]);
        
        $result = [];
        
        if (isset($twitterResult->statuses)) {
            foreach ($twitterResult->statuses as $tweet) {
                $date = new \DateTime($tweet->created_at);
                $result[] = $this->formatItem(
                    $tweet->text, 
                    $date->format('Y-m-d H:i:s'), 
                    $tweet->user->name,
                    self::TYPE_TWITTER
                );
            }
        }
        
        return $result;
    }
    
    protected function formatItem($text, $date, $user, $type) 
    {
        return [
            'text' => $text,
            'date' => $date,
            'user' => $user,
            'type' => $type,
        ];
    }
    
}
