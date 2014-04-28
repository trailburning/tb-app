<?php

namespace TB\Bundle\FrontendBundle\Util;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Guzzle\Http\Client;

/**
 * 
 */
class Mailproxy
{
    protected $httpClient;
    
    public function __construct(ContainerInterface $container, Client $httpClient)
    {
        $this->container = $container;
        $this->httpClient = $httpClient;
    }
    
    public function post($email)
    {
        $file = @file_get_contents($this->container->getParameter('mailproxy_email_server'), NULL, stream_context_create(array('http' => array('method' => 'POST', 'content' => http_build_query([
                'cm-zjdkk-zjdkk' => $email,
            ])))));
        
        // $request = $this->httpClient->post($this->container->getParameter('mailproxy_email_server'), [
//             'body' => [
//                 'cm-zjdkk-zjdkk' => 'trost@cynova.net',
//             ],
//         ]);
//         $response = $request->send();
    }
}
