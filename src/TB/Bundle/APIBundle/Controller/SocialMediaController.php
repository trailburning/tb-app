<?php

namespace TB\Bundle\APIBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;

use TB\Bundle\APIBundle\Util\ApiException;

class SocialMediaController extends AbstractRestController
{
    
    /**
     * @Route("/socialmedia")
     * @Method("GET")
     */
    public function getSearchAction(Request $request)
    {
        if (!$request->query->has('term')) {
            throw new ApiException('Missing mandatory parameter "term"', 400);
        }
        
        $term = $request->query->get('term');
        
        $socialMedia = $this->container->get('tb.socialmedia');
        
        $result = $socialMedia->search($term);
        $output = ['usermsg' => 'success', 'value' => $result];

       return $this->getRestResponse($output);
    }
}
