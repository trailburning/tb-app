<?php

namespace TB\Bundle\APIBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ExceptionController extends AbstractRestController
{

    public function showAction(Request $request, $exception)
    {
        $output = array('usermsg' => $exception->getMessage(), "value" => null);
        
        return $this->getRestResponse($output, $exception->getCode());
    }
    
}
