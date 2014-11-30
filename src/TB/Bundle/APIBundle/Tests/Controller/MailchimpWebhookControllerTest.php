<?php

namespace TB\Bundle\APIBundle\Tests\Controller;

use TB\Bundle\APIBundle\Tests\AbstractApiTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 *
 */
class WebhookMailchimpControllerTest extends AbstractApiTestCase
{
    
    /**
     * Test the GET /mailchimp/webhook action
     */
    public function testGetMailchimpWebhook()
    {
        $webhookKey = $this->getContainer()->getParameter('mailchimp_webhook_secret');
           
        $client = $this->createClient();
        $crawler = $client->request('GET', '/v1/mailchimp/webhook?key=' . $webhookKey);
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode());
    }
    
    public function testGetMailchimpWebhook404()
    {
        $webhookKey = 'invalidkey';
           
        $client = $this->createClient();
        $crawler = $client->request('GET', '/v1/mailchimp/webhook?key=' . $webhookKey);
        $this->assertEquals(Response::HTTP_NOT_FOUND,  $client->getResponse()->getStatusCode());
    }
    
    public function testPostMailchimpWebhook()
    {
        $webhookKey = $this->getContainer()->getParameter('mailchimp_webhook_secret');
        $client = $this->createClient();
           
        $data = [
            'type' => 'subscribe',
            'data' => [
                'email' => 'mattallbeury@trailburning.com',
            ],
        ];
        
        $crawler = $client->request('POST', '/v1/mailchimp/webhook?key=' . $webhookKey, $data);
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode());
    }

}
