<?php

namespace TB\Bundle\APIBundle\Service;

use TB\Bundle\FrontendBundle\Service\TwitterAPI;

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
                $images = [];
                if (property_exists($tweet, 'entities') && property_exists($tweet->entities, 'media')) {
                    foreach ($tweet->entities->media as $media) {
                        if ($media->type == 'photo') {
                            $images[] = $media->media_url;
                        }
                    }
                }
                
                $result[] = $this->formatItem(
                    $tweet->text, 
                    $date->format('Y-m-d H:i:s'), 
                    $tweet->user->name,
                    self::TYPE_TWITTER,
                    $images
                );
            }
        }
        
        return $result;
    }
    
    protected function formatItem($text, $date, $user, $type, array $images) 
    {
        return [
            'text' => $this->formatText($text),
            'date' => $date,
            'user' => $user,
            'type' => $type,
            'images' => $images,
        ];
    }
        
    protected function formatText($text) 
    {
        // replace URL's by a link tag
        $text = preg_replace("~((?:http|https|ftp)://(?:\S*?\.\S*?))(?=\s|\;|\)|\]|\[|\{|\}|,|\"|'|:|\<|$|\.\s)~i", '<a href="$1" target="_blank">$1</a>', $text);
        
        return $text;
    }
}
