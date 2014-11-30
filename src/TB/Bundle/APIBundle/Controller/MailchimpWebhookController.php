<?php

namespace TB\Bundle\APIBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Exception;

class MailchimpWebhookController extends AbstractRestController
{

    /**
     * @Route("/mailchimp/webhook")
     * @Method("GET")
     * @Template()
     */    
    public function getMailchimpWebhookAction(Request $request)
    {
        if ($request->query->get('key') != $this->container->getParameter('mailchimp_webhook_secret')) {
            $response = new Response();
            $response->setStatusCode(404);
            
            return $response;
        }
        
        return new Response();
    }
    
    /**
     * @Route("/mailchimp/webhook")
     * @Method("POST")
     * @Template()
     */    
    public function postMailchimpWebhookAction(Request $request)
    {
        if ($request->query->get('key') != $this->container->getParameter('mailchimp_webhook_secret')) {
            $response = new Response();
            $response->setStatusCode(404);
            
            return $response;
        }
        
        $webhook = $this->container->get('tb.mailchimp.webhook');
        
        if (!$request->request->has('type')) {
            $response = new Response('Missing field "type" in request data');
            $response->setStatusCode(400);
            
            return $response;
        }
        
        if (!$request->request->has('data')) {
            $response = new Response('Missing field "data" in request data');
            $response->setStatusCode(400);

            return $response;
        }
        
        try {
            $webhook->process($request->request->get('type'), $request->request->get('data'));
        } catch (Exception $e) {
            $response = new Response('Unable to process webhook');
            $response->setStatusCode(500);
            
            return $response;
        }
        
        return new Response();
    }
    
}
