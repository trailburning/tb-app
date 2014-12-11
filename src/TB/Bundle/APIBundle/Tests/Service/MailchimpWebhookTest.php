<?php 

namespace TB\Bundle\APIBundle\Tests\Service;

use TB\Bundle\APIBundle\Tests\AbstractApiTest;

class MailchimpWebhookTest extends AbstractApiTest
{

    public function testProcessSubscribe()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\UserProfileData',
        ]); 
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        
        $user = $this->getUser('mattallbeury');
        $user->setNewsletter(false);
        $em->persist($user);
        $em->flush();
        
        $webhook = $this->getContainer()->get('tb.mailchimp.webhook');
        $data = [
            'email' => 'test@trailburning.com'
        ];
        
        $this->assertFalse($user->getNewsletter());
        $webhook->process('subscribe', $data);
        $this->assertTrue($user->getNewsletter());
    }
    
    public function testProcessUnsubscribe()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\UserProfileData',
        ]); 
        
        $user = $this->getUser('mattallbeury');
        $user->setNewsletter(true);
        $webhook = $this->getContainer()->get('tb.mailchimp.webhook');
        $data = [
            'email' => 'test@trailburning.com'
        ];
        
        $this->assertTrue($user->getNewsletter());
        $webhook->process('unsubscribe', $data);
        $this->assertFalse($user->getNewsletter());
    }
    
}    