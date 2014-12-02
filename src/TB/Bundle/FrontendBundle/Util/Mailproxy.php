<?php

namespace TB\Bundle\FrontendBundle\Util;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Guzzle\Http\Client;
use Mandrill;
use Mailchimp_Lists;
use Exception;

/**
 * 
 */
class Mailproxy
{
    protected $httpClient;
    
    protected $container;
    
    protected $mandrill;
    
    protected $mailchimpLists;
    
    public function __construct(ContainerInterface $container, Client $httpClient, Mandrill $mandrill, Mailchimp_Lists $mailchimpLists)
    {
        $this->container = $container;
        $this->httpClient = $httpClient;
        $this->mandrill = $mandrill;
        $this->mailchimpLists = $mailchimpLists;
    }
    
    public function addNewsletterSubscriber($email)
    {
        try {
            $data = [
                'email' => $email,
            ];
            $parameters = [
                'double_optin' => false,
                'send_welcome' => false,
            ];
            $this->mailchimpLists->subscribe($this->container->getParameter('mailchimp_newsletters_list_id'), $data, $parameters, 'html', false);    
            return true;
        } catch (Exception $e) {
            throw $e;
            return false;
        }
    }
    
    public function removeNewsletterSubscriber($email) 
    {
        try {
            $data = [
                'email' => $email,
            ];
            $this->mailchimpLists->unsubscribe($this->container->getParameter('mailchimp_newsletters_list_id'), $data);    
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function sendWelcomeMail($email, $firstname) 
    {
        try {
            $template_name = 'tb-welcome';
            $template_content = [];
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
