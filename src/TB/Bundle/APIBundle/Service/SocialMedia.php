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
                $text = $tweet->text;
                $date = new \DateTime($tweet->created_at);
                $images = [];
                
                
                // TODO: move twitter entities text composition to other class or method and add tests
                if (property_exists($tweet, 'entities')) {
                    $entities = $tweet->entities;
                    $textAdditionsAtIndex = [];
                    
                    echo $text . "\n";
                    
                    if (property_exists($entities, 'hashtags')) {
                        foreach ($entities->hashtags as $hashtag) {
                            $replacement = '<a href="https://twitter.com/hashtag/' . $hashtag->text . '" target="_blank">#' . $hashtag->text . '</a>';
                            $replacementLength = $hashtag->indices[1] - $hashtag->indices[0];
                            
                            $replacementIndex = $hashtag->indices[0];
                            
                            // find text additions made before this entities index, and add the added letters count to the index
                            $modifiedReplacementIndex = $replacementIndex;
                            foreach ($textAdditionsAtIndex as $index => $value) {
                                if ($index < $replacementIndex) {
                                    $modifiedReplacementIndex += $value;
                                }
                            }
                            
                            $text = substr_replace($text, $replacement, $hashtag->indices[0], $replacementLength);
                            $textAdditionsAtIndex[$hashtag->indices[0]] = strlen($replacement) - $replacementLength;
                        }
                    }
                    
                    if (property_exists($entities, 'user_mentions')) {
                        foreach ($entities->user_mentions as $userMention) {
                            $replacement = '<a href="https://twitter.com/' . $userMention->name . '" target="_blank">@' . $userMention->screen_name . '</a>';
                            $replacementLength = $userMention->indices[1] - $userMention->indices[0];
                            $replacementIndex = $userMention->indices[0];
                            
                            // find text additions made before this entities index, and add the added letters count to the index
                            $modifiedReplacementIndex = $replacementIndex;
                            foreach ($textAdditionsAtIndex as $index => $value) {
                                if ($index < $replacementIndex) {
                                    $modifiedReplacementIndex += $value;
                                }
                            }
                            
                            $text = substr_replace($text, $replacement, $modifiedReplacementIndex, $replacementLength);
                            $textAdditionsAtIndex[$userMention->indices[0]] = strlen($replacement) - $replacementLength;

                        }
                    }  
                    
                    if (property_exists($entities, 'urls')) {
                        foreach ($entities->urls as $url) {
                            $replacement = '<a href="' . $url->expanded_url . '" target="_blank">' . $url->display_url . '</a>';
                            $replacementLength = $url->indices[1] - $url->indices[0];
                            $replacementIndex = $url->indices[0];
                            
                            // find text additions made before this entities index, and add the added letters count to the index
                            $modifiedReplacementIndex = $replacementIndex;
                            foreach ($textAdditionsAtIndex as $index => $value) {
                                if ($index < $replacementIndex) {
                                    $modifiedReplacementIndex += $value;
                                }
                            }
                            
                            $text = substr_replace($text, $replacement, $modifiedReplacementIndex, $replacementLength);
                            $textAdditionsAtIndex[$url->indices[0]] = strlen($replacement) - $replacementLength;
                        }
                    }  
                                    
                    if (property_exists($entities, 'media')) {
                        foreach ($entities->media as $media) {
                            if ($media->type == 'photo') {
                                $replacement = '';
                                $replacementLength = $media->indices[1] - $media->indices[0];
                                $replacementIndex = $media->indices[0];
                            
                                // find text additions made before this entities index, and add the added letters count to the index
                                $modifiedReplacementIndex = $replacementIndex;
                                foreach ($textAdditionsAtIndex as $index => $value) {
                                    if ($index < $replacementIndex) {
                                        $modifiedReplacementIndex += $value;
                                    }
                                }
                            
                                $text = substr_replace($text, $replacement, $modifiedReplacementIndex, $replacementLength);
                                $textAdditionsAtIndex[$media->indices[0]] = strlen($replacement) - $replacementLength;
                                
                                
                                $images[] = $media->media_url;
                            }
                        }
                    }    
                }
                echo $text . "\n";
                exit;
                
                var_export($tweet->entities);
                exit;
                
                $result[] = $this->formatItem(
                    $text, 
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
