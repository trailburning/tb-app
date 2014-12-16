<?php

namespace TB\Bundle\APIBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;

use TB\Bundle\APIBundle\Service\ApiException;

class SocialMediaController extends AbstractRestController
{
    
    /**
     * @Route("/socialmedia")
     * @Method("GET")
     */
    public function getSocialmediaAction(Request $request)
    {
        $socialMedia = $this->container->get('tb.socialmedia');        
        if ($request->query->has('term')) {
            $term = $request->query->get('term');
            $result = $socialMedia->search($term);
        } elseif ($request->query->has('user')) {
            $user = $request->query->get('user');
            $result = $socialMedia->timeline($user);
        } else {
            throw new ApiException('Either "term" or "user" must be specified as parameter', 400);
        }
        
        $output = ['usermsg' => 'success', 'value' => $result];

       return $this->getRestResponse($output);
    }
}
