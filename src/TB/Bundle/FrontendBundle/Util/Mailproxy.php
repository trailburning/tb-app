<?php

namespace TB\Bundle\FrontendBundle\Util;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Guzzle\Http\Client;
use Mandrill;

/**
 * 
 */
class Mailproxy
{
    protected $httpClient;
    
    public function __construct(ContainerInterface $container, Client $httpClient, Mandrill $mandrill)
    {
        $this->container = $container;
        $this->httpClient = $httpClient;
        $this->mandrill = $mandrill;
    }
    
    public function addNewsletterSubscriber($email)
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

        return true;
    }
    
    public function sendWelcomeMail($email, $firstname) 
    {
        try {
            $template_name = 'tb-welcome';
            $message = [
                'to' => [
                    [
                        'email' => $email
                    ]
                ],
                'merge' => true,
                'merge_language' => 'mailchimp',
                'global_merge_vars' => [
                    [
                        'name' => 'firstname',
                        'content' => $firstname
                    ]
                ],
            ];
            $async = false;
            $result = $this->mandrill->messages->sendTemplate($template_name, $template_content, $message, $async);
            
            return true;
        } catch (Mandrill_Error $e) {
            // Mandrill errors are thrown as exceptions
            // echo 'A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage();
            return false;
        }
    }
}
