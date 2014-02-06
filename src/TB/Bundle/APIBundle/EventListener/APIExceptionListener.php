<?php 

namespace TB\Bundle\APIBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

use TB\Bundle\APIBundle\Util\ApiException;

class APIExceptionListener
{
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        
        // Customize your response object to display the exception details
        $response = new Response();
        
        if ($exception instanceof HttpExceptionInterface) {
            $response->setContent($event->getException()->getMessage());
            $response->setStatusCode($exception->getStatusCode());
            $response->headers->replace($exception->getHeaders());
        } elseif ($exception instanceof ApiException) {
            $output = array('usermsg' => 'error', "value" => null);
            $response->setContent(json_encode($event->getException()->getMessage()));
            $response->headers->set('Content-Type', 'application/json');
        } else {
            $response->setContent($event->getException()->getMessage());
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Send the modified response object to the event
        $event->setResponse($response);
    }
}