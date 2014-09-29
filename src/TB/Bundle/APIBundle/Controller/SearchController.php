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
            query: {
                match: {
                  suggest_nge: "' . $request->query->get('q') . '"
                }
            }
        }';
        
        $params = ['index' => 'trailburning', 'body' => $body];

        $results = $client->search($params);

        return $this->getRestResponse($results);
    }
}
