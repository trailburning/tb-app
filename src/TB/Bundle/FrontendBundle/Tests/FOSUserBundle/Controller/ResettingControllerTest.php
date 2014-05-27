<?php

namespace TB\Bundle\FrontendBundle\Tests\FOSUserbundle\Controller;

use TB\Bundle\FrontendBundle\Tests\AbstractFrontendTest;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\ApplicationTester;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;

/**
 *
 */
class ResettingControllerTest extends AbstractFrontendTest
{
        
    /**
     * Test Trail created by UserProfile, no Event, no Editorial
     */
    public function testResetting()
    {
        $this->loadFixtures([
            'TB\Bundle\FrontendBundle\DataFixtures\ORM\UserProfileData',
        ]);

        $client = $this->createClient();
        $crawler = $client->request('GET', '/resetting/request');
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode(),
            'Resseting request returns status code 200');
        
        // submit the resetting request form
        $form = $crawler->filter('.fos_user_resetting_request')->form(array(
            'username' => 'mattallbeury@trailburning.com'
        ));     
            
        $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect('/resetting/check-email?email=...%40mattallbeury'));
        
        // get mailer collector to check the resetting mail    
        $mailCollector = $client->getProfile()->getCollector('swiftmailer');
        $this->assertEquals(1, $mailCollector->getMessageCount(), 
            'One Mail was sent');
        
        $collectedMessages = $mailCollector->getMessages();
        $message = $collectedMessages[0];
        
        // Asserting e-mail data
        $this->assertInstanceOf('Swift_Message', $message);
        $this->assertEquals('Reset Password', $message->getSubject());
        $this->assertEquals('email@trailburning.com', key($message->getFrom()));
        $this->assertEquals('mattallbeury@trailburning.com', key($message->getTo()));

        //extract link to reset page
        $body = $message->getBody();
        if (!preg_match('/please visit http:\/\/localhost([^\s]+)/', $body, $match)) {
            $this->fail('could not extratc resseting link from email body');
        }   
        $link = $match[1];
        
        // check redirect after form submit
        $crawler = $client->followRedirect();
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode(),
            'The status code after the redirect to the resett success page is 200');    
        
        // go to reset passwor page
        $crawler = $client->request('GET', $link);
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode(),
            'Reset password page returns status code 200');

            // submit the reset password form
        $form = $crawler->filter('.fos_user_resetting_reset')->form(array(
            'fos_user_resetting_form[plainPassword][first]' => 'newpassword',
            'fos_user_resetting_form[plainPassword][second]' => 'newpassword',
        ));     
        
        $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect('/'));
        
        // check redirect to homepage after reset
        $crawler = $client->followRedirect();
        $this->assertEquals(Response::HTTP_OK,  $client->getResponse()->getStatusCode(),
            'The status code after the redirect to the homepage is 200');    
    }    

}
