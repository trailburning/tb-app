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
                if (property_exists($tweet, 'entities')) {
                    $text = $this->composeTwitterTextFromEntities($tweet->text, $tweet->entities);
                } else {
                    $text = $tweet->text;
                }
                
                $date = new \DateTime($tweet->created_at);
                $images = [];
                
                // we want to return images in an separate array
                if (property_exists($tweet, 'entities') && property_exists($tweet->entities, 'media')) {
                    foreach ($tweet->entities->media as $media) {
                        if ($media->type == 'photo') {
                            $images[] = $media->media_url;    
                        }
                    }
                }
                
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
            'text' => $text,
            'date' => $date,
            'user' => $user,
            'type' => $type,
            'images' => $images,
        ];      
    }           
                
    protected function composeTwitterTextFromEntities($text, $entities) 
    {           
        $supportedEntites = ['hashtags', 'media', 'user_mentions', 'urls'];
        $foundEntities = [];
                                       
        foreach ($supportedEntites as $entityType) {
            if (property_exists($entities, $entityType)) {
                foreach ($entities->$entityType as $entity) {
                    // there is a special case for media entities
                    if ($entityType == 'media') {
                        // only include media of type photo
                        if ($entity->type != 'photo') {
                            continue;
                        }
                        // we want to return photos in an separate array
                        $images[] = $entity->media_url;
                    }
                    
                    $foundEntities[$entity->indices[0]] = [
                        'type' => $entityType,
                        'entity' => $entity,
                    ];
                }
            }
        }
        ksort($foundEntities);
        
        $addedTextLength = 0;
        foreach ($foundEntities as $index => $value) {
            $type = $value['type'];
            $entity = $value['entity'];
            
            switch ($type) {
                case 'user_mentions':
                    $replacement = '<a href="https://twitter.com/' . $entity->name . '" target="_blank">@' . $entity->screen_name . '</a>';
                    $replacementLength = $entity->indices[1] - $entity->indices[0];
                    // consider previous replacements and the added text length
                    $text = $this->mb_substr_replace($text, $replacement, $entity->indices[0] + $addedTextLength, $replacementLength);
                    // save additional text length, to repplace at the correct index in a later replacement
                    $additionalLength = mb_strlen($replacement) - $replacementLength;
                    $addedTextLength += $additionalLength;
                    break;
                case 'urls':
                    $replacement = '<a href="' . $entity->expanded_url . '" target="_blank">' . $entity->display_url . '</a>';
                    $replacementLength = $entity->indices[1] - $entity->indices[0];
                    // consider previous replacements and the added text length
                    $text = $this->mb_substr_replace($text, $replacement, $entity->indices[0] + $addedTextLength, $replacementLength);
                    // save additional text length, to repplace at the correct index in a later replacement
                    $additionalLength = mb_strlen($replacement) - $replacementLength;
                    $addedTextLength += $additionalLength;
                    break;
                case 'media':
                    $replacement = '';
                    $replacementLength = $entity->indices[1] - $entity->indices[0];
                    // consider previous replacements and the added text length
                    $text = $this->mb_substr_replace($text, $replacement, $entity->indices[0] + $addedTextLength, $replacementLength);
                    // save additional text length, to repplace at the correct index in a later replacement
                    $additionalLength = mb_strlen($replacement) - $replacementLength;
                    $addedTextLength += $additionalLength;
                    break;
                case 'hashtags':
                    $replacement = '<a href="https://twitter.com/hashtag/' . $entity->text . '" target="_blank">#' . $entity->text . '</a>';
                    $replacementLength = $entity->indices[1] - $entity->indices[0];
                    // consider previous replacements and the added text length
                    $text = $this->mb_substr_replace($text, $replacement, $entity->indices[0] + $addedTextLength, $replacementLength);
                    // save additional text length, to repplace at the correct index in a later replacement
                    $additionalLength = mb_strlen($replacement) - $replacementLength;
                    $addedTextLength += $additionalLength;
                    break;
            }
        }
        
        return $text;   
    }
    
    protected function mb_substr_replace($string, $replacement, $start, $length=NULL) 
    {
        if (is_array($string)) {
            $num = count($string);
            // $replacement
            $replacement = is_array($replacement) ? array_slice($replacement, 0, $num) : array_pad(array($replacement), $num, $replacement);
            // $start
            if (is_array($start)) {
                $start = array_slice($start, 0, $num);
                foreach ($start as $key => $value)
                    $start[$key] = is_int($value) ? $value : 0;
            }
            else {
                $start = array_pad(array($start), $num, $start);
            }
            // $length
            if (!isset($length)) {
                $length = array_fill(0, $num, 0);
            }
            elseif (is_array($length)) {
                $length = array_slice($length, 0, $num);
                foreach ($length as $key => $value)
                    $length[$key] = isset($value) ? (is_int($value) ? $value : $num) : 0;
            }
            else {
                $length = array_pad(array($length), $num, $length);
            }
            // Recursive call
            return array_map(__FUNCTION__, $string, $replacement, $start, $length);
        }
        preg_match_all('/./us', (string)$string, $smatches);
        preg_match_all('/./us', (string)$replacement, $rmatches);
        if ($length === NULL) $length = mb_strlen($string);
        array_splice($smatches[0], $start, $length, $rmatches[0]);
        return join($smatches[0]);
    }
    
}