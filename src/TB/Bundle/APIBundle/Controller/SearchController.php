<?php

namespace TB\Bundle\APIBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class SearchController extends AbstractRestController
{
    
    /**
     * @Route("/search/suggest")
     * @Method("GET")
     */
    public function getSuggestAction()
    {
        $request = $this->getRequest();
        $client = $this->get('tb.elasticsearch.client');
        
        $body = '{
            "query": {
                "dis_max": {
                    "tie_breaker": 0.7,
                    "queries": [
                        {
                            "term": {"suggest_engram_full": "' . $request->query->get('q') . '" }
                        },
                        {
                            "term": {"suggest_engram_part": "' . $request->query->get('q') . '" }
                        },
                        {
                            "match" : { "text" : "' . $request->query->get('q') . '" }
                        }
                    ]
                 } 
             },
             "highlight": {
                 "pre_tags" : ["<strong>"],
                 "post_tags" : ["</strong>"],
                 "fields": {
                    "suggest_text": {
                        "number_of_fragments" : 1
                    },
                    "suggest_engram_full": {
                        "number_of_fragments" : 1
                    },
                    "suggest_engram_part": {
                        "number_of_fragments" : 1
                    }
               }
           }
        }';
        
        $params = ['index' => 'trailburning', 'body' => $body];

        $results = $client->search($params);

        return $this->getRestResponse($results);
    }
}
